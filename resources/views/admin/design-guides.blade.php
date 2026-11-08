<x-admin-layout title="Panduan Cetak Custom Design">
<style>
    .dg-page { max-width: 1200px; margin: 0 auto; padding: 8px 4px 32px; }

    .dg-hero {
        background: linear-gradient(135deg, #0a1d37 0%, #1a3a52 100%);
        color: #fff;
        border-radius: 16px;
        padding: 24px 28px;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }
    .dg-hero::after {
        content: "";
        position: absolute;
        right: -40px; top: -40px;
        width: 160px; height: 160px;
        border-radius: 50%;
        background: rgba(255, 193, 7, 0.18);
    }
    .dg-hero h1 { font-size: 1.35rem; font-weight: 700; margin: 0 0 6px; position: relative; }
    .dg-hero p { margin: 0; font-size: 0.875rem; line-height: 1.6; color: rgba(255,255,255,0.75); max-width: 640px; position: relative; }

    .dg-alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 0.875rem; }
    .dg-alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .dg-alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    .dg-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .dg-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        transition: box-shadow .2s, transform .2s;
    }
    .dg-card:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.09); transform: translateY(-2px); }

    .dg-card-head {
        display: flex; align-items: flex-start; justify-content: space-between;
        gap: 10px; padding: 14px 16px 10px;
    }
    .dg-name { font-weight: 600; font-size: 0.95rem; color: #111827; line-height: 1.3; overflow-wrap: anywhere; }
    .dg-id { font-size: 0.72rem; color: #9ca3af; margin-top: 2px; }

    .dg-badge {
        flex-shrink: 0;
        font-size: 0.68rem; font-weight: 600;
        padding: 3px 10px; border-radius: 999px; white-space: nowrap;
    }
    .dg-badge-custom { background: #dcfce7; color: #166534; }
    .dg-badge-default { background: #f3f4f6; color: #6b7280; }

    .dg-preview {
        margin: 0 16px;
        height: 170px;
        background: #f9fafb;
        border: 1px dashed #d1d5db;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        overflow: hidden;
    }
    .dg-preview img { max-width: 100%; max-height: 100%; object-fit: contain; display: block; }
    .dg-empty { text-align: center; color: #9ca3af; font-size: 0.78rem; padding: 12px; }
    .dg-empty svg { display: block; margin: 0 auto 6px; }

    .dg-delete-row { padding: 8px 16px 0; text-align: right; min-height: 28px; }
    .dg-delete {
        font-size: 0.78rem; color: #dc2626; background: none; border: none;
        cursor: pointer; padding: 4px 0; font-weight: 500;
    }
    .dg-delete:hover { text-decoration: underline; }

    .dg-form { padding: 10px 16px 16px; margin-top: auto; display: flex; flex-direction: column; gap: 10px; }
    .dg-file {
        width: 100%; font-size: 0.8rem; color: #6b7280;
        border: 1px solid #e5e7eb; border-radius: 8px; padding: 6px;
        background: #fff;
    }
    .dg-file::file-selector-button {
        margin-right: 10px; padding: 7px 12px;
        border: none; border-radius: 6px;
        background: #f3f4f6; color: #374151; font-weight: 600; cursor: pointer;
    }
    .dg-file::file-selector-button:hover { background: #e5e7eb; }

    .dg-save {
        width: 100%; padding: 10px 16px;
        background: #f59e0b; color: #fff;
        border: none; border-radius: 8px;
        font-weight: 600; font-size: 0.875rem; cursor: pointer;
        transition: background .2s;
    }
    .dg-save:hover { background: #d97706; }

    .dg-none { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 32px; text-align: center; color: #6b7280; }

    @media (max-width: 1100px) {
        .dg-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 640px) {
        .dg-grid { grid-template-columns: 1fr; gap: 14px; }
        .dg-hero { padding: 18px; border-radius: 14px; }
        .dg-hero h1 { font-size: 1.15rem; }
        .dg-preview { height: 150px; }
    }
</style>

<div class="dg-page">
    <div class="dg-hero">
        <h1>Panduan Cetak Custom Design</h1>
        <p>
            Unggah gambar panduan area cetak untuk tiap produk. Pelanggan melihat gambar ini di halaman Desain Kustom.
            Produk tanpa gambar panduan memakai gambar bawaan.
        </p>
    </div>

    @if(session('success'))
        <div class="dg-alert dg-alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="dg-alert dg-alert-error">{{ $errors->first() }}</div>
    @endif

    @if($products->count())
    <div class="dg-grid">
        @foreach($products as $product)
        <div class="dg-card">
            <div class="dg-card-head">
                <div>
                    <div class="dg-name">{{ $product->name }}</div>
                    <div class="dg-id">ID: {{ $product->id }}</div>
                </div>
                @if(!empty($product->design_guide))
                    <span class="dg-badge dg-badge-custom">Panduan khusus</span>
                @else
                    <span class="dg-badge dg-badge-default">Bawaan</span>
                @endif
            </div>

            <div class="dg-preview">
                @if(!empty($product->design_guide))
                    <a href="{{ asset('storage/' . $product->design_guide) }}" target="_blank" style="display:flex;width:100%;height:100%;align-items:center;justify-content:center;">
                        <img src="{{ asset('storage/' . $product->design_guide) }}" alt="Panduan {{ $product->name }}">
                    </a>
                @else
                    <div class="dg-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                        Memakai gambar bawaan
                    </div>
                @endif
            </div>

            <div class="dg-delete-row">
                @if(!empty($product->design_guide))
                    <form method="POST" action="{{ route('admin.design-guides.destroy', $product->id) }}" onsubmit="return confirm('Hapus panduan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dg-delete">Hapus panduan</button>
                    </form>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.design-guides.store', $product->id) }}" enctype="multipart/form-data" class="dg-form">
                @csrf
                <input type="file" name="design_guide" accept="image/*" required class="dg-file">
                <button type="submit" class="dg-save">Simpan</button>
            </form>
        </div>
        @endforeach
    </div>
    @else
        <div class="dg-none">Belum ada produk yang diizinkan custom design.</div>
    @endif
</div>
</x-admin-layout>