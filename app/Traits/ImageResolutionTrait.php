<?php

namespace App\Traits;

use App\Models\Product;
use App\Models\ProductVariant;

trait ImageResolutionTrait
{
    /**
     * Resolve image for an item with variant and product fallbacks
     * Priority: variant image > product image > stored image in item
     *
     * @param array $item Item data containing variant_id, product_id, image
     * @param Product|null $product Optional product instance to avoid re-querying
     * @return string|null Image path or null
     */
    protected function resolveItemImage(array $item, $product = null): ?string
    {
        // Try to get variant image first
        if (!empty($item['variant_id'])) {
            $variantImage = $this->getVariantImage($item['variant_id']);
            if ($variantImage) {
                return $variantImage;
            }
        }

        // Fallback to product image
        if (!$product && !empty($item['product_id'])) {
            $product = Product::find($item['product_id']);
        }

        if ($product && !empty($product->image)) {
            return $product->image;
        }

        // Last fallback: stored image in item
        return $item['image'] ?? null;
    }

    /**
     * Get variant image safely
     *
     * @param int|string $variantId
     * @return string|null
     */
    protected function getVariantImage($variantId): ?string
    {
        if (empty($variantId)) {
            return null;
        }

        $variant = ProductVariant::find($variantId);
        
        return ($variant && !empty($variant->image)) ? $variant->image : null;
    }

    /**
     * Resolve product image with optional variant
     *
     * @param int $productId
     * @param int|null $variantId
     * @return string|null
     */
    protected function resolveProductImage(int $productId, $variantId = null): ?string
    {
        // Try variant first
        if ($variantId) {
            $variantImage = $this->getVariantImage($variantId);
            if ($variantImage) {
                return $variantImage;
            }
        }

        // Fallback to product
        $product = Product::find($productId);
        
        return $product ? $product->image : null;
    }

    /**
     * Batch resolve images for multiple items
     *
     * @param array $items
     * @return array Items with resolved images
     */
    protected function batchResolveImages(array $items): array
    {
        return array_map(function ($item) {
            $item['image'] = $this->resolveItemImage($item);
            return $item;
        }, $items);
    }
}
