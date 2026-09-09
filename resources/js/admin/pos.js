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

    const options = product.variants.map((v, i) => `${i + 1}. ${v.color || '-'} / ${v.size || '-'} (stok: ${v.stock})`).join('\n');
    const choice = prompt(`Pilih varian untuk ${product.name}:\n${options}\n\nMasukkan nomor:`);
    const index = parseInt(choice) - 1;

    if (index >= 0 && index < product.variants.length) {
        posAddToCart(product, product.variants[index]);
    }
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
                    <div class="pos-cart-product-variant">${item.color || ''} ${item.size || ''}</div>
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