<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DesignGuideController extends Controller
{
    /**
     * Daftar produk yang boleh custom beserta gambar panduan cetaknya
     */
    public function index()
    {
        $products = Product::where('custom_design_allowed', 1)->orderBy('name')->get();

        return view('admin.design-guides', compact('products'));
    }

    /**
     * Unggah (atau ganti) gambar panduan cetak untuk satu produk
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'design_guide' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $product = Product::findOrFail($id);

        if (!empty($product->design_guide)) {
            Storage::disk('public')->delete($product->design_guide);
        }

        $path = $request->file('design_guide')->store('design-guides', 'public');

        Product::where('id', $product->id)->update(['design_guide' => $path]);

        return redirect()->route('admin.design-guides')
            ->with('success', 'Panduan cetak untuk "' . $product->name . '" berhasil disimpan.');
    }

    /**
     * Hapus gambar panduan (produk kembali memakai panduan bawaan)
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if (!empty($product->design_guide)) {
            Storage::disk('public')->delete($product->design_guide);
        }

        Product::where('id', $product->id)->update(['design_guide' => null]);

        return redirect()->route('admin.design-guides')
            ->with('success', 'Panduan cetak untuk "' . $product->name . '" dihapus.');
    }
}