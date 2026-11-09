let posProducts = [];
let posCart = [];
let posPaymentMethod = 'cash';
let posActiveCategory = '';

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function formatRupiah(number) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(number));
}

// Ambil produk dari server
async function posLoadProducts(search = '') {
    const grid = document.getElementById('pos-product-grid');
    grid.innerHTML = '<p class="pos-empty-text">Memuat produk...</p>';

    try {
        const params = new URLSearchParams({ search, category: posActiveCategory });
        const res = await fetch(`${window.posRoutes.products}?${params.toString()}`);
        const data = await res.json();

        if (!data.success) throw new Error('Gagal memuat produk');

        posProducts = data.data;
        renderProductGrid();
    } catch (err) {
        grid.innerHTML = '<p class="pos-empty-text">Gagal memuat produk</p>';
    }
}

function renderProductGrid() {
    const grid = document.getElementById('pos-product-grid');

    if (posProducts.length === 0) {
        grid.innerHTML = '<p class="pos-empty-text">Produk tidak ditemukan</p>';
        return;
    }

    grid.innerHTML = posProducts.map(product => `
        <div class="pos-product-card">
            <div class="pos-product-image">
                ${product.image ? `<img src="${product.image}" alt="${product.name}">` : '<i class="fas fa-image"></i>'}
            </div>
            <p class="pos-product-name">${product.name}</p>
            <p class="pos-product-price">${formatRupiah(product.price)}</p>
            <button type="button" class="pos-add-btn" onclick="posOpenVariantPicker(${product.id})">
                + Tambah
            </button>
        </div>
    `).join('');
}

// Tab kategori
document.querySelectorAll('.pos-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.pos-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        posActiveCategory = this.dataset.category;
        document.getElementById('pos-category-select').value = posActiveCategory;
        posLoadProducts(document.getElementById('pos-search').value);
    });
});

document.getElementById('pos-category-select').addEventListener('change', function () {
    posActiveCategory = this.value;
    document.querySelectorAll('.pos-tab').forEach(t => {
        t.classList.toggle('active', t.dataset.category === posActiveCategory);
    });
    posLoadProducts(document.getElementById('pos-search').value);
});

// Pilih varian produk (kalau ada) lalu tambahkan ke cart
function posOpenVariantPicker(productId) {
    const product = posProducts.find(p => p.id === productId);
    if (!product) return;

    if (product.variants.length === 0) {
        posAddToCart(product, null);
        return;
    }

    if (product.variants.length === 1) {
        posAddToCart(product, product.variants[0]);
        return;
    }

    posShowVariantModal(product);
}

