<?php

namespace App\Traits;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Log;

trait StockManagementTrait
{
    /**
     * Deduct stock when order is approved
     *
     * @param mixed $order Order or CustomDesignOrder instance
     * @param string $orderType 'regular' or 'custom'
     * @return array ['success' => bool, 'message' => string]
     */
    protected function deductStockForOrder($order, string $orderType): array
    {
        try {
            if ($orderType === 'custom') {
                return $this->deductCustomOrderStock($order);
            } else {
                return $this->deductRegularOrderStock($order);
            }
        } catch (\Exception $e) {
            Log::error("Error deducting stock for order #{$order->id}: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan saat mengurangi stock: ' . $e->getMessage()];
        }
    }

    /**
     * Restore stock when order is rejected or cancelled
     *
     * @param mixed $order Order or CustomDesignOrder instance
     * @param string $orderType 'regular' or 'custom'
     * @return void
     */
    protected function restoreStockForOrder($order, string $orderType): void
    {
        try {
            if ($orderType === 'custom') {
                $this->restoreCustomOrderStock($order);
            } else {
                $this->restoreRegularOrderStock($order);
            }
        } catch (\Exception $e) {
            Log::error("Error restoring stock for order #{$order->id}: " . $e->getMessage());
        }
    }

    /**
     * Deduct stock for custom design order (single product/variant)
     *
     * @param mixed $order
     * @return array
     */
    private function deductCustomOrderStock($order): array
    {
        if ($order->variant_id) {
            $variant = ProductVariant::find($order->variant_id);
            
            if (!$variant) {
                return ['success' => false, 'message' => 'Variant produk tidak ditemukan'];
            }
            
            if ($variant->stock < $order->quantity) {
                return [
                    'success' => false,
                    'message' => "Stock variant tidak cukup. Stock tersedia: {$variant->stock}, diminta: {$order->quantity}"
                ];
            }
            
            $variant->decrement('stock', $order->quantity);
            
            // Also decrement product stock to keep it in sync
            $product = Product::find($variant->product_id);
            if ($product && $product->stock > 0) {
                $deductAmount = min($order->quantity, $product->stock);
                $product->decrement('stock', $deductAmount);
            }
            
            Log::info("Stock deducted for custom order #{$order->id}: Variant #{$variant->id}, qty: {$order->quantity}");
        } else {
            $product = Product::find($order->product_id);
            
            if (!$product) {
                return ['success' => false, 'message' => 'Produk tidak ditemukan'];
            }
            
            if ($product->stock < $order->quantity) {
                return [
                    'success' => false,
                    'message' => "Stock produk tidak cukup. Stock tersedia: {$product->stock}, diminta: {$order->quantity}"
                ];
            }
            
            $product->decrement('stock', $order->quantity);
            Log::info("Stock deducted for custom order #{$order->id}: Product #{$product->id}, qty: {$order->quantity}");
        }

        return ['success' => true];
    }

    /**
     * Deduct stock for regular order (multiple items)
     *
     * @param mixed $order
     * @return array
     */
    private function deductRegularOrderStock($order): array
    {
        foreach ($order->items as $item) {
            // Skip if both variant_id and product_id are invalid/empty
            if (empty($item['product_id']) && empty($item['variant_id'])) {
                Log::warning("Skipping item with no product_id or variant_id in order #{$order->id}");
                continue;
            }

            if (isset($item['variant_id']) && $item['variant_id'] && !empty($item['variant_id'])) {
                // Ensure variant_id is numeric
                if (!is_numeric($item['variant_id'])) {
                    Log::error("Invalid variant_id '{$item['variant_id']}' for order #{$order->id}, item: {$item['name']}");
                    return ['success' => false, 'message' => "Variant produk '{$item['name']}' memiliki ID tidak valid. Silakan hubungi administrator."];
                }
                
                $variant = ProductVariant::find($item['variant_id']);
                
                if (!$variant) {
                    Log::error("Variant ID {$item['variant_id']} not found for order #{$order->id}");
                    return ['success' => false, 'message' => "Variant produk '{$item['name']}' tidak ditemukan di database. Mungkin sudah dihapus."];
                }
                
                if ($variant->stock < $item['quantity']) {
                    return [
                        'success' => false,
                        'message' => "Stock variant '{$item['name']}' tidak cukup. Stock tersedia: {$variant->stock}, diminta: {$item['quantity']}"
                    ];
                }
                
                $variant->decrement('stock', $item['quantity']);
                
                // Also decrement product stock to keep it in sync
                $product = Product::find($variant->product_id);
                if ($product && $product->stock > 0) {
                    $deductAmount = min($item['quantity'], $product->stock);
                    $product->decrement('stock', $deductAmount);
                }
                
                Log::info("Stock deducted for order #{$order->id}: Variant #{$variant->id}, qty: {$item['quantity']}");
            } else {
                // Use product stock instead
                if (!is_numeric($item['product_id'])) {
                    Log::error("Invalid product_id '{$item['product_id']}' for order #{$order->id}, item: {$item['name']}");
                    return ['success' => false, 'message' => "Produk '{$item['name']}' memiliki ID tidak valid. Silakan hubungi administrator."];
                }
                
                $product = Product::find($item['product_id']);
                
                if (!$product) {
                    Log::error("Product ID {$item['product_id']} not found for order #{$order->id}");
                    return ['success' => false, 'message' => "Produk '{$item['name']}' tidak ditemukan di database. Mungkin sudah dihapus."];
                }
                
                if ($product->stock < $item['quantity']) {
                    return [
                        'success' => false,
                        'message' => "Stock produk '{$item['name']}' tidak cukup. Stock tersedia: {$product->stock}, diminta: {$item['quantity']}"
                    ];
                }
                
                $product->decrement('stock', $item['quantity']);
                Log::info("Stock deducted for order #{$order->id}: Product #{$product->id}, qty: {$item['quantity']}");
            }
        }

        return ['success' => true];
    }

    /**
     * Restore stock for custom design order
     *
     * @param mixed $order
     * @return void
     */
    private function restoreCustomOrderStock($order): void
    {
        if ($order->variant_id) {
            $variant = ProductVariant::find($order->variant_id);
            if ($variant) {
                $variant->increment('stock', $order->quantity);
                
                // Also restore product stock
                $product = Product::find($variant->product_id);
                if ($product) {
                    $product->increment('stock', $order->quantity);
                }
                
                Log::info("Stock restored for custom order #{$order->id}: Variant #{$variant->id}, qty: {$order->quantity}");
            }
        } else {
            $product = Product::find($order->product_id);
            if ($product) {
                $product->increment('stock', $order->quantity);
                Log::info("Stock restored for custom order #{$order->id}: Product #{$product->id}, qty: {$order->quantity}");
            }
        }
    }

    /**
     * Restore stock for regular order
     *
     * @param mixed $order
     * @return void
     */
    private function restoreRegularOrderStock($order): void
    {
        foreach ($order->items as $item) {
            if (isset($item['variant_id']) && $item['variant_id']) {
                $variant = ProductVariant::find($item['variant_id']);
                if ($variant) {
                    $variant->increment('stock', $item['quantity']);
                    
                    // Also restore product stock
                    $product = Product::find($variant->product_id);
                    if ($product) {
                        $product->increment('stock', $item['quantity']);
                    }
                    
                    Log::info("Stock restored for order #{$order->id}: Variant #{$variant->id}, qty: {$item['quantity']}");
                }
            } else {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->increment('stock', $item['quantity']);
                    Log::info("Stock restored for order #{$order->id}: Product #{$product->id}, qty: {$item['quantity']}");
                }
            }
        }
    }

    /**
     * Check if stock is available for a product/variant
     *
     * @param int $productId
     * @param int|null $variantId
     * @param int $quantity
     * @return array ['available' => bool, 'stock' => int, 'message' => string]
     */
    protected function checkStockAvailability(int $productId, $variantId, int $quantity): array
    {
        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            
            if (!$variant) {
                return ['available' => false, 'stock' => 0, 'message' => 'Variant tidak ditemukan'];
            }
            
            $available = $variant->stock >= $quantity;
            
            return [
                'available' => $available,
                'stock' => $variant->stock,
                'message' => $available ? 'Stock tersedia' : "Stock tidak cukup. Tersedia: {$variant->stock}"
            ];
        }

        $product = Product::find($productId);
        
        if (!$product) {
            return ['available' => false, 'stock' => 0, 'message' => 'Produk tidak ditemukan'];
        }
        
        $available = $product->stock >= $quantity;
        
        return [
            'available' => $available,
            'stock' => $product->stock,
            'message' => $available ? 'Stock tersedia' : "Stock tidak cukup. Tersedia: {$product->stock}"
        ];
    }
}
