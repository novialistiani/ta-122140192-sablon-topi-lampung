<x-admin-layout title="Dashboard">
    @push('styles')
    @vite(['resources/css/admin/dashboard.css'])
    @endpush

    <div class="dashboard-content">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <!-- Product Sales Card -->
            <div class="stats-card">
                <div class="flex items-start">
                    <div class="stat-icon-wrapper stat-icon-blue">
                        <i class="fas fa-box-open stat-icon"></i>
                    </div>
                    <div>
                        <p class="stat-content">Produk Terjual</p>
                        <h3 class="stat-value" data-stat="total_sold">{{ number_format($stats['total_sold'] ?? 0) }}</h3>
                        <div class="stat-trend">
                            <span class="stat-badge-{{ ($soldTrend ?? 0) >= 0 ? 'up' : 'down' }}">
                                <i class="fas fa-arrow-{{ ($soldTrend ?? 0) >= 0 ? 'up' : 'down' }}"></i> {{ abs($soldTrend ?? 0) }}%
                            </span>
                            <span class="stat-label-small">Dibandingkan dengan bulan lalu</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Card -->
            <div class="stats-card">
                <div class="flex items-start">
                    <div class="stat-icon-wrapper stat-icon-green">
                        <i class="fas fa-chart-line stat-icon"></i>
                    </div>
                    <div>
                        <p class="stat-content">Hasil Pendapatan</p>
                        <h3 class="stat-value" data-stat="revenue">Rp {{ number_format($stats['revenue'] ?? 0, 0, ',', '.') }}</h3>
                        <div class="stat-trend">
                            <span class="stat-badge-up">
                                <i class="fas fa-arrow-up"></i> Data Real-time
                            </span>
                            <span class="stat-label-small">Pesanan yang selesai</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customers Card -->
            <div class="stats-card">
                <div class="flex items-start">
                    <div class="stat-icon-wrapper stat-icon-purple">
                        <i class="fas fa-users stat-icon"></i>
                    </div>
                    <div>
                        <p class="stat-content">Pembeli</p>
                        <h3 class="stat-value" data-stat="customers">{{ number_format($stats['customers'] ?? 0) }}</h3>
                        <div class="stat-trend">
                            <span class="stat-badge-up">
                                <i class="fas fa-arrow-up"></i> Terdaftar
                            </span>
                            <span class="stat-label-small">Pelanggan yang terverifikasi</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="charts-row">
            <!-- Sales Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h2 class="chart-title">Grafik Penjualan</h2>
                    <button class="chart-menu-btn">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
                <div class="chart-wrapper">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Top Products -->
            <div class="chart-card">
                <div class="chart-header">
                    <h2 class="chart-title">Produk Terlaris</h2>
                    <button class="chart-menu-btn">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
                <div class="product-list">
                    @forelse($topProducts ?? [] as $product)
                    <div class="product-item">
                        <div class="product-icon-wrapper product-icon-{{ ['blue', 'green', 'yellow', 'purple', 'pink'][$loop->index] ?? 'blue' }}">
                            <i class="fas fa-box product-icon"></i>
                        </div>
                        <div class="product-info">
                            <p class="product-name">{{ $product['name'] }}</p>
                            <p class="product-sales">{{ $product['variant_count'] }} variant</p>
                        </div>
                        <a href="{{ route('admin.all-products.detail', $product['id']) }}" class="product-price" style="text-decoration: none; color: inherit; cursor: pointer;">Lihat detail</a>
                    </div>
                    @empty
                    <div class="product-item">
                        <p class="text-gray-500 text-center py-4 col-span-full">Tidak ada data produk</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="table-card">
            <div class="table-header">
                <h2 class="chart-title">Pesanan Terbaru</h2>
                <button class="chart-menu-btn">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>

            <div style="overflow-x: auto;">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th class="table-checkbox-cell">
                                <input type="checkbox">
                            </th>
                            <th class="table-th">Produk</th>
                            <th class="table-th">ID Pesanan</th>
                            <th class="table-th">Tanggal</th>
                            <th class="table-th">Nama Pelanggan</th>
                            <th class="table-th">Status</th>
                            <th class="table-th">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody id="recent-orders-tbody">
                        <tr class="table-row">
                            <td colspan="7" class="table-td text-center py-4 text-gray-500">
                                Memuat pesanan terbaru...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/js/admin/dashboard-charts.js'])
    @endpush
</x-admin-layout>
