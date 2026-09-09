<x-admin-layout title="POS (Point of Sale)">
    @vite(['resources/css/admin/pos.css'])

    <div class="pos-page-header">
        <h1>POS (Point of Sale)</h1>
        <p>Transaksi cepat untuk penjualan langsung</p>
    </div>

    <div class="pos-layout">
        <!-- Kolom Kiri: Produk -->
        <div class="pos-panel">
            <div class="pos-toolbar">
                <div class="pos-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="pos-search" placeholder="Cari produk atau kode...">
                </div>
                <select id="pos-category-select" class="pos-select">
                    <option value="">Semua Kategori</option>
                    <option value="kaos">Kaos</option>
                    <option value="polo">Polo Shirt</option>
                    <option value="topi">Topi</option>
                    <option value="jaket">Hoodie/Jaket</option>
                    <option value="jersey">Jersey</option>
                    <option value="celana">Celana</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>

            <div class="pos-tabs" id="pos-category-tabs">
                <button class="pos-tab active" data-category="">Semua</button>
                <button class="pos-tab" data-category="kaos">Kaos</button>
                <button class="pos-tab" data-category="polo">Polo Shirt</button>
                <button class="pos-tab" data-category="topi">Topi</button>
                <button class="pos-tab" data-category="jaket">Hoodie</button>
            </div>

            <div id="pos-product-grid" class="pos-product-grid">
                <p class="pos-empty-text">Memuat produk...</p>
            </div>

            <div class="pos-pagination" id="pos-pagination"></div>
        </div>

        <!-- Kolom Kanan: Keranjang -->
        <div class="pos-panel pos-cart-panel">
            <div class="pos-cart-header">
                <h3><i class="fas fa-shopping-cart"></i> Keranjang (<span id="pos-cart-count">0</span>)</h3>
                <button type="button" id="pos-clear-cart" class="pos-btn-outline-danger">
                    <i class="fas fa-trash"></i> Kosongkan
                </button>
            </div>

            <div class="pos-cart-table-wrapper">
                <table class="pos-cart-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="pos-cart-tbody">
                        <tr><td colspan="5" class="pos-empty-text">Belum ada item</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="pos-summary">
                <div class="pos-summary-row">
                    <span>Subtotal</span>
                    <span id="pos-subtotal">Rp 0</span>
                </div>
                <div class="pos-summary-row">
                    <span>Diskon</span>
                    <div class="pos-discount-input">
                        <input type="number" id="pos-discount" value="0" min="0" max="100">
                        <span>%</span>
                    </div>
                </div>
                <div class="pos-summary-row pos-summary-total">
                    <span>Total</span>
                    <span id="pos-total">Rp 0</span>
                </div>
            </div>

            <div class="pos-payment-method">
                <label>Metode Pembayaran</label>
                <select id="pos-payment-method-select" class="pos-select">
                    <option value="cash">Tunai</option>
                    <option value="qris">QRIS</option>
                </select>
            </div>

            <div id="pos-cash-input-wrapper" class="pos-cash-wrapper">
                <label>Uang Diterima</label>
                <input type="number" id="pos-cash-received" placeholder="0" class="pos-input">
                <p class="pos-change-text">Kembalian: <span id="pos-change">Rp 0</span></p>
            </div>

            <div class="pos-action-buttons">
                <button type="button" id="pos-save-cart-btn" class="pos-btn-secondary">
                    <i class="fas fa-save"></i> Simpan Keranjang
                </button>
                <button type="button" id="pos-checkout-btn" class="pos-btn-primary">
                    <i class="fas fa-credit-card"></i> Bayar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Struk -->
    <div id="pos-receipt-modal" class="pos-modal-overlay">
        <div class="pos-modal">
            <div id="pos-receipt-content"></div>
            <div class="pos-modal-actions">
                <button onclick="window.print()" class="pos-btn-primary">Cetak</button>
                <button onclick="posResetTransaction()" class="pos-btn-secondary">Transaksi Baru</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        window.posRoutes = {
            products: "{{ route('admin.pos.products') }}",
            checkout: "{{ route('admin.pos.checkout') }}",
        };
    </script>
    @vite(['resources/js/admin/pos.js'])
    @endpush
</x-admin-layout>