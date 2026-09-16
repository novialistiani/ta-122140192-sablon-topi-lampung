<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestCartController extends Controller
{
    public function testAddToCart(Request $request)
    {
        // Test data
        $cart = session()->get('cart', []);
        $cart['test_1_red_M'] = [
            'id' => 'test_1_red_M',
            'product_id' => 1,
            'name' => 'Test Kaos Premium',
            'price' => 85000,
            'quantity' => 2,
            'color' => 'red',
            'size' => 'M',
            'image' => 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27400%27 height=%27400%27%3E%3Crect fill=%27%23f0f0f0%27 width=%27400%27 height=%27400%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 font-size=%2724%27 fill=%27%23999%27 text-anchor=%27middle%27 dy=%27.3em%27%3ENo Image%3C/text%3E%3C/svg%3E',
            'variant' => 'red / M',
        ];
        session()->put('cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Test cart data added',
            'cart' => $cart
        ]);
    }

    public function testGetCart()
    {
        $cart = session()->get('cart', []);
        return response()->json(['cart' => $cart]);
    }
}
