<x-admin-layout title="Pengaturan QRIS">
    <div class="pos-panel" style="max-width: 500px;">
        <h2 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Pengaturan QRIS</h2>
        <p style="font-size: 14px; color: #64748b; margin-bottom: 20px;">
            Gambar QRIS ini akan ditampilkan kepada pelanggan saat melakukan pembayaran.
        </p>

        @if(session('success'))
            <div style="background: #f0fdf4; color: #166534; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 14px;">
                {{ session('success') }}
            </div>
        @endif

        @if($settings && $settings->qris_image)
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="{{ asset('storage/' . $settings->qris_image) }}" alt="QRIS Saat Ini"
                     style="max-width: 240px; width: 100%; border-radius: 12px; border: 1px solid #e2e8f0; padding: 8px;">
                <p style="font-size: 13px; color: #64748b; margin-top: 8px;">QRIS yang sedang aktif</p>
            </div>
        @else
            <div style="text-align: center; padding: 30px; background: #f8fafc; border-radius: 12px; margin-bottom: 20px;">
                <p style="color: #94a3b8; font-size: 14px;">Belum ada QRIS yang diunggah</p>
            </div>
        @endif

        <form action="{{ route('admin.payment-settings.update-qris') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;">
                Upload Gambar QRIS Baru
            </label>
            <input type="file" name="qris_image" accept="image/jpeg,image/jpg,image/png" required class="pos-input" style="margin-bottom: 16px;">

            <button type="submit" class="pos-btn-primary" style="width: 100%;">
                Simpan QRIS
            </button>
        </form>
    </div>
</x-admin-layout>