<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ChatbotSettingsController extends Controller
{
    /**
     * Display chatbot settings page
     */
    public function index()
    {
        $globalEnabled = config('chatbot.enabled', true);
        $disabledProducts = Cache::get('chatbot_disabled_products', []);
        $products = Product::select('id', 'name', 'is_active')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.chatbot.settings', compact('globalEnabled', 'disabledProducts', 'products'));
    }

    /**
     * Toggle chatbot globally
     */
    public function toggleGlobal(Request $request)
    {
        $enabled = $request->get('enabled', true);

        try {
            // Update config (dalam memory untuk session ini)
            config(['chatbot.enabled' => $enabled]);

            // TODO: Dapat di-save ke database settings table jika ingin persist
            // Settings::updateOrCreate(['key' => 'chatbot_enabled'], ['value' => $enabled]);

            // Update active conversations
            if (!$enabled) {
                ChatConversation::where('is_admin_active', false)
                    ->update(['is_admin_active' => true]); // Force manual mode
            }

            return response()->json([
                'success' => true,
                'message' => 'Chatbot ' . ($enabled ? 'diaktifkan' : 'dinonaktifkan') . ' secara global',
                'status' => $enabled ? 'enabled' : 'disabled'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status chatbot'
            ], 500);
        }
    }

    /**
     * Toggle chatbot for specific product
     */
    public function toggleProduct(Request $request, $productId)
    {
        $request->validate([
            'enabled' => 'required|boolean'
        ]);

        $product = Product::findOrFail($productId);
        $enabled = $request->get('enabled', true);
        $disabledProducts = Cache::get('chatbot_disabled_products', []);

        try {
            if (!$enabled) {
                // Add to disabled list
                if (!in_array($productId, $disabledProducts)) {
                    $disabledProducts[] = $productId;
                }
                $message = "Chatbot dinonaktifkan untuk produk {$product->name}";
            } else {
                // Remove from disabled list
                $disabledProducts = array_filter($disabledProducts, fn($id) => $id !== $productId);
                $message = "Chatbot diaktifkan untuk produk {$product->name}";
            }

            // Store in cache (atau database untuk persistence)
            Cache::forever('chatbot_disabled_products', $disabledProducts);

            // Update existing conversations for this product
            ChatConversation::where('product_id', $productId)
                ->where('status', 'active')
                ->update(['is_admin_active' => !$enabled]); // Jika chatbot disabled, force manual mode

            return response()->json([
                'success' => true,
                'message' => $message,
                'product_id' => $productId,
                'enabled' => $enabled,
                'disabled_products_count' => count($disabledProducts)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status chatbot untuk produk'
            ], 500);
        }
    }

    /**
     * Get chatbot settings
     */
    public function getSettings()
    {
        $globalEnabled = config('chatbot.enabled', true);
        $disabledProducts = Cache::get('chatbot_disabled_products', []);
        $totalConversations = ChatConversation::count();
        $activeConversations = ChatConversation::where('status', 'active')->count();
        $escalatedConversations = ChatConversation::where('is_escalated', true)->count();
        $adminHandledConversations = ChatConversation::where('taken_over_by_admin', true)->count();

        return response()->json([
            'success' => true,
            'settings' => [
                'global_enabled' => $globalEnabled,
                'disabled_products' => $disabledProducts,
                'disabled_products_count' => count($disabledProducts)
            ],
            'statistics' => [
                'total_conversations' => $totalConversations,
                'active_conversations' => $activeConversations,
                'escalated_conversations' => $escalatedConversations,
                'admin_handled_conversations' => $adminHandledConversations
            ]
        ]);
    }

    /**
     * Get product chatbot status
     */
    public function getProductStatus($productId)
    {
        $product = Product::findOrFail($productId);
        $disabledProducts = Cache::get('chatbot_disabled_products', []);
        $enabled = !in_array($productId, $disabledProducts);

        $conversationCount = ChatConversation::where('product_id', $productId)->count();
        $activeCount = ChatConversation::where('product_id', $productId)
            ->where('status', 'active')
            ->count();

        return response()->json([
            'success' => true,
            'product_id' => $productId,
            'product_name' => $product->name,
            'chatbot_enabled' => $enabled,
            'conversation_count' => $conversationCount,
            'active_conversations' => $activeCount
        ]);
    }

    /**
     * Get all products with chatbot status
     */
    public function getProductsList()
    {
        $disabledProducts = Cache::get('chatbot_disabled_products', []);
        
        $products = Product::where('is_active', true)
            ->withCount(['conversations' => function($query) {
                $query->where('status', 'active');
            }])
            ->get()
            ->map(function($product) use ($disabledProducts) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'enabled' => !in_array($product->id, $disabledProducts),
                    'conversation_count' => $product->conversations_count ?? 0
                ];
            });

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }

    /**
     * Reset chatbot settings to defaults
     */
    public function reset(Request $request)
    {
        $request->validate([
            'confirm' => 'required|in:yes'
        ]);

        try {
            // Clear disabled products list
            Cache::forget('chatbot_disabled_products');

            // Reset all conversations
            ChatConversation::update(['is_admin_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Pengaturan chatbot berhasil direset ke default'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset pengaturan'
            ], 500);
        }
    }
}
