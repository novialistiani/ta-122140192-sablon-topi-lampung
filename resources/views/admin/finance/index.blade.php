<x-admin-layout title="Finance & Wallet">
    @push('styles')
    @vite(['resources/css/admin/dashboard.css'])
    <style>
        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .filter-form {
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
        }
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .form-input {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
        }
        .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        .btn-primary:hover {
            background: #2563eb;
        }
        .btn-success {
            background: #10b981;
            color: white;
        }
        .btn-success:hover {
            background: #059669;
        }
    </style>
    @endpush

    <div class="dashboard-content">
        {{-- Date Filter & Export --}}
        <div class="filter-card">
            <form action="{{ route('admin.finance.index') }}" method="GET" class="filter-form">
                <div class="form-group">
                    <label class="form-label">From Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">To Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-input">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('admin.finance.export') }}?start_date={{ $startDate }}&end_date={{ $endDate }}" 
                   class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </form>
        </div>

        {{-- Stats Cards --}}
        <div class="stats-grid">
            {{-- Total Pemasukan --}}
            <div class="stats-card">
                <div class="flex items-start">
                    <div class="stat-icon-wrapper stat-icon-blue">
                        <i class="fas fa-wallet stat-icon"></i>
                    </div>
                    <div>
                        <p class="stat-content">Total Pemasukan</p>
                        <h3 class="stat-value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
                        <div class="stat-trend">
                            <span class="stat-badge-{{ $revenueChange >= 0 ? 'up' : 'down' }}">
                                <i class="fas fa-arrow-{{ $revenueChange >= 0 ? 'up' : 'down' }}"></i> {{ number_format(abs($revenueChange), 1) }}%
                            </span>
                            <span class="stat-label-small">Dibandingkan dengan Oktober 2025</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Transaksi VA --}}
            <div class="stats-card">
                <div class="flex items-start">
                    <div class="stat-icon-wrapper stat-icon-purple">
                        <i class="fas fa-credit-card stat-icon"></i>
                    </div>
                    <div>
                        <p class="stat-content">Transaksi VA</p>
                        <h3 class="stat-value">{{ $vaTransactions }}</h3>
                        <div class="stat-trend">
                            <span class="stat-badge-{{ $vaChange >= 0 ? 'up' : 'down' }}">
                                <i class="fas fa-arrow-{{ $vaChange >= 0 ? 'up' : 'down' }}"></i> {{ number_format(abs($vaChange), 1) }}%
                            </span>
                            <span class="stat-label-small">Dibandingkan dengan Oktober 2025</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Transaksi E-Wallet --}}
            <div class="stats-card">
                <div class="flex items-start">
                    <div class="stat-icon-wrapper stat-icon-green">
                        <i class="fas fa-mobile-alt stat-icon"></i>
                    </div>
                    <div>
                        <p class="stat-content">Transaksi E-Wallet</p>
                        <h3 class="stat-value">Rp {{ number_format($ewalletTransactions * 99000, 0, ',', '.') }}</h3>
                        <div class="stat-trend">
                            <span class="stat-badge-{{ $ewalletChange >= 0 ? 'up' : 'down' }}">
                                <i class="fas fa-arrow-{{ $ewalletChange >= 0 ? 'up' : 'down' }}"></i> {{ number_format(abs($ewalletChange), 1) }}%
                            </span>
                            <span class="stat-label-small">Dibandingkan dengan Oktober 2025</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart --}}
        <div class="chart-card" style="margin-bottom: 2rem;">
            <div class="chart-header">
                <h2 class="chart-title">Grafik Keuangan</h2>
            </div>
            <div class="chart-wrapper">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        {{-- Transactions Table --}}
        <div class="chart-card">
            <div class="chart-header">
                <h2 class="chart-title">Transaksi</h2>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Tanggal</th>
                            <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">ID Transaksi</th>
                            <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Customer</th>
                            <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Payment Method</th>
                            <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Total</th>
                            <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr style="border-bottom: 1px solid #e5e7eb; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='white'">
                            <td style="padding: 16px; font-size: 14px; color: #374151; white-space: nowrap;">
                                {{ $transaction->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td style="padding: 16px; font-size: 14px; font-weight: 500; color: #1f2937; white-space: nowrap;">
                                {{ $transaction->transaction_id }}
                            </td>
                            <td style="padding: 16px; font-size: 14px; color: #374151;">
                                {{ $transaction->user->name ?? 'N/A' }}
                            </td>
                            <td style="padding: 16px; font-size: 14px; color: #374151;">
                                <span style="display: inline-flex; align-items: center; padding: 4px 12px; background: #eff6ff; color: #1e40af; border-radius: 9999px; font-size: 12px; font-weight: 500;">
                                    <i class="fas fa-credit-card" style="margin-right: 6px;"></i>
                                    {{ strtoupper($transaction->payment_channel ?? 'N/A') }}
                                </span>
                            </td>
                            <td style="padding: 16px; font-size: 14px; font-weight: 600; color: #059669; white-space: nowrap;">
                                Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                            </td>
                            <td style="padding: 16px;">
                                @if($transaction->status === 'paid')
                                    <span style="display: inline-flex; align-items: center; padding: 6px 12px; background: #d1fae5; color: #065f46; border-radius: 9999px; font-size: 12px; font-weight: 600;">
                                        <i class="fas fa-check-circle" style="margin-right: 6px;"></i> Sudah Dibayar
                                    </span>
                                @elseif($transaction->virtualAccount && !$transaction->virtualAccount->isExpired())
                                    <span style="display: inline-flex; align-items: center; padding: 6px 12px; background: #dbeafe; color: #1e40af; border-radius: 9999px; font-size: 12px; font-weight: 600;">
                                        <i class="fas fa-clock" style="margin-right: 6px;"></i> VA Aktif
                                    </span>
                                @elseif($transaction->status === 'expired')
                                    <span style="display: inline-flex; align-items: center; padding: 6px 12px; background: #fee2e2; color: #991b1b; border-radius: 9999px; font-size: 12px; font-weight: 600;">
                                        <i class="fas fa-times-circle" style="margin-right: 6px;"></i> Expired
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; padding: 6px 12px; background: #fef3c7; color: #92400e; border-radius: 9999px; font-size: 12px; font-weight: 600;">
                                        <i class="fas fa-hourglass-half" style="margin-right: 6px;"></i> Pending
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="padding: 48px 16px; text-align: center; color: #9ca3af;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 12px; display: block; opacity: 0.5;"></i>
                                <p style="font-size: 14px; margin: 0;">Tidak ada transaksi dalam periode ini</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            <div style="padding: 1rem; border-top: 1px solid #e5e7eb;">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
    {{-- End Dashboard Content --}}

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const chartData = @json($chartData);
        
        const labels = chartData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('id-ID', { month: 'short', day: 'numeric' });
        });
        
        const data = chartData.map(item => item.total);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (Rp)',
                    data: data,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    </script>
    @endpush
</x-admin-layout>
