<x-admin-layout title="Pengaturan QRIS">
    <style>
        .qris-wrap { max-width: 520px; margin: 0 auto; width: 100%; box-sizing: border-box; }
        .qris-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
            padding: 24px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        }
        .qris-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 6px; }
        .qris-title { font-size: 18px; font-weight: 700; color: #0f172a; margin: 0; }
        .qris-desc { font-size: 14px; color: #64748b; margin: 0 0 20px; line-height: 1.5; }

        .qris-badge {
            font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 999px;
            white-space: nowrap; flex-shrink: 0;
        }
        .qris-badge.on { background: #dcfce7; color: #166534; }
        .qris-badge.off { background: #f1f5f9; color: #64748b; }

        .qris-alert { padding: 12px 14px; border-radius: 10px; font-size: 14px; margin-bottom: 16px; line-height: 1.45; }
        .qris-alert.ok { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .qris-alert.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        .qris-preview {
            background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 14px;
            padding: 20px 16px; text-align: center; margin-bottom: 22px;
        }
        .qris-frame {
            display: inline-block; background: #fff; border: 1px solid #e2e8f0;
            border-radius: 14px; padding: 10px; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
        }
        .qris-frame img { display: block; width: 100%; max-width: 240px; height: auto; border-radius: 6px; }
        .qris-caption { font-size: 13px; color: #64748b; margin: 10px 0 0; }
        .qris-empty { padding: 28px 8px; color: #94a3b8; font-size: 14px; }
        .qris-empty svg { display: block; margin: 0 auto 8px; color: #cbd5e1; }

        .qris-label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 8px; }
        .qris-drop {
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;
            text-align: center; padding: 22px 14px; border: 2px dashed #cbd5e1; border-radius: 14px;
            background: #fff; cursor: pointer; transition: border-color .2s, background .2s;
        }
        .qris-drop:hover, .qris-drop.drag { border-color: #facc15; background: #fffbeb; }
        .qris-drop strong { font-size: 14px; color: #0f172a; }
        .qris-drop span { font-size: 12px; color: #64748b; }
        .qris-drop input { display: none; }
        .qris-filename { font-size: 13px; color: #166534; font-weight: 600; margin-top: 6px; word-break: break-all; }

        .qris-new { display: none; margin-top: 14px; text-align: center; }
        .qris-new.show { display: block; }
        .qris-new img { max-width: 160px; width: 100%; height: auto; border-radius: 10px; border: 1px solid #e2e8f0; padding: 6px; background: #fff; }
        .qris-new p { font-size: 12px; color: #64748b; margin: 6px 0 0; }

        .qris-btn {
            width: 100%; margin-top: 18px; min-height: 46px; border: none; border-radius: 12px;
            background: #facc15; color: #001F3F; font-size: 15px; font-weight: 700; cursor: pointer;
            transition: background .2s, opacity .2s;
        }
        .qris-btn:hover { background: #fbbf24; }
        .qris-btn:disabled { opacity: .5; cursor: not-allowed; }

        .qris-note { font-size: 12px; color: #94a3b8; margin: 14px 0 0; line-height: 1.5; text-align: center; }

        @media (max-width: 768px) {
            .qris-card { padding: 16px; border-radius: 14px; }
            .qris-title { font-size: 16px; }
            .qris-preview { padding: 16px 10px; }
        }
    </style>

    <div class="qris-wrap">
        <div class="qris-card">
            <div class="qris-head">
                <h2 class="qris-title">Pengaturan QRIS</h2>
                @if($settings && $settings->qris_image)
                    <span class="qris-badge on">Aktif</span>
                @else
                    <span class="qris-badge off">Belum ada</span>
                @endif
            </div>
            <p class="qris-desc">Gambar QRIS ini akan ditampilkan kepada pelanggan saat melakukan pembayaran.</p>

            @if(session('success'))
                <div class="qris-alert ok">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="qris-alert err">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="qris-preview">
                @if($settings && $settings->qris_image)
                    <div class="qris-frame">
                        <img src="{{ route('images.serve', ['path' => $settings->qris_image]) }}"
                             alt="QRIS yang sedang aktif"
                             onerror="this.closest('.qris-frame').style.display='none'; document.getElementById('qrisImgError').style.display='block';">
                    </div>
                    <p class="qris-caption">QRIS yang sedang aktif</p>
                    <p class="qris-caption" id="qrisImgError" style="display:none; color:#b91c1c;">
                        Gambar tidak dapat dimuat. Coba unggah ulang QRIS.
                    </p>
                @else
                    <div class="qris-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                            <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                            <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                            <path d="M14 14h3v3h-3zM20 14v3M14 20h3M20 20h1"></path>
                        </svg>
                        Belum ada QRIS yang diunggah
                    </div>
                @endif
            </div>

            <form action="{{ route('admin.payment-settings.update-qris') }}" method="POST" enctype="multipart/form-data" id="qrisForm">
                @csrf
                <label class="qris-label" for="qrisInput">Upload Gambar QRIS Baru</label>

                <label class="qris-drop" id="qrisDrop" for="qrisInput">
                    <strong>Pilih file atau seret ke sini</strong>
                    <span>JPG atau PNG, maksimal 5 MB</span>
                    <input type="file" name="qris_image" id="qrisInput" accept="image/jpeg,image/jpg,image/png" required>
                    <div class="qris-filename" id="qrisFilename"></div>
                </label>

                <div class="qris-new" id="qrisNew">
                    <img id="qrisNewImg" alt="Pratinjau QRIS baru">
                    <p>Pratinjau QRIS baru (belum disimpan)</p>
                </div>

                <button type="submit" class="qris-btn" id="qrisSubmit" disabled>Simpan QRIS</button>
            </form>

            <p class="qris-note">QRIS lama akan diganti dan tidak bisa dikembalikan. Pastikan gambar jelas dan bisa dipindai.</p>
        </div>
    </div>

    <script>
        (function () {
            const input = document.getElementById('qrisInput');
            const drop = document.getElementById('qrisDrop');
            const nameEl = document.getElementById('qrisFilename');
            const box = document.getElementById('qrisNew');
            const img = document.getElementById('qrisNewImg');
            const btn = document.getElementById('qrisSubmit');
            const MAX = 5 * 1024 * 1024;

            function reset() {
                nameEl.textContent = '';
                box.classList.remove('show');
                img.removeAttribute('src');
                btn.disabled = true;
            }

            input.addEventListener('change', function () {
                const file = input.files && input.files[0];
                if (!file) { reset(); return; }

                if (!/^image\/(jpe?g|png)$/i.test(file.type)) {
                    alert('Format file harus JPG atau PNG.');
                    input.value = ''; reset(); return;
                }
                if (file.size > MAX) {
                    alert('Ukuran file maksimal 5 MB.');
                    input.value = ''; reset(); return;
                }

                nameEl.textContent = file.name;
                img.src = URL.createObjectURL(file);
                box.classList.add('show');
                btn.disabled = false;
            });

            ['dragenter', 'dragover'].forEach(ev =>
                drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('drag'); }));
            ['dragleave', 'drop'].forEach(ev =>
                drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove('drag'); }));
            drop.addEventListener('drop', function (e) {
                if (e.dataTransfer && e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    input.dispatchEvent(new Event('change'));
                }
            });
        })();
    </script>
</x-admin-layout>