// ===== Pop-up pilih varian (pengganti prompt) =====
function posEscape(s) {
    return String(s ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
}

function posIsCssColor(value) {
    return !!value && window.CSS && CSS.supports('color', value);
}

function posVariantEscHandler(e) {
    if (e.key === 'Escape') posCloseVariantModal();
}

function posCloseVariantModal() {
    const el = document.getElementById('pos-variant-modal');
    if (el) el.remove();
    document.removeEventListener('keydown', posVariantEscHandler);
}

function posShowVariantModal(product) {
    posCloseVariantModal();

    const variants = product.variants;
    const colors = [...new Set(variants.map(v => v.color).filter(Boolean))];
    const sizes = [...new Set(variants.map(v => v.size).filter(Boolean))];
    let selColor = colors.length === 1 ? colors[0] : null;
    let selSize = sizes.length === 1 ? sizes[0] : null;

    const hasStock = (c, s) => variants.some(v =>
        Number(v.stock) > 0 && (!c || v.color === c) && (!s || v.size === s));

    const findVariant = () => {
        if (colors.length && !selColor) return null;
        if (sizes.length && !selSize) return null;
        return variants.find(v =>
            (!colors.length || v.color === selColor) &&
            (!sizes.length || v.size === selSize)) || null;
    };

    const overlay = document.createElement('div');
    overlay.id = 'pos-variant-modal';
    overlay.className = 'pos-variant-overlay';
    document.body.appendChild(overlay);
    document.addEventListener('keydown', posVariantEscHandler);

    function render() {
        const variant = findVariant();
        const canAdd = !!variant && Number(variant.stock) > 0;

        let info = 'Pilih warna dan ukuran';
        if (colors.length && !selColor) info = 'Pilih warna terlebih dahulu';
        else if (sizes.length && !selSize) info = 'Pilih ukuran';
        else if (variant && Number(variant.stock) <= 0) info = 'Stok habis';
        else if (variant) info = `Stok: ${variant.stock} &middot; ${formatRupiah(variant.price ?? product.price)}`;

        const colorHtml = colors.length ? `
            <div class="pos-variant-group">
                <div class="pos-variant-label">Warna ${selColor ? `<span>${posEscape(selColor)}</span>` : ''}</div>
                <div class="pos-variant-swatches">
                    ${colors.map(c => {
                        const ok = hasStock(c, selSize);
                        const css = posIsCssColor(c);
                        return `<button type="button" class="pos-swatch ${c === selColor ? 'active' : ''}"
                                    data-color="${posEscape(c)}" ${ok ? '' : 'disabled'}
                                    title="${posEscape(c)}" aria-label="Warna ${posEscape(c)}"
                                    style="${css ? `background:${posEscape(c)}` : ''}">${css ? '' : '?'}</button>`;
                    }).join('')}
                </div>
            </div>` : '';

        const sizeHtml = sizes.length ? `
            <div class="pos-variant-group">
                <div class="pos-variant-label">Ukuran ${selSize ? `<span>${posEscape(selSize)}</span>` : ''}</div>
                <div class="pos-variant-sizes">
                    ${sizes.map(s => `
                        <button type="button" class="pos-size-chip ${s === selSize ? 'active' : ''}"
                                data-size="${posEscape(s)}" ${hasStock(selColor, s) ? '' : 'disabled'}>${posEscape(s)}</button>
                    `).join('')}
                </div>
            </div>` : '';

        overlay.innerHTML = `
            <div class="pos-variant-box" role="dialog" aria-modal="true">
                <div class="pos-variant-head">
                    <div>
                        <h3>${posEscape(product.name)}</h3>
                        <p>Pilih varian produk</p>
                    </div>
                    <button type="button" class="pos-variant-close" data-act="close" aria-label="Tutup">&times;</button>
                </div>
                ${colorHtml}
                ${sizeHtml}
                <div class="pos-variant-info">${info}</div>
                <div class="pos-variant-actions">
                    <button type="button" class="pos-btn-secondary" data-act="close">Batal</button>
                    <button type="button" class="pos-btn-primary" data-act="add" ${canAdd ? '' : 'disabled'}>
                        Tambah ke Keranjang
                    </button>
                </div>
            </div>`;
    }

    overlay.addEventListener('click', e => {
        if (e.target === overlay) { posCloseVariantModal(); return; }

        const btn = e.target.closest('[data-act], [data-color], [data-size]');
        if (!btn || btn.disabled) return;

        if (btn.dataset.act === 'close') { posCloseVariantModal(); return; }

        if (btn.dataset.act === 'add') {
            const variant = findVariant();
            if (!variant) return;
            posCloseVariantModal();
            posAddToCart(product, variant);
            return;
        }

        if (btn.dataset.color !== undefined) {
            selColor = btn.dataset.color;
            if (selSize && !hasStock(selColor, selSize)) selSize = null;
        } else if (btn.dataset.size !== undefined) {
            selSize = btn.dataset.size;
            if (selColor && !hasStock(selColor, selSize)) selColor = null;
        }
        render();
    });

    render();
}

function posAddToCart(product, variant) {
    const key = product.id + '-' + (variant ? variant.id : 'base');
    const existing = posCart.find(item => item.key === key);
    const stock = variant ? variant.stock : product.stock;

    if (existing) {
        if (existing.quantity + 1 > stock) {
            alert('Stok tidak mencukupi');
            return;
        }
        existing.quantity += 1;
    } else {
        if (stock <= 0) {
            alert('Stok habis');
            return;
        }
        posCart.push({
            key,
            product_id: product.id,
            variant_id: variant ? variant.id : null,
            name: product.name,
            color: variant ? variant.color : null,
            size: variant ? variant.size : null,
            price: variant ? variant.price : product.price,
            stock,
            quantity: 1,
        });
    }

    renderCart();
}

function posRemoveFromCart(key) {
    posCart = posCart.filter(item => item.key !== key);
    renderCart();
}

function posUpdateQuantity(key, delta) {
    const item = posCart.find(i => i.key === key);
    if (!item) return;

    const newQty = item.quantity + delta;
    if (newQty < 1) {
        posRemoveFromCart(key);
        return;
    }
    if (newQty > item.stock) {
        alert('Stok tidak mencukupi');
        return;
    }
    item.quantity = newQty;
    renderCart();
}

function posClearCart() {
    if (posCart.length === 0) return;
    if (!confirm('Kosongkan seluruh keranjang?')) return;
    posCart = [];
    renderCart();
}

document.getElementById('pos-clear-cart').addEventListener('click', posClearCart);

function renderCart() {
    const tbody = document.getElementById('pos-cart-tbody');

    if (posCart.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="pos-empty-text">Belum ada item</td></tr>';
    } else {
        tbody.innerHTML = posCart.map(item => `
            <tr>
                <td>
                    <div class="pos-cart-product-name">${item.name}</div>
                    <div class="pos-cart-product-variant">${item.color ? (posIsCssColor(item.color) ? `<span class="pos-cart-swatch" style="background:${item.color}"></span>` : item.color + ' ') : ''}${item.size || ''}</div>
                </td>
                <td>${formatRupiah(item.price)}</td>
                <td>
                    <div class="pos-qty-control">
                        <button type="button" class="pos-qty-btn" onclick="posUpdateQuantity('${item.key}', -1)">-</button>
                        <span class="pos-qty-value">${item.quantity}</span>
                        <button type="button" class="pos-qty-btn" onclick="posUpdateQuantity('${item.key}', 1)">+</button>
                    </div>
                </td>
                <td>${formatRupiah(item.price * item.quantity)}</td>
                <td>
                    <button type="button" class="pos-remove-btn" onclick="posRemoveFromCart('${item.key}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    document.getElementById('pos-cart-count').textContent = posCart.reduce((sum, i) => sum + i.quantity, 0);

    updateSummary();
}

function posGetSubtotal() {
    return posCart.reduce((sum, item) => sum + item.price * item.quantity, 0);
}

function posGetDiscountPercent() {
    return parseFloat(document.getElementById('pos-discount').value) || 0;
}

function posGetTotal() {
    const subtotal = posGetSubtotal();
    const discount = posGetDiscountPercent();
    return subtotal - (subtotal * discount / 100);
}

function updateSummary() {
    const subtotal = posGetSubtotal();
    const total = posGetTotal();

    document.getElementById('pos-subtotal').textContent = formatRupiah(subtotal);
    document.getElementById('pos-total').textContent = formatRupiah(total);
    posUpdateChange();
}

document.getElementById('pos-discount').addEventListener('input', updateSummary);

function posUpdateChange() {
    const cashInput = document.getElementById('pos-cash-received');
    const cashReceived = parseFloat(cashInput.value) || 0;
    const change = cashReceived - posGetTotal();
    document.getElementById('pos-change').textContent = formatRupiah(Math.max(change, 0));
}

document.getElementById('pos-cash-received').addEventListener('input', posUpdateChange);

// Toggle metode pembayaran
document.getElementById('pos-payment-method-select').addEventListener('change', function () {
    posPaymentMethod = this.value;
    document.getElementById('pos-cash-input-wrapper').style.display =
        posPaymentMethod === 'cash' ? 'block' : 'none';
});

// Submit transaksi
document.getElementById('pos-checkout-btn').addEventListener('click', async function () {
    if (posCart.length === 0) {
        alert('Belum ada item di transaksi');
        return;
    }

    const total = posGetTotal();
    const cashReceived = parseFloat(document.getElementById('pos-cash-received').value) || 0;

    if (posPaymentMethod === 'cash' && cashReceived < total) {
        alert('Uang diterima kurang dari total');
        return;
    }

    const payload = {
        items: posCart.map(item => ({
            product_id: item.product_id,
            variant_id: item.variant_id,
            quantity: item.quantity,
        })),
        payment_method: posPaymentMethod,
        cash_received: posPaymentMethod === 'cash' ? cashReceived : null,
    };

    try {
        const res = await fetch(window.posRoutes.checkout, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(payload),
        });

        const data = await res.json();

        if (!data.success) {
            alert(data.message || 'Transaksi gagal');
            return;
        }

        showReceipt(data);
    } catch (err) {
        alert('Terjadi kesalahan saat memproses transaksi');
    }
});

function showReceipt(data) {
    const itemsHtml = posCart.map(item => `
        <div style="display:flex;justify-content:space-between;padding:4px 0;">
            <span>${item.name} x${item.quantity}</span>
            <span>${formatRupiah(item.price * item.quantity)}</span>
        </div>
    `).join('');

    document.getElementById('pos-receipt-content').innerHTML = `
        <h3 style="text-align:center;font-weight:700;margin-bottom:10px;">Struk Transaksi #${data.order_id}</h3>
        <div style="border-top:1px dashed #ccc;border-bottom:1px dashed #ccc;padding:8px 0;margin-bottom:8px;">${itemsHtml}</div>
        <div style="display:flex;justify-content:space-between;font-weight:700;"><span>Total</span><span>${formatRupiah(data.total)}</span></div>
        ${data.cash_received ? `
            <div style="display:flex;justify-content:space-between;font-size:13px;"><span>Tunai</span><span>${formatRupiah(data.cash_received)}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:13px;"><span>Kembalian</span><span>${formatRupiah(data.change)}</span></div>
        ` : ''}
    `;

    document.getElementById('pos-receipt-modal').classList.add('active');
}

function posResetTransaction() {
    posCart = [];
    document.getElementById('pos-cash-received').value = '';
    document.getElementById('pos-discount').value = '0';
    renderCart();
    document.getElementById('pos-receipt-modal').classList.remove('active');
    posLoadProducts();
}

// Simpan keranjang (placeholder - belum ada backend-nya)
document.getElementById('pos-save-cart-btn').addEventListener('click', function () {
    alert('Fitur simpan keranjang akan tersedia di update berikutnya.');
});

// Search produk
let searchTimeout;
document.getElementById('pos-search').addEventListener('input', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => posLoadProducts(this.value), 400);
});

// Inisialisasi
posLoadProducts();
// Expose functions to global scope agar bisa dipanggil dari onclick di HTML
window.posOpenVariantPicker = posOpenVariantPicker;
window.posUpdateQuantity = posUpdateQuantity;
window.posRemoveFromCart = posRemoveFromCart;
window.posResetTransaction = posResetTransaction;