<x-customer-layout title="Keranjang" active="keranjang">
    @vite(['resources/js/customer/cart.js'])

    <div class="mx-auto max-w-full">
                    @if(session('success'))
                        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($cartItems->isEmpty())
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-8 text-center">
                            <div class="mx-auto mb-4 h-16 w-16 rounded-2xl bg-slate-100 flex items-center justify-center">
                                <span class="material-icons text-slate-400 text-4xl">shopping_cart</span>
                            </div>
                            <h2 class="text-lg font-semibold text-slate-900">Keranjangmu kosong</h2>
                            <p class="mt-1 text-sm text-slate-500">Mulai belanja dan tambahkan produk favoritmu.</p>
                            <div class="mt-4">
                                <a href="{{ route('all-products') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800 transition">
                                    Lihat Produk
                                </a>
                            </div>
                        </div>
                    @else
                        @php
                            $formatCurrency = fn($value) => 'Rp ' . number_format($value, 0, ',', '.');
                            $totalItems = $cartItems->count();
                            $subtotal = $cartItems->sum(fn($item) => $item['price'] * $item['quantity']);
                        @endphp

                        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900">Keranjangmu</h2>
                                    <p class="text-sm text-slate-500">Kelola produk pilihanmu sebelum lanjut ke checkout.</p>
                                </div>
                                <div class="flex items-center gap-6">
                                    <div class="text-right">
                                        <p class="text-xs text-slate-500">Subtotal</p>
                                        <p class="text-base font-semibold text-slate-900" id="summary-subtotal">{{ $formatCurrency($subtotal) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-slate-500">Jumlah Produk</p>
                                        <p class="text-base font-semibold text-slate-900" id="summary-count">{{ $totalItems }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="cart-content" class="bg-white rounded-lg shadow-sm border border-slate-200" data-subtotal="{{ $subtotal }}">
                            <div class="grid grid-cols-12 gap-4 p-4 border-b border-slate-200 bg-slate-50 text-sm font-semibold text-slate-700">
                                <div class="col-span-1 flex items-center justify-center">
                                    <input type="checkbox" id="select-all-top" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-400">
                                </div>
                                <div class="col-span-4 flex items-center">Semua Produk</div>
                                <div class="col-span-2 text-center">Harga Satuan</div>
                                <div class="col-span-2 text-center">Kuantitas</div>
                                <div class="col-span-2 text-center">Total Harga</div>
                                <div class="col-span-1 text-center">Aksi</div>
                            </div>

                            <div id="cart-items" class="divide-y divide-slate-200">
                                @foreach($cartItems as $item)
                                    @php
                                        $imageUrl = $item['image'] ? (filter_var($item['image'], FILTER_VALIDATE_URL) ? $item['image'] : asset('storage/' . $item['image'])) : 'https://via.placeholder.com/160';
                                        $lineTotal = $item['price'] * $item['quantity'];
                                    @endphp
                                    <div class="cart-item grid grid-cols-12 gap-4 p-4 hover:bg-slate-50 transition items-center" data-key="{{ $item['key'] }}" data-price="{{ $item['price'] }}" data-quantity="{{ $item['quantity'] }}">
                                        <div class="col-span-1 flex items-center justify-center">
                                            <input type="checkbox" class="item-checkbox h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-400" checked>
                                        </div>
                                        <div class="col-span-4 flex items-center gap-4">
                                            <div class="w-16 h-16 bg-slate-100 rounded-lg overflow-hidden">
                                                <img src="{{ $imageUrl }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-slate-900">{{ $item['name'] }}</span>
                                                <div class="text-xs text-slate-500 mt-1 space-x-1">
                                                    @if($item['color'])
                                                        <span>Warna: {{ $item['color'] }}</span>
                                                    @endif
                                                    @if($item['size'])
                                                        <span>Ukuran: {{ $item['size'] }}</span>
                                                    @endif
                                                </div>
                                                @if(!is_null($item['stock']))
                                                    <div class="mt-1 text-xs text-slate-400">Stok tersedia: {{ $item['stock'] }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-span-2 text-center">
                                            <span class="text-sm text-slate-900">{{ $formatCurrency($item['price']) }}</span>
                                        </div>
                                        <div class="col-span-2 flex justify-center">
                                            <form method="POST" action="{{ route('cart.update', $item['key']) }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white">
                                                @csrf
                                                @method('PATCH')
                                                <button type="button" class="quantity-btn px-2 py-1 text-slate-600 hover:bg-slate-50" data-action="decrease">−</button>
                                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="99" class="quantity-input w-12 px-2 py-1 text-center text-sm outline-none" data-initial="{{ $item['quantity'] }}">
                                                <button type="button" class="quantity-btn px-2 py-1 text-slate-600 hover:bg-slate-50" data-action="increase">+</button>
                                                <button type="submit" class="ml-2 hidden rounded-lg bg-slate-900 px-3 py-1 text-xs font-medium text-white hover:bg-slate-800 transition save-quantity">Simpan</button>
                                                <button type="button" class="ml-2 hidden rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100 transition cancel-quantity">Batal</button>
                                            </form>
                                        </div>
                                        <div class="col-span-2 text-center">
                                            <span class="text-sm font-medium text-slate-900 line-total">{{ $formatCurrency($lineTotal) }}</span>
                                        </div>
                                        <div class="col-span-1 text-center">
                                            <form method="POST" action="{{ route('cart.remove', $item['key']) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm text-rose-600 hover:text-rose-700 font-medium">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="p-4 border-t border-slate-200 bg-white">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <input type="checkbox" id="select-all-bottom" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-400">
                                        <label for="select-all-bottom" class="text-sm text-slate-700">
                                            Pilih semua <span id="item-count-text">({{ $totalItems }})</span>
                                        </label>
                                        <button id="delete-selected-btn" type="button" class="text-sm text-rose-600 hover:text-rose-700 font-medium">
                                            Hapus
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-8">
                                        <div class="text-right">
                                            <div class="text-xs text-slate-500 mb-1">
                                                Total <span id="subtotal-label"></span>
                                            </div>
                                            <div class="text-lg font-bold text-slate-900" id="total-price">
                                                {{ $formatCurrency($subtotal) }}
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('checkout') }}" style="display: inline;">
                                            @csrf
                                            <button id="checkout-btn" type="submit" class="px-8 py-3 bg-yellow-400 hover:bg-yellow-500 text-slate-900 font-semibold rounded-lg transition flex items-center gap-2">
                                                Checkout <span id="checkout-count" class="bg-slate-900 text-white text-xs px-2 py-0.5 rounded">({{ $totalItems }})</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form id="bulk-delete-form" method="POST" action="{{ route('cart.bulk-remove') }}" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>

    <style>
        /* Modal Styles */
        #address-modal {
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }

        #address-modal:not(.hidden) {
            animation: modalFadeIn 0.3s ease-out;
        }

        #address-modal .transform {
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Blur background when modal is active */
        body.modal-active {
            overflow: hidden;
        }

        body.modal-active > div:first-child {
            filter: blur(2px);
            pointer-events: none;
        }

        /* Modal responsive adjustments */
        @media (max-width: 640px) {
            #address-modal .transform {
                margin: 1rem;
                max-width: calc(100vw - 2rem);
            }
        }
    </style>

    @stack('scripts')
</x-customer-layout>
