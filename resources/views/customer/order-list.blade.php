<x-customer-layout title="Daftar Pesanan" active="order-list">
    @vite(['resources/css/customer/shared.css'])
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.addEventListener('profile-updated', event => {
                const newAvatarUrl = event.detail.avatarUrl;
                document.querySelectorAll('.header-avatar').forEach(img => {
                    img.src = newAvatarUrl;
                });
            });
        });
    </script>
    
    <div class="max-w-7xl mx-auto px-2 sm:px-3 lg:px-4 py-2">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-3 sticky top-0 z-10">
                    <h3 class="text-lg font-semibold mb-4">Filter Pesanan</h3>
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- Kategori Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Pesanan</label>
                            <select name="kategori" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                <option value="">Semua Tipe</option>
                                <option value="regular" {{ request('kategori') === 'regular' ? 'selected' : '' }}>Reguler</option>
                                <option value="custom" {{ request('kategori') === 'custom' ? 'selected' : '' }}>Custom Design</option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Diproses</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                        </div>

                        <!-- Tanggal Mulai -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                            <input type="date" name="tgl_mulai" value="{{ request('tgl_mulai') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        </div>

                        <!-- Tanggal Akhir -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                            <input type="date" name="tgl_akhir" value="{{ request('tgl_akhir') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm shadow-sm">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <a href="{{ route('order-list') }}" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium text-center text-sm border border-gray-300">
                                <i class="fas fa-redo mr-1"></i> Reset
                            </a>
                        </div>
            </form>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 text-green-800 px-4 py-3 rounded-lg mb-6 shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 text-red-800 px-4 py-3 rounded-lg mb-6 shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Orders List -->
        @forelse($orders as $order)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                @if($order instanceof \App\Models\CustomDesignOrder)
                                    <span class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded mr-2">
                                        <i class="fas fa-palette mr-1"></i> Custom Design
                                    </span>
                                @endif
                                Pesanan #{{ $order->id }}
                            </h3>
                            <p class="text-sm text-gray-600">
                                @if($order instanceof \App\Models\Order && $order->formatted_last_action)
                                    {{ $order->formatted_last_action }}
                                @else
                                    {{ $order->created_at->format('d M Y, H:i') }}
                                @endif
                            </p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($order->status === 'approved') bg-green-100 text-green-800
                            @elseif($order->status === 'rejected') bg-red-100 text-red-800
                            @elseif($order->status === 'cancelled') bg-gray-100 text-gray-800
                            @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                            @elseif($order->status === 'completed') bg-purple-100 text-purple-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            @if($order->status === 'pending')
                                Menunggu Konfirmasi
                            @elseif($order->status === 'approved')
                                Disetujui
                            @elseif($order->status === 'rejected')
                                Ditolak
                            @elseif($order->status === 'cancelled')
                                Dibatalkan
                            @elseif($order->status === 'processing')
                                Diproses
                            @elseif($order->status === 'completed')
                                Selesai
                            @else
                                {{ ucfirst($order->status) }}
                            @endif
                        </span>
                    </div>

                    @if($order instanceof \App\Models\CustomDesignOrder)
                        {{-- Custom Design Order --}}
                        <div class="flex items-start space-x-4">
                            @php
                                // Priority: first upload image > variant->image > product->image > placeholder
                                $orderImage = null;
                                if ($order->uploads && $order->uploads->count() > 0) {
                                    $firstUpload = $order->uploads->first();
                                    $orderImage = asset('storage/' . $firstUpload->file_path);
                                } elseif ($order->variant && !empty($order->variant->image)) {
                                    $orderImage = str_starts_with($order->variant->image, 'http') 
                                        ? $order->variant->image 
                                        : asset('storage/' . $order->variant->image);
                                } elseif ($order->product && !empty($order->product->image)) {
                                    $orderImage = str_starts_with($order->product->image, 'http') 
                                        ? $order->product->image 
                                        : asset('storage/' . $order->product->image);
                                }
                            @endphp
                            
                            @if($orderImage)
                                <img src="{{ $orderImage }}" alt="{{ $order->product_name }}" class="w-20 h-20 object-cover rounded flex-shrink-0">
                            @else
                                <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center flex-shrink-0">
                                    <span class="text-gray-500 text-xs">No Image</span>
                                </div>
                            @endif

                            <div class="flex-1 min-w-0">
                                {{-- Product Info & Price Row --}}
                                <div class="flex justify-between items-start gap-4 mb-2">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">{{ $order->product_name }}</h4>
                                        <p class="text-sm text-gray-600">
                                            Custom Design
                                            @if($order->variant)
                                                @if($order->variant->color) • {{ $order->variant->color }}@endif
                                                @if($order->variant->size) • {{ $order->variant->size }}@endif
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-lg font-bold text-gray-900">Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}</p>
                                        <p class="text-xs text-gray-500">Produk + Custom Design</p>
                                    </div>
                                </div>

                                {{-- Cutting & Design Parts --}}
                                <div class="flex gap-4 text-sm text-gray-600 mb-2">
                                    <p><i class="fas fa-cut"></i> Cutting: <span class="font-medium">{{ $order->cutting_type }}</span></p>
                                    <p><i class="fas fa-images"></i> Bagian: <span class="font-medium">{{ $order->uploads->count() }}</span></p>
                                </div>

                                {{-- File Design Compact --}}
                                @if($order->uploads->count() > 0)
                                <div class="bg-gray-50 p-2 rounded text-xs mb-3 max-h-12 overflow-y-auto">
                                    <p class="font-medium text-gray-700 mb-1">File Desain:</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($order->uploads as $upload)
                                        <span class="bg-white px-2 py-1 rounded border border-gray-200">
                                            <i class="fas fa-file-image text-blue-500 text-xs"></i> {{ $upload->section_name }} ({{ number_format($upload->file_size / 1024, 1) }} KB)
                                        </span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                {{-- Status Info --}}
                                @if($order->status === 'pending')
                                <p class="text-sm text-yellow-600 font-medium mb-3">
                                    <i class="fas fa-clock"></i> Menunggu konfirmasi admin
                                </p>
                                @elseif($order->status === 'rejected')
                                <div class="bg-red-50 p-2 rounded text-sm mb-3">
                                    <p class="text-red-600 font-medium">Alasan Penolakan:</p>
                                    <p class="text-red-800">{{ $order->admin_notes ?? 'Tidak ada catatan' }}</p>
                                </div>
                                @endif

                                {{-- Action Buttons --}}
                                <div class="flex justify-end gap-2 flex-wrap">
                                    @php
                                        // Check if order has active VA
                                        $hasActiveVA = \App\Models\VirtualAccount::where('user_id', auth()->id())
                                            ->where('order_type', 'custom')
                                            ->where('order_id', $order->id)
                                            ->where('status', 'pending')
                                            ->where('expired_at', '>', now())
                                            ->exists();
                                    @endphp
                                    
                                    @if($order->status === 'approved' && $order->payment_deadline && $order->payment_deadline->isFuture())
                                        @if($hasActiveVA)
                                            {{-- Has VA: Only show "Status Pembayaran" button --}}
                                            <a href="{{ route('payment-status', ['type' => 'custom', 'order_id' => $order->id]) }}" 
                                               class="px-3 py-1 bg-purple-600 text-white text-xs rounded-lg hover:bg-purple-700 transition whitespace-nowrap">
                                                <i class="fas fa-receipt mr-1"></i> Status Pembayaran
                                            </a>
                                        @else
                                            {{-- No VA: Show "Detail" and "Bayar" buttons --}}
                                            <a href="{{ route('order-detail', ['type' => 'custom', 'id' => $order->id]) }}" 
                                               class="px-3 py-1 bg-blue-600 text-white text-xs rounded-lg hover:bg-blue-700 transition whitespace-nowrap">
                                                <i class="fas fa-eye mr-1"></i> Detail
                                            </a>
                                            <a href="{{ route('alamat') }}?order_type=custom&order_id={{ $order->id }}" 
                                               class="px-3 py-1 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700 transition whitespace-nowrap">
                                                <i class="fas fa-credit-card mr-1"></i> Bayar
                                            </a>
                                        @endif
                                    @elseif(in_array($order->status, ['processing', 'completed']))
                                        <a href="{{ route('payment-status', ['type' => 'custom', 'order_id' => $order->id]) }}" 
                                           class="px-3 py-1 bg-purple-600 text-white text-xs rounded-lg hover:bg-purple-700 transition whitespace-nowrap">
                                            <i class="fas fa-receipt mr-1"></i> Status Pembayaran
                                        </a>
                                    @endif
                                    
                                    @if($order->status === 'pending')
                                    <button onclick="cancelOrder('custom', {{ $order->id }})" 
                                            class="px-3 py-1 bg-red-600 text-white text-xs rounded-lg hover:bg-red-700 transition whitespace-nowrap">
                                        <i class="fas fa-times mr-1"></i> Batalkan
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Regular Order --}}
                        <div class="space-y-3 mb-4">
                            @php
                                // Order model's retrieved hook already converts items to array
                                $items = is_array($order->items) ? $order->items : [];
                            @endphp
                            @foreach($items as $item)
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                <div class="flex items-center space-x-3">
                                    @if(isset($item['image']) && $item['image'])
                                        <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] ?? 'Product' }}" class="w-12 h-12 object-cover rounded">
                                    @else
                                        <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                            <span class="text-gray-500 text-xs">No Image</span>
                                        </div>
                                    @endif
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ $item['name'] ?? 'Unknown Product' }}</h4>
                                        <p class="text-sm text-gray-600">
                                            @if(isset($item['color']) && $item['color']) Warna: {{ $item['color'] }} @endif
                                            @if(isset($item['size']) && $item['size']) Ukuran: {{ $item['size'] }} @endif
                                            @if(isset($item['quantity'])) Qty: {{ $item['quantity'] }} @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @php
                                        $price = isset($item['price']) ? (float)$item['price'] : 0;
                                        $qty = isset($item['quantity']) ? (int)$item['quantity'] : 0;
                                    @endphp
                                    <p class="font-medium text-gray-900">Rp {{ number_format($price * $qty, 0, ',', '.') }}</p>
                                    <p class="text-sm text-gray-600">Rp {{ number_format($price, 0, ',', '.') }} x {{ $qty }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="pt-4 border-t border-gray-200">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <p class="text-sm text-gray-600">Total Pesanan</p>
                                    <p class="text-lg font-bold text-gray-900">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</p>
                                </div>
                                @if($order->status === 'pending')
                                <div class="text-right">
                                    <p class="text-sm text-yellow-600 font-medium">
                                        <i class="fas fa-clock"></i> Menunggu konfirmasi admin
                                    </p>
                                </div>
                                @elseif($order->status === 'rejected' && $order->admin_notes)
                                <div class="text-right">
                                    <p class="text-sm text-red-600 font-medium">Alasan Penolakan:</p>
                                    <p class="text-sm text-red-800">{{ $order->admin_notes }}</p>
                                </div>
                                @endif
                            </div>
                            
                            {{-- Action Buttons --}}
                            <div class="flex justify-end gap-2 mt-3">
                                @php
                                    // Check if order has active VA
                                    $hasActiveVA = \App\Models\VirtualAccount::where('user_id', auth()->id())
                                        ->where('order_type', 'regular')
                                        ->where('order_id', $order->id)
                                        ->where('status', 'pending')
                                        ->where('expired_at', '>', now())
                                        ->exists();
                                @endphp
                                
                                @if($order->status === 'approved' && $order->payment_deadline && $order->payment_deadline->isFuture())
                                    @if($hasActiveVA)
                                        {{-- Has VA: Only show "Status Pembayaran" button --}}
                                        <a href="{{ route('payment-status', ['type' => 'regular', 'order_id' => $order->id]) }}" 
                                           class="px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition">
                                            <i class="fas fa-receipt mr-1"></i> Status Pembayaran
                                        </a>
                                    @else
                                        {{-- No VA: Show "Detail" and "Bayar" buttons --}}
                                        <a href="{{ route('order-detail', ['type' => 'regular', 'id' => $order->id]) }}" 
                                           class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition">
                                            <i class="fas fa-eye mr-1"></i> Detail
                                        </a>
                                        <a href="{{ route('alamat') }}?order_type=regular&order_id={{ $order->id }}" 
                                           class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition">
                                            <i class="fas fa-credit-card mr-1"></i> Bayar
                                        </a>
                                    @endif
                                @elseif(in_array($order->status, ['processing', 'completed']))
                                    <a href="{{ route('payment-status', ['type' => 'regular', 'order_id' => $order->id]) }}" 
                                       class="px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition">
                                        <i class="fas fa-receipt mr-1"></i> Status Pembayaran
                                    </a>
                                @endif
                                
                                @if($order->status === 'pending')
                                <button onclick="cancelOrder('regular', {{ $order->id }})" 
                                        class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition">
                                    <i class="fas fa-times mr-1"></i> Batalkan
                                </button>
                                @endif
                            </div>
                        </div>
                    @endif
            </div>
        @empty
        <div class="p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                <i class="fas fa-shopping-bag text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum ada pesanan</h3>
            <p class="text-gray-600 mb-6 max-w-md mx-auto">Anda belum memiliki pesanan apapun. Mulai jelajahi katalog produk kami!</p>
            <a href="{{ route('catalog', ['category' => 'kaos']) }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-sm">
                <i class="fas fa-shopping-cart mr-2"></i>
                Mulai Belanja
            </a>
        </div>
        @endforelse

        <!-- Pagination -->
        @if($orders->hasPages())
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
        @endif
    </div>    <script>
        // Cancel order function
        async function cancelOrder(type, orderId) {
            if (!confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
                return;
            }
            
            try {
                const response = await fetch(`/order/${type}/${orderId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Pesanan berhasil dibatalkan');
                    location.reload();
                } else {
                    alert(data.message || 'Gagal membatalkan pesanan');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat membatalkan pesanan');
            }
        }
    </script>

    @stack('scripts')
</x-customer-layout>