<x-admin-layout title="Panduan Cetak Custom Design">
<div style="padding: 24px; max-width: 1100px; margin: 0 auto;">
    <h1 style="font-size: 20px; font-weight: 700; margin-bottom: 4px;">Panduan Cetak Custom Design</h1>
    <p style="color: #6b7280; margin-bottom: 20px;">
        Unggah gambar panduan area cetak untuk tiap produk. Pelanggan melihat gambar ini di halaman Desain Kustom.
        Produk tanpa gambar panduan memakai gambar bawaan.
    </p>

    @if(session('success'))
        <div style="padding: 12px; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 16px;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div style="padding: 12px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 16px;">{{ $errors->first() }}</div>
    @endif

    <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background: #f9fafb; text-align: left;">
                    <th style="padding: 12px;">Produk</th>
                    <th style="padding: 12px;">Panduan Saat Ini</th>
                    <th style="padding: 12px;">Unggah / Ganti</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr style="border-top: 1px solid #f3f4f6;">
                    <td style="padding: 12px; font-weight: 600;">{{ $product->name }}<br><small style="color:#6b7280;">ID: {{ $product->id }}</small></td>
                    <td style="padding: 12px;">
                        @if(!empty($product->design_guide))
                            <a href="{{ asset('storage/' . $product->design_guide) }}" target="_blank">
                                <img src="{{ asset('storage/' . $product->design_guide) }}" alt="Panduan" style="height: 80px; border-radius: 6px; border: 1px solid #e5e7eb;">
                            </a>
                            <form method="POST" action="{{ route('admin.design-guides.destroy', $product->id) }}" style="margin-top: 6px;" onsubmit="return confirm('Hapus panduan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="font-size: 12px; color: #dc2626; background: none; border: none; cursor: pointer;">Hapus</button>
                            </form>
                        @else
                            <span style="color: #6b7280;">Memakai gambar bawaan</span>
                        @endif
                    </td>
                    <td style="padding: 12px;">
                        <form method="POST" action="{{ route('admin.design-guides.store', $product->id) }}" enctype="multipart/form-data" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                            @csrf
                            <input type="file" name="design_guide" accept="image/*" required style="font-size: 13px;">
                            <button type="submit" style="padding: 8px 16px; background: #f59e0b; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Simpan</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" style="padding: 20px; text-align: center; color: #6b7280;">Belum ada produk yang diizinkan custom design.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-admin-layout>