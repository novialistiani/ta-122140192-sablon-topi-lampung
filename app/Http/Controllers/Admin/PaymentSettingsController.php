<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentSettingsController extends Controller
{
    /**
     * Tampilkan halaman pengaturan QRIS
     */
    public function index()
    {
        $settings = PaymentSettings::first();
        return view('admin.payment-settings.index', compact('settings'));
    }

    /**
     * Upload/ganti gambar QRIS
     */
    public function updateQris(Request $request)
    {
        $request->validate([
            'qris_image' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $settings = PaymentSettings::first();

        // Hapus gambar lama jika ada
        if ($settings && $settings->qris_image) {
            Storage::disk('public')->delete($settings->qris_image);
        }

        $path = $request->file('qris_image')->store('payment-settings', 'public');

        if ($settings) {
            $settings->update(['qris_image' => $path]);
        } else {
            PaymentSettings::create(['qris_image' => $path]);
        }

        return back()->with('success', 'Gambar QRIS berhasil diperbarui.');
    }
}