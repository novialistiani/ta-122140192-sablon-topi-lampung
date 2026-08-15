<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        // Get NEW ARRIVALS - Latest products added (ready: active + stock > 0)
        $newArrivals = Product::with('variants')
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->latest('created_at')
            ->take(8)
            ->get()
            ->map(function ($product) {
                if ($product->variants && $product->variants->count() > 0) {
                    $prices = $product->variants->pluck('price')->filter();
                    $minPrice = $prices->min();
                    $maxPrice = $prices->max();
                    $totalStock = $product->variants->sum('stock');
                    
                    $product->price_min = $minPrice;
                    $product->price_max = $maxPrice;
                    $product->price_range = $minPrice == $maxPrice 
                        ? "Rp " . number_format($minPrice, 0, ',', '.')
                        : "Rp " . number_format($minPrice, 0, ',', '.') . " - Rp " . number_format($maxPrice, 0, ',', '.');
                    $product->total_stock = $totalStock;
                } else {
                    $product->price_min = $product->price;
                    $product->price_max = $product->price;
                    $product->price_range = "Rp " . number_format($product->price, 0, ',', '.');
                    $product->total_stock = $product->stock;
                }
                return $product;
            });

        // Get TOP SELLING - Best selling products
        $topSelling = Product::with('variants')
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('sales', 'desc')
            ->orderBy('views', 'desc')
            ->take(8)
            ->get()
            ->map(function ($product) {
                if ($product->variants && $product->variants->count() > 0) {
                    $prices = $product->variants->pluck('price')->filter();
                    $minPrice = $prices->min();
                    $maxPrice = $prices->max();
                    $totalStock = $product->variants->sum('stock');
                    
                    $product->price_min = $minPrice;
                    $product->price_max = $maxPrice;
                    $product->price_range = $minPrice == $maxPrice 
                        ? "Rp " . number_format($minPrice, 0, ',', '.')
                        : "Rp " . number_format($minPrice, 0, ',', '.') . " - Rp " . number_format($maxPrice, 0, ',', '.');
                    $product->total_stock = $totalStock;
                } else {
                    $product->price_min = $product->price;
                    $product->price_max = $product->price;
                    $product->price_range = "Rp " . number_format($product->price, 0, ',', '.');
                    $product->total_stock = $product->stock;
                }
                return $product;
            });

        // Get products by category (for homepage sections)
        $topiProducts = Product::with('variants')
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->where('category', 'topi')
            ->latest()
            ->take(4)
            ->get()
            ->map(function ($product) {
                if ($product->variants && $product->variants->count() > 0) {
                    $prices = $product->variants->pluck('price')->filter();
                    $minPrice = $prices->min();
                    $maxPrice = $prices->max();
                    $totalStock = $product->variants->sum('stock');
                    
                    $product->price_min = $minPrice;
                    $product->price_max = $maxPrice;
                    $product->price_range = $minPrice == $maxPrice 
                        ? "Rp " . number_format($minPrice, 0, ',', '.')
                        : "Rp " . number_format($minPrice, 0, ',', '.') . " - Rp " . number_format($maxPrice, 0, ',', '.');
                    $product->total_stock = $totalStock;
                } else {
                    $product->price_min = $product->price;
                    $product->price_max = $product->price;
                    $product->price_range = "Rp " . number_format($product->price, 0, ',', '.');
                    $product->total_stock = $product->stock;
                }
                return $product;
            });

        $kaosProducts = Product::with('variants')
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->where('category', 'kaos')
            ->latest()
            ->take(4)
            ->get()
            ->map(function ($product) {
                if ($product->variants && $product->variants->count() > 0) {
                    $prices = $product->variants->pluck('price')->filter();
                    $minPrice = $prices->min();
                    $maxPrice = $prices->max();
                    $totalStock = $product->variants->sum('stock');
                    
                    $product->price_min = $minPrice;
                    $product->price_max = $maxPrice;
                    $product->price_range = $minPrice == $maxPrice 
                        ? "Rp " . number_format($minPrice, 0, ',', '.')
                        : "Rp " . number_format($minPrice, 0, ',', '.') . " - Rp " . number_format($maxPrice, 0, ',', '.');
                    $product->total_stock = $totalStock;
                } else {
                    $product->price_min = $product->price;
                    $product->price_max = $product->price;
                    $product->price_range = "Rp " . number_format($product->price, 0, ',', '.');
                    $product->total_stock = $product->stock;
                }
                return $product;
            });

        return view('pages.home', compact('newArrivals', 'topSelling', 'topiProducts', 'kaosProducts'));
    }

    public function otherInfo()
    {
        return view('pages.other-info');
    }
}
