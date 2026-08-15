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
            'image' => 'https://via.placeholder.com/120',
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
