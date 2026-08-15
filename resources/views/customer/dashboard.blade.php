<x-customer-layout title="Dashboard" active="dashboard">
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.addEventListener('profile-updated', event => {
                const newAvatarUrl = event.detail.avatarUrl;
                document.querySelectorAll('.header-avatar').forEach(img => {
                    img.src = newAvatarUrl;
                });
            });
            
            // Realtime dashboard update every 30 seconds
            const dashboardUpdater = {
                statsContainer: null,
                ordersContainer: null,
                updateInterval: 30000, // 30 seconds
                
                init() {
                    this.statsContainer = document.querySelector('[data-stats-container]');
                    this.ordersContainer = document.querySelector('[data-orders-container]');
                    
                    if (this.statsContainer || this.ordersContainer) {
                        this.startUpdating();
                    }
                },
                
                startUpdating() {
                    // Initial update
                    this.updateDashboard();
                    
                    // Update every 30 seconds
                    setInterval(() => this.updateDashboard(), this.updateInterval);
                },
                
                updateDashboard() {
                    Promise.all([
                        this.fetchStats(),
                        this.fetchOrders()
                    ]).catch(error => {
                        console.error('Dashboard update error:', error);
                    });
                },
                
                fetchStats() {
                    return fetch('{{ route("api.customer.dashboard-stats") }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && this.statsContainer) {
                            this.updateStatsUI(data.data);
                        }
                    });
                },
                
                fetchOrders() {
                    return fetch('{{ route("api.customer.dashboard-orders") }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && this.ordersContainer) {
                            this.updateOrdersUI(data.data);
                        }
                    });
                },
                
                updateStatsUI(stats) {
                    const statCards = document.querySelectorAll('[data-stat-card]');
                    if (statCards.length >= 4) {
                        const formatCurrency = (value) => 'Rp ' + Number(value).toLocaleString('id-ID', {maximumFractionDigits: 0});
                        
                        statCards[0].querySelector('p.text-2xl').textContent = formatCurrency(stats.totalSpent);
                        statCards[1].querySelector('p.text-2xl').textContent = stats.totalItems;
                        statCards[2].querySelector('p.text-2xl').textContent = stats.completedItems;
                        statCards[3].querySelector('p.text-2xl').textContent = stats.cancelledItems;
                    }
                },
                
                updateOrdersUI(orders) {
                    const tbody = this.ordersContainer.querySelector('tbody');
                    if (!tbody) return;
                    
                    const statusBadgeClass = (status) => {
                        const classes = {
                            'pending': 'bg-yellow-100 text-yellow-800',
                            'approved': 'bg-blue-100 text-blue-800',
                            'processing': 'bg-blue-100 text-blue-800',
                            'completed': 'bg-green-100 text-green-800',
                            'rejected': 'bg-red-100 text-red-800',
                            'cancelled': 'bg-red-100 text-red-800',
                        };
                        return classes[status] || 'bg-gray-100 text-gray-800';
                    };
                    
                    const formatCurrency = (value) => 'Rp ' + Number(value).toLocaleString('id-ID', {maximumFractionDigits: 0});
                    
                    if (orders.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="py-8 text-center text-gray-500">Belum ada pesanan</td></tr>';
                        return;
                    }
                    
                    tbody.innerHTML = orders.map(order => `
                        <tr class="text-sm hover:bg-gray-50">
                            <td class="py-3">${order.id}</td>
                            <td class="py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-medium ${statusBadgeClass(order.status)}">
                                    ${order.statusLabel}
                                </span>
                            </td>
                            <td class="py-3">
                                ${order.product}
                                ${order.moreProducts > 0 ? `<span class="text-gray-500">+${order.moreProducts} lainnya</span>` : ''}
                            </td>
                            <td class="py-3 text-gray-600">${order.date}</td>
                            <td class="py-3 font-semibold">${formatCurrency(order.total)}</td>
                        </tr>
                    `).join('');
                }
            };
            
            dashboardUpdater.init();
        });
    </script>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-4 gap-6 mb-6" data-stats-container>
        <div class="bg-white p-4 rounded-lg shadow" data-stat-card>
            <h3 class="text-sm text-gray-500 mb-2">Total Akumulasi</h3>
            <p class="text-2xl font-bold mb-1">Rp {{ number_format($totalSpent, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500">*Akumulasi belanja selama 365 hari</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow" data-stat-card>
            <h3 class="text-sm text-gray-500 mb-2">Total Barang</h3>
            <p class="text-2xl font-bold mb-1">{{ $totalItems }}</p>
            <p class="text-xs text-gray-500">*Akumulasi belanja selama 365 hari</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow" data-stat-card>
            <h3 class="text-sm text-gray-500 mb-2">Barang Selesai</h3>
            <p class="text-2xl font-bold mb-1">{{ $completedItems }}</p>
            <p class="text-xs text-gray-500">*Akumulasi belanja selama 365 hari</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow" data-stat-card>
            <h3 class="text-sm text-gray-500 mb-2">Barang Dibatalkan</h3>
            <p class="text-2xl font-bold mb-1">{{ $cancelledItems }}</p>
            <p class="text-xs text-gray-500">*Akumulasi belanja selama 365 hari</p>
        </div>
    </div>

    <!-- Customer Info and Orders -->
    <div class="grid grid-cols-3 gap-6">
        <!-- Customer Info -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-bold mb-4">Info Customer</h2>
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-gray-500">Name</label>
                    <input type="text" value="{{ auth()->user()->name }}" class="w-full border rounded-lg p-2" readonly>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Email</label>
                    <input type="email" value="{{ auth()->user()->email }}" class="w-full border rounded-lg p-2" readonly>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Telepon</label>
                    <input type="tel" value="{{ auth()->user()->phone ?? '-' }}" class="w-full border rounded-lg p-2" readonly>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Alamat pengiriman</label>
                    <textarea class="w-full border rounded-lg p-2" readonly>{{ auth()->user()->address ?? 'Belum diatur' }}</textarea>
                </div>
            </div>
        </div>

        <!-- Orders -->
        <div class="col-span-2">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-bold mb-4">Orders Terbaru</h2>
                
                <!-- Orders Table -->
                <div class="overflow-x-auto" data-orders-container>
                    <table class="w-full">
                        <thead>
                            <tr class="text-left border-b">
                                <th class="pb-3">ID Order</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3">Produk</th>
                                <th class="pb-3">Tanggal</th>
                                <th class="pb-3">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($recentOrders as $order)
                            <tr class="text-sm hover:bg-gray-50">
                                <td class="py-3">
                                    @if($order->order_type === 'regular')
                                        {{ $order->order_number ?? '#' . str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                    @else
                                        CDO-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                    @endif
                                </td>
                                <td class="py-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium
                                        @if($order->status === 'pending')
                                            bg-yellow-100 text-yellow-800
                                        @elseif($order->status === 'approved')
                                            bg-blue-100 text-blue-800
                                        @elseif($order->status === 'processing')
                                            bg-blue-100 text-blue-800
                                        @elseif($order->status === 'completed')
                                            bg-green-100 text-green-800
                                        @elseif($order->status === 'rejected' || $order->status === 'cancelled')
                                            bg-red-100 text-red-800
                                        @else
                                            bg-gray-100 text-gray-800
                                        @endif">
                                        @if($order->status === 'pending')
                                            Menunggu
                                        @elseif($order->status === 'approved')
                                            Disetujui
                                        @elseif($order->status === 'processing')
                                            Diproses
                                        @elseif($order->status === 'completed')
                                            Selesai
                                        @elseif($order->status === 'rejected')
                                            Ditolak
                                        @elseif($order->status === 'cancelled')
                                            Dibatalkan
                                        @else
                                            {{ ucfirst($order->status) }}
                                        @endif
                                    </span>
                                </td>
                                <td class="py-3">
                                    @if($order->order_type === 'regular')
                                        @php
                                            $items = is_array($order->items) ? $order->items : [];
                                            $productNames = collect($items)->pluck('name')->unique();
                                        @endphp
                                        {{ $productNames->first() ?? 'N/A' }}
                                        @if(count($productNames) > 1)
                                            <span class="text-gray-500">+{{ count($productNames) - 1 }} lainnya</span>
                                        @endif
                                    @else
                                        {{ $order->product_name ?? 'Custom Design' }}
                                    @endif
                                </td>
                                <td class="py-3 text-gray-600">{{ $order->created_at->format('d M Y') }}</td>
                                <td class="py-3 font-semibold">Rp {{ number_format($order->order_type === 'custom' ? ($order->total_price ?? 0) : ($order->total ?? 0), 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500">
                                    Belum ada pesanan
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 text-center">
                    <a href="{{ route('order-list') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Lihat semua pesanan →
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-customer-layout>