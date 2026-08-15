const formatIDR = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(Math.max(0, value || 0));

document.addEventListener('DOMContentLoaded', () => {
    const cartContent = document.getElementById('cart-content');
    if (!cartContent) {
        return;
    }

    const selectAllTop = document.getElementById('select-all-top');
    const selectAllBottom = document.getElementById('select-all-bottom');
    const voucherToggle = document.getElementById('select-voucher');
    const voucherSelect = document.getElementById('voucher-select');
    const checkoutCount = document.getElementById('checkout-count');
    const summarySubtotal = document.getElementById('summary-subtotal');
    const summaryCount = document.getElementById('summary-count');
    const totalPrice = document.getElementById('total-price');
    const subtotalLabel = document.getElementById('subtotal-label');
    const deleteSelectedBtn = document.getElementById('delete-selected-btn');
    const bulkDeleteForm = document.getElementById('bulk-delete-form');
    const itemCountText = document.getElementById('item-count-text');

    const getItemRows = () => Array.from(document.querySelectorAll('.cart-item'));
    const getCheckboxes = () => Array.from(document.querySelectorAll('.cart-item .item-checkbox'));

    const updateSelectAllState = () => {
        const checkboxes = getCheckboxes();
        const allChecked = checkboxes.length > 0 && checkboxes.every((checkbox) => checkbox.checked);
        if (selectAllTop) {
            selectAllTop.checked = allChecked;
        }
        if (selectAllBottom) {
            selectAllBottom.checked = allChecked;
        }
    };

    const calculateTotals = () => {
        let subtotal = 0;
        let selectedQuantity = 0;
        let selectedCount = 0;

        getItemRows().forEach((row) => {
            const checkbox = row.querySelector('.item-checkbox');
            const quantityInput = row.querySelector('.quantity-input');
            const price = parseFloat(row.dataset.price || '0');
            const quantity = Math.max(1, Math.min(99, parseInt(quantityInput?.value || '1', 10)));

            if (quantityInput) {
                quantityInput.value = quantity.toString();
            }

            if (checkbox?.checked) {
                subtotal += price * quantity;
                selectedQuantity += quantity;
                selectedCount += 1;
            }

            const lineTotalEl = row.querySelector('.line-total');
            if (lineTotalEl) {
                lineTotalEl.textContent = formatIDR(price * quantity);
            }
        });

        const discountEnabled = voucherToggle?.checked;
        const rawDiscount = discountEnabled ? parseInt(voucherSelect?.value || '0', 10) : 0;
        const discount = Math.min(Math.max(rawDiscount, 0), subtotal);
        const total = Math.max(0, subtotal - discount);

        if (summarySubtotal) {
            summarySubtotal.textContent = formatIDR(subtotal);
        }
        if (summaryCount) {
            summaryCount.textContent = selectedQuantity.toString();
        }
        if (totalPrice) {
            totalPrice.textContent = formatIDR(total);
        }
        if (checkoutCount) {
            checkoutCount.textContent = `(${selectedCount})`;
        }
        if (subtotalLabel) {
            subtotalLabel.textContent = discount > 0 ? `(Potongan ${formatIDR(discount)})` : '';
        }
        if (itemCountText) {
            itemCountText.textContent = `(${getItemRows().length})`;
        }

        updateSelectAllState();
    };

    const toggleAll = (checked) => {
        getCheckboxes().forEach((checkbox) => {
            checkbox.checked = checked;
        });
        calculateTotals();
    };

    getCheckboxes().forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            calculateTotals();
        });
    });

    selectAllTop?.addEventListener('change', (event) => {
        toggleAll(event.target.checked);
    });

    selectAllBottom?.addEventListener('change', (event) => {
        toggleAll(event.target.checked);
    });

    voucherToggle?.addEventListener('change', () => {
        calculateTotals();
    });

    voucherSelect?.addEventListener('change', () => {
        calculateTotals();
    });

    document.querySelectorAll('.quantity-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const action = button.dataset.action;
            const form = button.closest('form');
            const input = form?.querySelector('.quantity-input');
            const saveButton = form?.querySelector('.save-quantity');
            const cancelButton = form?.querySelector('.cancel-quantity');
            if (!input) {
                return;
            }

            const current = parseInt(input.value || '1', 10) || 1;
            const nextValue = action === 'increase' ? Math.min(99, current + 1) : Math.max(1, current - 1);
            input.value = nextValue.toString();

            if (saveButton) {
                const initial = parseInt(input.dataset.initial || '1', 10) || 1;
                const hasChanged = nextValue !== initial;
                saveButton.classList.toggle('hidden', !hasChanged);
                cancelButton?.classList.toggle('hidden', !hasChanged);
            }

            const row = form?.closest('.cart-item');
            if (row) {
                row.dataset.quantity = nextValue.toString();
            }

            calculateTotals();
        });
    });

    document.querySelectorAll('.quantity-input').forEach((input) => {
        input.addEventListener('input', () => {
            let value = parseInt(input.value || '1', 10) || 1;
            value = Math.max(1, Math.min(99, value));
            input.value = value.toString();

            const form = input.closest('form');
            const saveButton = form?.querySelector('.save-quantity');
            const cancelButton = form?.querySelector('.cancel-quantity');
            if (saveButton) {
                const initial = parseInt(input.dataset.initial || '1', 10) || 1;
                const hasChanged = value !== initial;
                saveButton.classList.toggle('hidden', !hasChanged);
                cancelButton?.classList.toggle('hidden', !hasChanged);
            }

            const row = input.closest('.cart-item');
            if (row) {
                row.dataset.quantity = value.toString();
            }

            calculateTotals();
        });
    });

    document.querySelectorAll('.cancel-quantity').forEach((button) => {
        button.addEventListener('click', () => {
            const form = button.closest('form');
            const input = form?.querySelector('.quantity-input');
            const saveButton = form?.querySelector('.save-quantity');
            if (!input) {
                return;
            }
            const initial = parseInt(input.dataset.initial || '1', 10) || 1;
            input.value = initial.toString();
            button.classList.add('hidden');
            saveButton?.classList.add('hidden');

            const row = form?.closest('.cart-item');
            if (row) {
                row.dataset.quantity = initial.toString();
            }

            calculateTotals();
        });
    });

    deleteSelectedBtn?.addEventListener('click', () => {
        const selectedRows = getItemRows().filter((row) => row.querySelector('.item-checkbox')?.checked);
        if (selectedRows.length === 0) {
            alert('Pilih minimal satu produk untuk dihapus.');
            return;
        }


        if (!confirm('Hapus produk yang dipilih dari keranjang?')) {
            return;
        }

        if (!bulkDeleteForm) {
            return;
        }

        bulkDeleteForm.querySelectorAll('input[name="keys[]"]').forEach((input) => input.remove());

        selectedRows.forEach((row) => {
            const key = row.dataset.key;
            if (!key) {
                return;
            }
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'keys[]';
            hiddenInput.value = key;
            bulkDeleteForm.appendChild(hiddenInput);
        });

        bulkDeleteForm.submit();
    });

    calculateTotals();
});
