<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /**
     * Tampilkan halaman utama kasir (POS)
     */
    public function index()
    {
        return view('admin.pos.index');
    }

    /**
     * API: Cari produk aktif beserta variannya untuk ditampilkan di layar kasir
     */
    public function searchProducts(Request $request)
{
    $search = $request->get('search', '');
    $category = $request->get('category', '');

    $products = Product::with('variants')
        ->where('is_active', true)
        ->when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%");
        })
        ->when($category, function ($query) use ($category) {
            $query->where('category', $category);
        })
        ->orderBy('name')
        ->limit(30)
        ->get();

    $data = $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
                        'image' => (function () use ($product) {
    $img = $product->image;
    if (!$img) {
        $variant = $product->variants->first(fn($v) => !empty($v->image));
        $img = $variant ? $variant->image : null;
    }

    if (!$img) {
        return null;
    }

    if (filter_var($img, FILTER_VALIDATE_URL)) {
        return $img;
    }

    return route('images.serve', ['path' => $img]);
})(),
            'price' => $product->price,
            'stock' => $product->stock,
            'variants' => $product->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'color' => $variant->color,
                    'size' => $variant->size,
                    'price' => $variant->price,
                    'stock' => $variant->stock,
                ];
            }),
        ];
    });

    return response()->json(['success' => true, 'data' => $data]);
}

    /**
     * Simpan transaksi POS: kurangi stok, catat sebagai Order dengan source='pos'
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,qris',
            'cash_received' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $items = [];
            $subtotal = 0;

            foreach ($validated['items'] as $itemInput) {
                $product = Product::findOrFail($itemInput['product_id']);
                $variant = $itemInput['variant_id']
                    ? ProductVariant::where('id', $itemInput['variant_id'])->where('product_id', $product->id)->first()
                    : null;

                $availableStock = $variant ? $variant->stock : $product->stock;
                if ($itemInput['quantity'] > $availableStock) {
                    throw new \Exception("Stok {$product->name} tidak mencukupi. Tersedia: {$availableStock}");
                }

                $price = $variant ? (float) $variant->price : (float) $product->price;

                $items[] = [
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'name' => $product->name,
                    'price' => $price,
                    'quantity' => $itemInput['quantity'],
                    'color' => $variant?->color,
                    'size' => $variant?->size,
                    'image' => $variant && $variant->image ? $variant->image : $product->image,
                ];

                $subtotal += $price * $itemInput['quantity'];

                // Kurangi stok
                if ($variant) {
                    $variant->decrement('stock', $itemInput['quantity']);
                } else {
                    $product->decrement('stock', $itemInput['quantity']);
                }
            }

            $order = Order::create([
                'user_id' => null,
                'items' => $items,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total' => $subtotal,
                'status' => 'completed',
                'payment_status' => 'paid',
                'source' => 'pos',
                'pos_payment_method' => $validated['payment_method'],
                'cashier_id' => auth('admin')->id(),
                'paid_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan',
                'order_id' => $order->id,
                'total' => $subtotal,
                'cash_received' => $validated['cash_received'] ?? null,
                'change' => isset($validated['cash_received']) ? $validated['cash_received'] - $subtotal : null,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}