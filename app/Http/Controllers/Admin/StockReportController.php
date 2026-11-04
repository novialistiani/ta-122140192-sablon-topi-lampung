<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StockMovementExport;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class StockReportController extends Controller
{
    /**
     * Susun query pergerakan stok berdasarkan filter (dipakai halaman dan ekspor).
     */
    private function filteredQuery(Request $request): array
    {
        $start = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->subDays(29)->startOfDay();
        $end = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        $query = StockMovement::query()->whereBetween('created_at', [$start, $end]);

        if ($request->filled('search')) {
            $query->where('product_name', 'like', '%' . $request->search . '%');
        }

        if ($request->type === 'in') {
            $query->where('quantity_change', '>', 0);
        } elseif ($request->type === 'out') {
            $query->where('quantity_change', '<', 0);
        }

        if ($request->filled('reason') && array_key_exists($request->reason, StockMovement::REASON_LABELS)) {
            $query->where('reason', $request->reason);
        }

        return [$query, $start, $end];
    }

    public function index(Request $request)
    {
        [$query, $start, $end] = $this->filteredQuery($request);

        $totals = (clone $query)->selectRaw(
            'COUNT(*) as movements,
             COALESCE(SUM(CASE WHEN quantity_change > 0 THEN quantity_change ELSE 0 END), 0) as total_in,
             COALESCE(SUM(CASE WHEN quantity_change < 0 THEN -quantity_change ELSE 0 END), 0) as total_out'
        )->first();

        $perProduct = (clone $query)->selectRaw(
            'product_id, product_name,
             COALESCE(SUM(CASE WHEN quantity_change > 0 THEN quantity_change ELSE 0 END), 0) as total_in,
             COALESCE(SUM(CASE WHEN quantity_change < 0 THEN -quantity_change ELSE 0 END), 0) as total_out'
        )->groupBy('product_id', 'product_name')->orderByDesc('total_out')->get();

        // Stok saat ini: jumlah stok varian bila ada varian, selain itu stok produk
        $currentStock = Product::with('variants')
            ->whereIn('id', $perProduct->pluck('product_id')->filter()->all())
            ->get()
            ->mapWithKeys(fn ($p) => [
                $p->id => $p->variants->count() > 0 ? (int) $p->variants->sum('stock') : (int) $p->stock,
            ]);

        $movements = (clone $query)->latest('created_at')->latest('id')->paginate(25)->withQueryString();

        return view('admin.stock-report', [
            'movements' => $movements,
            'totals' => $totals,
            'perProduct' => $perProduct,
            'currentStock' => $currentStock,
            'start' => $start,
            'end' => $end,
            'filters' => $request->only(['search', 'type', 'reason']),
        ]);
    }

    public function export(Request $request)
    {
        [$query] = $this->filteredQuery($request);
        $rows = $query->latest('created_at')->latest('id')->get();

        return Excel::download(
            new StockMovementExport($rows),
            'laporan-stok-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}