<x-admin-layout title="Customer Detail - {{ $customer->name }}">
    @push('styles')
        @vite(['resources/css/admin/customer-detail.css'])
    @endpush

    <div class="customer-detail-container">
        <!-- Back Button -->
        <a href="{{ route('admin.management-users') }}" class="back-button">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to User Management
        </a>

        <!-- Customer Card -->
        <div class="customer-card">
            <!-- Header with Avatar -->
            <div class="customer-header">
                <div class="customer-avatar-large">
                    @if($customer->avatar)
                        <img src="{{ asset('storage/' . $customer->avatar) }}" alt="{{ $customer->name }}">
                    @else
                        <div class="customer-avatar-placeholder-large">
                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <h1 class="customer-name">{{ $customer->name }}</h1>
                <p class="customer-email">{{ $customer->email }}</p>
            </div>

            <!-- Body with Details -->
            <div class="customer-body">
                <!-- Contact Information -->
                <h2 class="section-title">Contact Information</h2>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Email Address</div>
                        <div class="detail-value">{{ $customer->email }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Phone Number</div>
                        <div class="detail-value">{{ $customer->phone ?? '-' }}</div>
                    </div>
                </div>

                <!-- Address Information -->
                <h2 class="section-title">Address Information</h2>
                <div class="detail-grid">
                    <div class="detail-item detail-item-full">
                        <div class="detail-label">Full Address</div>
                        <div class="detail-value">{{ $customer->address ?? '-' }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Province</div>
                        <div class="detail-value">{{ $customer->province ?? '-' }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">City</div>
                        <div class="detail-value">{{ $customer->city ?? '-' }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">District</div>
                        <div class="detail-value">{{ $customer->district ?? '-' }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Postal Code</div>
                        <div class="detail-value">{{ $customer->postal_code ?? '-' }}</div>
                    </div>
                </div>

                <!-- Account Information -->
                <h2 class="section-title">Account Information</h2>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Customer ID</div>
                        <div class="detail-value">#{{ str_pad($customer->id, 6, '0', STR_PAD_LEFT) }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span class="status-badge">Active</span>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Joined Date</div>
                        <div class="detail-value">{{ $customer->created_at->format('d F Y, H:i') }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Last Updated</div>
                        <div class="detail-value">{{ $customer->updated_at->format('d F Y, H:i') }}</div>
                    </div>
                </div>

                <!-- Riwayat Pesanan -->
                <h2 class="section-title" id="riwayat-pesanan">Riwayat Pesanan</h2>
                @if($orders->isEmpty())
                    <p style="color: #6B7280; margin-bottom: 24px;">Pelanggan ini belum memiliki pesanan.</p>
                @else
                    <div style="overflow-x: auto; margin-bottom: 24px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                            <thead>
                                <tr style="background: #F3F4F6; text-align: left;">
                                    <th style="padding: 10px;">Tanggal</th>
                                    <th style="padding: 10px;">No. Pesanan</th>
                                    <th style="padding: 10px;">Jenis</th>
                                    <th style="padding: 10px;">Total</th>
                                    <th style="padding: 10px;">Status</th>
                                    <th style="padding: 10px;">Pembayaran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr style="border-bottom: 1px solid #E5E7EB;">
                                    <td style="padding: 10px;">{{ $order->created_at->format('d M Y, H:i') }}</td>
                                    <td style="padding: 10px;">{{ $order->number }}</td>
                                    <td style="padding: 10px;">{{ $order->type }}</td>
                                    <td style="padding: 10px;">Rp {{ number_format($order->total ?? 0, 0, ',', '.') }}</td>
                                    <td style="padding: 10px;">{{ ucfirst(str_replace('_', ' ', $order->status ?? '-')) }}</td>
                                    <td style="padding: 10px;">{{ ucfirst(str_replace('_', ' ', $order->payment_status ?? '-')) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Export Buttons -->
                <h2 class="section-title">Export Customer Data</h2>
                <div class="export-buttons-grid">
                    <a href="{{ route('admin.customer-export-pdf', $customer->id) }}" class="btn-action btn-export btn-export-pdf">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        Export to PDF
                    </a>

                    <a href="{{ route('admin.customer-export-excel', $customer->id) }}" class="btn-action btn-export btn-export-excel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                        Export to Excel
                    </a>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons-detail">

                    @if($currentAdmin->isSuperAdmin())
<button onclick="deleteUser('customer', {{ $customer->id }})" class="btn-action btn-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        Delete Customer
                    </button>
@endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    @vite('resources/js/admin/user-management.js')
    @endpush
</x-admin-layout>
