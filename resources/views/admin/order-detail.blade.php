<x-admin-layout title="Order Detail">
    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
        @vite(['resources/css/admin/management-order.css'])
        <style>
            .payment-timeline {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin: 0.5rem 0;
                position: relative;
            }
            .timeline-step {
                display: flex;
                flex-direction: column;
                align-items: center;
                flex: 1;
                position: relative;
            }
            .timeline-icon {
                width: 3rem;
                height: 3rem;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                margin-bottom: 0.5rem;
                position: relative;
                z-index: 10;
                transition: none;
                background-color: #e5e7eb;
                color: #6b7280;
                box-shadow: none;
                border: 2px solid #d1d5db;
            }
            .timeline-icon.active {
                background-color: #22c55e !important;
                color: white !important;
                box-shadow: none !important;
                transform: none;
                border-color: #22c55e !important;
            }
            .timeline-icon.pending {
                background-color: #f9fafb !important;
                border: 2px solid #d1d5db;
                color: #9ca3af;
            }
            .timeline-label {
                font-size: 0.875rem;
                font-weight: 600;
                color: #374151;
                margin-bottom: 0.25rem;
                text-align: center;
            }
            .timeline-date {
                font-size: 0.75rem;
                color: #6b7280;
                text-align: center;
            }
            .timeline-line {
                position: absolute;
                top: 1.5rem;
                left: 50%;
                right: 0;
                z-index: -10;
                height: 2px;
                background-color: #e5e7eb;
                border-radius: 0;
            }
            .timeline-line.active {
                background-color: #22c55e !important;
                box-shadow: none;
            }
            @media (max-width: 768px) {
                .payment-timeline {
                    flex-direction: column;
                    gap: 1.5rem;
                }
                .timeline-step {
                    width: 100%;
                }
                .timeline-line {
                    display: none;
                }
                .timeline-icon {
                    width: 3rem;
                    height: 3rem;
                    font-size: 1.25rem;
                }
            }
            
            /* Card Styles */
            .detail-card {
                background: white;
                border-radius: 4px;
                box-shadow: none;
                border: 1px solid #e5e7eb;
                padding: 16px;
                margin-bottom: 12px;
            }
            .detail-section {
                border-bottom: 1px solid #e5e7eb;
                padding-bottom: 1rem;
                margin-bottom: 1rem;
            }
            .detail-section:last-child {
                border-bottom: 0;
                margin-bottom: 0;
                padding-bottom: 0;
            }
            .detail-heading {
                font-size: 1.125rem;
                font-weight: 600;
                color: #1f2937;
                margin-bottom: 1rem;
            }
            .detail-row {
                display: flex;
                flex-wrap: wrap;
                align-items: flex-start;
                justify-content: space-between;
                padding: 0.5rem 0;
            }
            .detail-label {
                font-size: 0.875rem;
                font-weight: 500;
                color: #4b5563;
                width: 100%;
            }
            @media (min-width: 768px) {
                .detail-label {
                    width: 33.333%;
                }
            }
            .detail-value {
                font-size: 0.875rem;
                color: #1f2937;
                width: 100%;
                word-break: break-word;
            }
            @media (min-width: 768px) {
                .detail-value {
                    width: 66.666%;
                }
            }
            .status-badge {
                display: inline-flex;
                align-items: center;
                padding: 0.25rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 500;
            }
            .status-pending {
                background-color: #fef3c7;
                color: #92400e;
            }
            .status-approved {
                background-color: #d1fae5;
                color: #065f46;
            }
            .status-rejected {
                background-color: #fee2e2;
                color: #991b1b;
            }
            .status-completed {
                background-color: #dbeafe;
                color: #1e40af;
            }
            
            /* Product Image Slider */
            .product-preview-slider {
                width: 100%;
                max-width: 300px;
                height: 300px;
                border-radius: 12px;
                overflow: hidden;
                background: #f9fafb;
            }
            .swiper-slide {
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f9fafb;
            }
            .swiper-slide img {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
            }
            .swiper-button-next, .swiper-button-prev {
                color: #fff !important;
                background: rgba(0,0,0,0.5);
                width: 40px !important;
                height: 40px !important;
                border-radius: 50%;
            }
            .swiper-button-next:after, .swiper-button-prev:after {
                font-size: 20px !important;
            }
            .swiper-pagination-bullet-active {
                background: #3b82f6 !important;
            }
            
            /* Order Grid Layouts */
            .order-grid {
                display: grid;
                grid-template-columns: 2fr 1fr;
                gap: 32px;
                align-items: start;
            }
            .order-grid-compact {
                display: grid;
                grid-template-columns: 2fr 0.8fr;
                gap: 24px;
                align-items: start;
            }
            
            /* Action Buttons */
            .btn-action {
                padding: 12px 32px;
                border: none;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                font-size: 14px;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                transition: all 0.2s;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .btn-action:hover {
                box-shadow: 0 4px 6px rgba(0,0,0,0.15);
            }
            .btn-approve {
                background: #22c55e;
                color: white;
            }
            .btn-approve:hover {
                background: #16a34a;
            }
            .btn-reject {
                background: #ef4444;
                color: white;
            }
            .btn-reject:hover {
                background: #dc2626;
            }
            .btn-process {
                background: #3b82f6;
                color: white;
            }
            .btn-process:hover {
                background: #2563eb;
            }
            .btn-process:disabled {
                background: #cbd5e1;
                cursor: not-allowed;
            }
            .btn-complete {
                background: #22c55e;
                color: white;
            }
            .btn-complete:hover {
                background: #16a34a;
            }
            .btn-cancel {
                background: #f59e0b;
                color: white;
            }
            .btn-cancel:hover {
                background: #d97706;
            }
            
            /* Modal Styles */
            .modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
            }
            .modal-content {
                background-color: #fefefe;
                margin: 15% auto;
                padding: 0;
                border: 1px solid #888;
                width: 90%;
                max-width: 500px;
                border-radius: 8px;
            }
            .modal-header {
                padding: 15px 20px;
                background-color: #f8f9fa;
                border-bottom: 1px solid #dee2e6;
                border-radius: 8px 8px 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .modal-header h3 {
                margin: 0;
                color: #333;
            }
            .close {
                color: #aaa;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
            }
            .close:hover {
                color: #000;
            }
            .modal-body {
                padding: 20px;
            }
            .modal-body label {
                display: block;
                margin-bottom: 8px;
                font-weight: bold;
                color: #333;
            }
            .modal-body textarea {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                resize: vertical;
                min-height: 100px;
            }
            
            /* Container Styling */
            .order-detail-container {
                max-width: 900px;
                margin: 0 auto;
                padding: 8px 4px;
                background: transparent;
            }
            .order-detail-content {
                background: transparent;
                min-height: 100vh;
            }
            
            /* 3-Column Layout for Product Detail */
            .product-detail-grid {
                display: grid;
                grid-template-columns: 1fr 1fr 1.5fr;
                gap: 24px;
                margin: 16px 0;
                padding: 20px;
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
            }
            .product-detail-grid h3 {
                font-size: 16px;
                font-weight: 600;
                margin-bottom: 16px;
                color: #111827;
            }
            .product-detail-grid p {
                margin: 8px 0;
                color: #374151;
                font-size: 14px;
            }
            .product-preview-container {
                display: flex;
                justify-content: center;
                align-items: flex-start;
            }
            .product-preview-container img {
                max-width: 100%;
                height: auto;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            
            /* Summary Table */
            .summary-table {
                width: 100%;
                margin: 16px 0;
                border-collapse: collapse;
                background: white;
            }
            .summary-table th {
                background: #f3f4f6;
                padding: 12px 16px;
                text-align: left;
                font-weight: 600;
                color: #111827;
                border: 1px solid #e5e7eb;
            }
            .summary-table td {
                padding: 12px 16px;
                border: 1px solid #e5e7eb;
                color: #374151;
            }
            .summary-table td:first-child {
                font-weight: 500;
            }
        </style>
    @endpush

    <div class="order-detail-container">
        <div class="order-detail-content">
        {{-- Order Status Timeline --}}
        @if(in_array($order->status, ['approved', 'processing', 'completed']))
        <div class="detail-card" style="margin-bottom: 12px;">
            <h2 style="font-size: 16px; font-weight: 600; color: #111827; margin-bottom: 12px;">Status Pesanan</h2>
            <div class="payment-timeline">
                {{-- Disetujui --}}
                <div class="timeline-step">
                    <div class="timeline-icon active">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="timeline-line {{ $order->payment_status === 'paid' ? 'active' : '' }}"></div>
                    <div class="timeline-label">Disetujui</div>
                    <div class="timeline-date">
                        @if(isset($order->approved_at))
                            <div style="text-align: center; white-space: nowrap;">
                                {{ $order->approved_at->format('l, d/m/Y') }}<br>
                                {{ $order->approved_at->format('H:i') }} WIB
                            </div>
                        @endif
                    </div>
                </div>
                
                {{-- Dibayar --}}
                <div class="timeline-step">
                    <div class="timeline-icon {{ $order->payment_status === 'paid' ? 'active' : 'pending' }}" 
                         title="Status pembayaran dari customer">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="timeline-line {{ in_array($order->status, ['processing', 'completed']) ? 'active' : '' }}"></div>
                    <div class="timeline-label">Dibayar</div>
                    <div class="timeline-date">
                        @if($order->payment_status === 'paid' && isset($order->paid_at))
                            <div style="text-align: center; white-space: nowrap;">
                                {{ $order->paid_at->format('l, d/m/Y') }}<br>
                                {{ $order->paid_at->format('H:i') }} WIB
                            </div>
                        @endif
                    </div>
                </div>
                
                {{-- Diproses (Clickable by admin) --}}
                <div class="timeline-step">
                    @if($order->payment_status === 'paid' && !in_array($order->status, ['processing', 'completed']))
                        <button type="button" onclick="updateOrderStatus('processing')" class="timeline-icon pending" style="cursor: pointer; border: 3px solid #28a745; transition: all 0.3s;" title="Klik untuk memproses pesanan">
                            <i class="fas fa-cog"></i>
                        </button>
                    @else
                        <div class="timeline-icon {{ in_array($order->status, ['processing', 'completed']) ? 'active' : 'pending' }}" style="cursor: default;">
                            <i class="fas fa-cog"></i>
                        </div>
                    @endif
                    <div class="timeline-line {{ $order->status === 'completed' ? 'active' : '' }}"></div>
                    <div class="timeline-label">Diproses</div>
                    <div class="timeline-date">
                        @if(in_array($order->status, ['processing', 'completed']) && isset($order->processing_at))
                            <div style="text-align: center; white-space: nowrap;">
                                {{ $order->processing_at->format('l, d/m/Y') }}<br>
                                {{ $order->processing_at->format('H:i') }} WIB
                            </div>
                        @endif
                    </div>
                </div>
                
                {{-- Selesai (Clickable by admin) --}}
                <div class="timeline-step">
                    @if(in_array($order->status, ['processing']) && $order->status !== 'completed')
                        <button type="button" onclick="updateOrderStatus('completed')" class="timeline-icon pending" style="cursor: pointer; border: 3px solid #28a745; transition: all 0.3s;" title="Klik untuk menyelesaikan pesanan">
                            <i class="fas fa-check-circle"></i>
                        </button>
                    @else
                        <div class="timeline-icon {{ $order->status === 'completed' ? 'active' : 'pending' }}" style="cursor: default;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    @endif
                    <div class="timeline-label">Selesai</div>
                    <div class="timeline-date">
                        @if($order->status === 'completed' && isset($order->completed_at))
                            <div style="text-align: center; white-space: nowrap;">
                                {{ $order->completed_at->format('l, d/m/Y') }}<br>
                                {{ $order->completed_at->format('H:i') }} WIB
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Back Button --}}
        <div style="margin-bottom: 12px;">
            <a href="{{ route('admin.order-list', request()->only(['search', 'type', 'status', 'days'])) }}" 
               style="display: inline-flex; align-items: center; padding: 8px 16px; background: #4b5563; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; border: 1px solid #374151;">
                <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Kembali ke Daftar Pesanan
            </a>
        </div>
        @endif

        {{-- Order Detail Card --}}
        <div class="card" style="background: white; border-radius: 4px; border: 1px solid #e5e7eb; overflow: hidden;">
            <div class="card-body" style="padding: 16px;">
                @if($orderType === 'regular')
                    {{-- Regular Order Detail with Grid Layout --}}
                    <div class="order-grid" style="margin-bottom: 16px;">
                        {{-- Left: Product List --}}
                        <div>
                            <h4 style="font-weight: 600; color: #111827; margin-bottom: 16px;">Item Pesanan</h4>
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 2px solid #e5e7eb;">
                                        <th style="padding: 16px 0; text-align: left; font-size: 14px; font-weight: 600; color: #374151;">Produk</th>
                                        <th style="padding: 16px 0; text-align: left; font-size: 14px; font-weight: 600; color: #374151;">Detail</th>
                                        <th style="padding: 16px 0; text-align: right; font-size: 14px; font-weight: 600; color: #374151;">Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Ensure items is an array
                                        $itemsToDisplay = is_array($order->items) ? $order->items : (is_string($order->items) ? json_decode($order->items, true) ?? [] : []);
                                    @endphp
                                    @forelse($itemsToDisplay as $item)
                                    <tr style="border-bottom: 1px solid #f3f4f6;">
                                        <td style="padding: 16px 0;">
                                            <p style="font-weight: 600; color: #111827; margin: 0 0 8px 0;">{{ $item['product_name'] ?? $item['name'] ?? 'N/A' }}</p>
                                            @if(isset($item['image']))
                                                <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['product_name'] ?? 'Product' }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                            @else
                                                <img src="https://via.placeholder.com/60" alt="No Image" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                            @endif
                                        </td>
                                        <td style="padding: 16px 0;">
                                            <div style="color: #6b7280; font-size: 14px; line-height: 1.8;">
                                                @if(isset($item['color']))
                                                <p style="margin: 0;">Warna: <span style="font-weight: 500;">{{ $item['color'] }}</span></p>
                                                @endif
                                                @if(isset($item['size']))
                                                <p style="margin: 0;">Ukuran: <span style="font-weight: 500;">{{ $item['size'] }}</span></p>
                                                @endif
                                                <p style="margin: 0;">Quantity: <span style="font-weight: 500;">{{ $item['quantity'] ?? 1 }}</span></p>
                                            </div>
                                        </td>
                                        <td style="padding: 16px 0; text-align: right; vertical-align: top;">
                                            <p style="font-weight: 600; color: #111827; margin: 0 0 4px 0;">Rp {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 0, ',', '.') }}</p>
                                            <p style="font-size: 13px; color: #6b7280; margin: 0;">Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }} x {{ $item['quantity'] ?? 1 }}</p>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" style="padding: 16px 0; text-align: center; color: #6b7280;">Tidak ada item dalam pesanan</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Right: Product Preview Image Slider --}}
                        <div style="position: sticky; top: 20px;">
                            @php
                                // Collect all product images from order items
                                $orderImages = [];
                                
                                // Ensure items is an array
                                $items = is_array($order->items) ? $order->items : (is_string($order->items) ? json_decode($order->items, true) ?? [] : []);
                                
                                foreach($items as $item) {
                                    // Try variant image first
                                    if(!empty($item['variant_id'])) {
                                        $variant = \App\Models\ProductVariant::find($item['variant_id']);
                                        if($variant && $variant->image) {
                                            $orderImages[] = asset('storage/' . $variant->image);
                                            continue;
                                        }
                                    }
                                    
                                    // Fallback to product image
                                    if(!empty($item['product_id'])) {
                                        $product = \App\Models\Product::find($item['product_id']);
                                        if($product && $product->image) {
                                            $orderImages[] = asset('storage/' . $product->image);
                                            continue;
                                        }
                                    }
                                    
                                    // Last fallback to stored image
                                    if(!empty($item['image'])) {
                                        $orderImages[] = asset('storage/' . $item['image']);
                                    }
                                }
                                
                                // If no images, use placeholder
                                if(empty($orderImages)) {
                                    $orderImages[] = 'https://via.placeholder.com/400x400?text=No+Image';
                                }
                            @endphp
                            
                            <div class="product-preview-slider">
                                <div class="swiper regularOrderSwiper" style="width: 100%; height: 100%;">
                                    <div class="swiper-wrapper">
                                        @foreach($orderImages as $imageUrl)
                                        <div class="swiper-slide">
                                            <img src="{{ $imageUrl }}" alt="Product" onerror="this.onerror=null; this.src='https://via.placeholder.com/400x400?text=No+Image';">
                                        </div>
                                        @endforeach
                                    </div>
                                    @if(count($orderImages) > 1)
                                    <div class="swiper-button-next"></div>
                                    <div class="swiper-button-prev"></div>
                                    <div class="swiper-pagination"></div>
                                    @endif
                                </div>
                            </div>
                            <p style="text-align: center; margin-top: 12px; color: #6b7280; font-size: 13px;">
                                Preview Produk ({{ count($orderImages) }} gambar)
                            </p>
                        </div>
                    </div>

                    {{-- Price Summary --}}
                    <div style="margin-top: 16px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid #e5e7eb;">
                                    <th style="padding: 12px 0; text-align: left; font-weight: 600; color: #374151;">Kategori</th>
                                    <th style="padding: 12px 0; text-align: right; font-weight: 600; color: #374151;">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom: 1px solid #e5e7eb;">
                                    <td style="padding: 12px 0; color: #6b7280;">Subtotal</td>
                                    <td style="padding: 12px 0; text-align: right; color: #6b7280;">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e5e7eb;">
                                    <td style="padding: 12px 0; color: #6b7280;">Diskon</td>
                                    <td style="padding: 12px 0; text-align: right; color: #6b7280;">Rp {{ number_format($order->discount ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px 0; font-weight: 600; color: #111827; font-size: 16px;">Total Pesanan</td>
                                    <td style="padding: 16px 0; text-align: right; font-weight: 600; color: #111827; font-size: 16px;">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding: 12px 0;">
                                        <p style="font-weight: 600; color: #111827; margin-bottom: 8px;">Log Status Pesanan</p>
                                        <p style="color: #6b7280; font-size: 13px; margin: 0;">
                                            {{ $order->created_at->format('M jS, Y H:i') }}: Pesanan dibuat. 
                                            @if($order->status === 'approved')
                                                {{ $order->approved_at ? $order->approved_at->format('M jS, Y H:i') : '' }}: Disetujui.
                                            @elseif($order->status === 'rejected')
                                                {{ $order->rejected_at ? $order->rejected_at->format('M jS, Y H:i') : '' }}: Ditolak.
                                            @endif
                                            @if(in_array($order->status, ['processing', 'completed']))
                                                {{ $order->updated_at->format('M jS, Y H:i') }}: Status diubah ke {{ ucfirst($order->status) }}.
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Rejection Reason (if rejected) --}}
                    @if($order->status === 'rejected' && !empty($order->admin_notes))
                    <div style="margin-top: 16px; padding: 16px; background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 8px;">
                        <div style="display: flex; align-items: start; gap: 12px;">
                            <i class="fas fa-exclamation-circle" style="color: #ef4444; font-size: 20px; margin-top: 2px;"></i>
                            <div style="flex: 1;">
                                <p style="font-weight: 600; color: #991b1b; margin: 0 0 8px 0; font-size: 14px;">Alasan Penolakan</p>
                                <p style="color: #7f1d1d; font-size: 14px; margin: 0; line-height: 1.6;">{{ $order->admin_notes }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Customer Information --}}
                    <div style="margin-top: 20px; padding-top: 16px; border-top: 2px solid #e5e7eb;">
                        <h4 style="font-weight: 600; color: #111827; margin-bottom: 16px;">Informasi Customer</h4>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 12px 0; font-weight: 600; color: #374151; width: 30%;">Nama</td>
                                <td style="padding: 12px 0; color: #6b7280;">{{ $order->user->name }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 12px 0; font-weight: 600; color: #374151;">Email</td>
                                <td style="padding: 12px 0; color: #6b7280;">{{ $order->user->email }}</td>
                            </tr>
                            @if($order->user->phone)
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 12px 0; font-weight: 600; color: #374151;">Telepon</td>
                                <td style="padding: 12px 0; color: #6b7280;">{{ $order->user->phone }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                @else
                    {{-- Custom Design Order Detail --}}
                    <div class="order-grid-compact">
                        {{-- Left: Details Table --}}
                        <div>
                            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                                <thead>
                                    <tr style="background: #f3f4f6;">
                                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #111827; border: 1px solid #e5e7eb;">Kategori</th>
                                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #111827; border: 1px solid #e5e7eb;">Detail</th>
                                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #111827; border: 1px solid #e5e7eb;">Preview & Download</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($uploads as $index => $upload)
                                    <tr>
                                        <td style="padding: 12px 16px; border: 1px solid #e5e7eb; vertical-align: top;">
                                            <p style="margin: 0; font-weight: 500; color: #374151;">Posisi Cetak</p>
                                        </td>
                                        <td style="padding: 12px 16px; border: 1px solid #e5e7eb; vertical-align: top;">
                                            <p style="margin: 0 0 4px 0; color: #111827; font-weight: 500;">{{ $upload->section_name }}</p>
                                        </td>
                                        <td style="padding: 12px 16px; border: 1px solid #e5e7eb; vertical-align: top;">
                                            <p style="margin: 0 0 4px 0; color: #3b82f6; text-decoration: underline; cursor: pointer;" onclick="previewImage{{ $index }}()">
                                                [ <strong>Tampilkan Pratinjau Desain</strong> ]
                                            </p>
                                            <p style="margin: 0; font-size: 13px;">Download: 
                                                <a href="{{ route('custom-design.download', $upload->id) }}" style="color: #3b82f6; text-decoration: underline;">
                                                    [ <strong>Unduh File Asli (.png)</strong> ]
                                                </a>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 16px; border: 1px solid #e5e7eb; vertical-align: top;">
                                            <p style="margin: 0; font-weight: 500; color: #374151;">Jenis Cetak</p>
                                        </td>
                                        <td style="padding: 12px 16px; border: 1px solid #e5e7eb; vertical-align: top;">
                                            <p style="margin: 0; color: #6b7280;">{{ $order->cutting_type ?? 'Sablon Digital (Printing)' }}</p>
                                        </td>
                                        <td style="padding: 12px 16px; border: 1px solid #e5e7eb; vertical-align: top;">
                                            <p style="margin: 0; color: #6b7280;">Ukuran Cetak: {{ $upload->print_size ?? 'A4 (21 x 29.7 cm)' }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 16px; border: 1px solid #e5e7eb; vertical-align: top;">
                                            <p style="margin: 0; font-weight: 500; color: #374151;">File Desain Anda:</p>
                                        </td>
                                        <td colspan="2" style="padding: 12px 16px; border: 1px solid #e5e7eb; vertical-align: top;">
                                            <p style="margin: 0 0 4px 0; color: #111827;">{{ $upload->file_name }}</p>
                                            <p style="margin: 0; font-size: 13px; color: #6b7280;">{{ number_format($upload->file_size / 1024, 1) }} KB</p>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Right: Product Image Preview --}}
                        <div style="position: sticky; top: 20px;">
                            @php
                                // Collect all product images
                                $productImages = [];
                                
                                // Get variant image if available
                                if($order->variant && $order->variant->image) {
                                    $productImages[] = asset('storage/' . $order->variant->image);
                                }
                                
                                // Get product image if available  
                                if($order->product && $order->product->image) {
                                    // Only add if it's a local storage image (not external URL)
                                    if(!str_starts_with($order->product->image, 'http')) {
                                        $productImages[] = asset('storage/' . $order->product->image);
                                    }
                                }
                                
                                // Get uploaded design images
                                if($order->uploads && (is_array($order->uploads) || method_exists($order->uploads, 'count'))) {
                                    $uploadsToProcess = is_array($order->uploads) ? $order->uploads : ($order->uploads->count() > 0 ? $order->uploads : []);
                                    foreach($uploadsToProcess as $upload) {
                                        // Check if file is an image by extension
                                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
                                        $filePath = is_array($upload) ? ($upload['file_path'] ?? '') : $upload->file_path;
                                        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                        
                                        if(in_array($fileExtension, $imageExtensions)) {
                                            $productImages[] = asset('storage/' . $filePath);
                                        }
                                    }
                                }
                                
                                // Remove duplicates
                                $productImages = array_unique($productImages);
                                
                                // If no images, use placeholder
                                if(empty($productImages)) {
                                    $productImages[] = 'https://via.placeholder.com/300x300?text=No+Image';
                                }
                            @endphp
                            
                            @if(count($productImages) > 1)
                                {{-- Show Slider for Multiple Images --}}
                                <div style="width: 100%; max-width: 300px; height: 300px; border-radius: 12px; overflow: hidden; background: #f9fafb; margin: 0 auto;">
                                    <div class="swiper productSwiper" style="width: 100%; height: 100%;">
                                        <div class="swiper-wrapper">
                                            @foreach($productImages as $imageUrl)
                                            <div class="swiper-slide">
                                                <img src="{{ $imageUrl }}" alt="{{ $order->product_name }}" onerror="this.onerror=null; this.src='https://via.placeholder.com/300x300?text=No+Image';">
                                            </div>
                                            @endforeach
                                        </div>
                                        <div class="swiper-button-next"></div>
                                        <div class="swiper-button-prev"></div>
                                        <div class="swiper-pagination"></div>
                                    </div>
                                </div>
                                <p style="text-align: center; margin-top: 12px; color: #6b7280; font-size: 13px;">
                                    Preview Produk & Desain ({{ count($productImages) }} gambar)
                                </p>
                            @else
                                {{-- Show Single Image --}}
                                <div style="background: #f9fafb; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                    <p style="font-size: 12px; color: #6b7280; margin-bottom: 12px; font-weight: 500;">Preview Produk & Desain</p>
                                    <img src="{{ $productImages[0] }}" alt="{{ $order->product_name }}" style="max-width: 250px; max-height: 250px; object-fit: contain; border-radius: 8px;" onerror="this.onerror=null; this.src='https://via.placeholder.com/250x250?text=No+Image';">
                                </div>
                            @endif
                        </div>
                    </div>

                        {{-- Price Summary --}}
                        <div style="margin-top: 16px;">
                            <h4 style="font-weight: 600; color: #111827; margin-bottom: 16px;">Ringkasan Biaya</h4>
                            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                                <tbody>
                                    <tr style="border-bottom: 1px solid #e5e7eb;">
                                        <td style="padding: 12px 0; color: #6b7280;">Biaya Produk</td>
                                        <td style="padding: 12px 0; text-align: right; color: #6b7280;">Rp {{ number_format($order->product_price, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #e5e7eb;">
                                        <td style="padding: 12px 0; color: #6b7280;">Biaya Custom</td>
                                        <td style="padding: 12px 0; text-align: right; color: #6b7280;">Rp {{ number_format($order->total_price - $order->product_price, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #e5e7eb;">
                                        <td style="padding: 12px 0; color: #6b7280;">Subtotal</td>
                                        <td style="padding: 12px 0; text-align: right; color: #6b7280;">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #e5e7eb;">
                                        <td style="padding: 12px 0; color: #6b7280;">Diskon</td>
                                        <td style="padding: 12px 0; text-align: right; color: #6b7280;">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 16px 0; font-weight: 600; color: #111827;">Log Status Pesanan</td>
                                        <td style="padding: 16px 0; text-align: right; color: #6b7280; font-size: 13px;">
                                            {{ $order->created_at->format('M jS, Y H:i') }}: Pesanan dibuat. 
                                            @if($order->status === 'approved')
                                                {{ $order->updated_at->format('M jS, Y H:i') }}: diterima.
                                            @elseif($order->status === 'rejected')
                                                {{ $order->updated_at->format('M jS, Y H:i') }}: ditolak.
                                            @endif
                                            @if($order->status === 'processing' || $order->status === 'completed')
                                                {{ $order->updated_at->format('M jS, Y H:i') }}: Status diubah ke Produksi.
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Rejection Reason (if rejected) --}}
                        @if($order->status === 'rejected' && !empty($order->admin_notes))
                        <div style="margin-top: 16px; padding: 16px; background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 8px;">
                            <div style="display: flex; align-items: start; gap: 12px;">
                                <i class="fas fa-exclamation-circle" style="color: #ef4444; font-size: 20px; margin-top: 2px;"></i>
                                <div style="flex: 1;">
                                    <p style="font-weight: 600; color: #991b1b; margin: 0 0 8px 0; font-size: 14px;">Alasan Penolakan</p>
                                    <p style="color: #7f1d1d; font-size: 14px; margin: 0; line-height: 1.6;">{{ $order->admin_notes }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                @endif
            </div>
        </div>

        {{-- Action Buttons (match screenshot style) --}}
        @if($orderType === 'custom' && $order->status === 'pending')
        <div style="margin-top: 16px; display: flex; justify-content: flex-end; gap: 12px;">
            <button type="button" onclick="showRejectModal()" style="padding: 12px 36px; background: #f59e0b; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.2s;" onmouseover="this.style.background='#d97706'" onmouseout="this.style.background='#f59e0b'">
                Ditolak
            </button>
            <form method="POST" action="{{ route('admin.order.approve', ['id' => $order->id, 'type' => $orderType]) }}" style="display: inline; margin: 0;">
                @csrf
                <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menyetujui pesanan ini?')" style="padding: 12px 36px; background: #1e293b; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.2s;" onmouseover="this.style.background='#0f172a'" onmouseout="this.style.background='#1e293b'">
                    Disetujui
                </button>
            </form>
        </div>
        @elseif(isset($order->payment_status) && $order->payment_status === 'paid')
        {{-- Manual Process/Complete Buttons for Paid Orders --}}
        <div style="margin-top: 16px; display: flex; justify-content: flex-end; gap: 12px;">
            @if($order->status === 'approved')
            <form method="POST" action="{{ route('admin.order.update-status', ['id' => $order->id]) }}" style="display: inline; margin: 0;">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="processing">
                <input type="hidden" name="type" value="{{ $orderType }}">
                <button type="submit" onclick="return confirm('Ubah status ke Diproses?')" style="padding: 12px 36px; background: #3b82f6; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px;">
                    <i class="fas fa-cog"></i> Proses
                </button>
            </form>
            @endif
            
            @if(in_array($order->status, ['approved', 'processing']))
            <form method="POST" action="{{ route('admin.order.update-status', ['id' => $order->id]) }}" style="display: inline; margin: 0;">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="completed">
                <input type="hidden" name="type" value="{{ $orderType }}">
                <button type="submit" onclick="return confirm('Tandai sebagai Selesai?')" style="padding: 12px 36px; background: #28a745; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px;">
                    <i class="fas fa-check-circle"></i> Selesai
                </button>
            </form>
            @endif
        </div>
        @elseif($order->status === 'approved' && (!isset($order->payment_status) || in_array($order->payment_status, ['unpaid', 'va_active'])))
        {{-- Option for admin to mark payment as received (for WA payment) --}}
        <div style="margin-top: 16px; display: flex; justify-content: flex-end; gap: 12px;">
            <div style="background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 12px 16px; font-size: 14px; color: #92400e; margin-right: auto;">
                <i class="fas fa-info-circle"></i> <strong>Pesanan sudah disetujui, menunggu pembayaran.</strong><br>
                Customer dapat melakukan pembayaran melalui WhatsApp. Klik tombol di bawah untuk mengkonfirmasi pembayaran telah diterima.
            </div>
            <form method="POST" action="{{ route('admin.order.mark-payment-received', ['id' => $order->id, 'type' => $orderType]) }}" style="display: inline; margin: 0;">
                @csrf
                <button type="submit" onclick="return confirm('Konfirmasi pembayaran sudah diterima? Pesanan akan siap diproses.')" style="padding: 12px 36px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.2s;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                    <i class="fas fa-money-bill-wave"></i> Konfirmasi Pembayaran
                </button>
            </form>
        </div>
        @elseif($order->status === 'pending')
        <div style="margin-top: 16px; display: flex; gap: 12px;">
            <form method="POST" action="{{ route('admin.order.approve', ['id' => $order->id, 'type' => $orderType]) }}" style="display: inline; margin: 0;">
                @csrf
                <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menyetujui pesanan ini?')" class="btn-action btn-approve">
                    <i class="fas fa-check"></i> Setujui Pesanan
                </button>
            </form>
            <button type="button" onclick="showRejectModal()" class="btn-action btn-reject">
                <i class="fas fa-times"></i> Tolak Pesanan
            </button>
        </div>
        @endif
    </div>

    {{-- Modal untuk reject reason --}}
    <div id="rejectModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Tolak Pesanan</h3>
                <span class="close" onclick="closeRejectModal()">&times;</span>
            </div>
            <form id="rejectForm" method="POST" action="{{ route('admin.order.reject', ['id' => $order->id, 'type' => $orderType]) }}">
                @csrf
                <div class="modal-body">
                    <label for="rejectReason">Alasan Penolakan:</label>
                    <textarea id="rejectReason" name="reason" required maxlength="500" placeholder="Masukkan alasan penolakan pesanan..."></textarea>
                </div>
                <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 12px; padding: 16px 20px; border-top: 1px solid #e5e7eb;">
                    <button type="button" onclick="closeRejectModal()" style="padding: 10px 24px; background: #6b7280; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.2s;" onmouseover="this.style.background='#4b5563'" onmouseout="this.style.background='#6b7280'">Batal</button>
                    <button type="submit" style="padding: 10px 24px; background: #ef4444; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.2s;" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">Tolak Pesanan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function showTab(tab) {
            // Hide all tab contents
            document.getElementById('detail-tab').style.display = 'none';
            document.getElementById('catatan-tab').style.display = 'none';
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.style.borderBottomColor = 'transparent';
                button.style.color = '#6b7280';
            });
            
            // Show selected tab and mark button as active
            if (tab === 'detail') {
                document.getElementById('detail-tab').style.display = 'block';
                document.getElementById('tab-detail').style.borderBottomColor = '#3b82f6';
                document.getElementById('tab-detail').style.color = '#3b82f6';
            } else {
                document.getElementById('catatan-tab').style.display = 'block';
                document.getElementById('tab-catatan').style.borderBottomColor = '#3b82f6';
                document.getElementById('tab-catatan').style.color = '#3b82f6';
            }
        }
        
        // Update order status via timeline click
        function updateOrderStatus(status) {
            if (!confirm(`Ubah status pesanan menjadi ${status === 'processing' ? 'Diproses' : 'Selesai'}?`)) {
                return;
            }
            
            const orderId = '{{ $order->id }}';
            const orderType = '{{ $orderType }}';
            
            // Show loading
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;
            
            fetch(`/admin/order-list/${orderId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    status: status,
                    type: orderType
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alert(data.message || 'Status berhasil diperbarui');
                    // Reload page to show updated timeline
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal memperbarui status');
                    btn.innerHTML = originalHTML;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            });
        }

        function showRejectModal() {
            document.getElementById('rejectModal').style.display = 'block';
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
            document.getElementById('rejectReason').value = '';
        }

        function changeStatus(status) {
            if (confirm('Apakah Anda yakin ingin mengubah status pesanan ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/order-list/{{ $order->id }}/status?type={{ $orderType }}`;

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'PATCH';
                form.appendChild(methodField);

                const statusField = document.createElement('input');
                statusField.type = 'hidden';
                statusField.name = 'status';
                statusField.value = status;
                form.appendChild(statusField);

                const csrfField = document.createElement('input');
                csrfField.type = 'hidden';
                csrfField.name = '_token';
                csrfField.value = '{{ csrf_token() }}';
                form.appendChild(csrfField);

                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('rejectModal');
            if (event.target == modal) {
                closeRejectModal();
            }
        }
    </script>
    
    {{-- Swiper JS --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        // Initialize product image sliders
        document.addEventListener('DOMContentLoaded', function() {
            // Reusable Swiper initialization function
            function initSwiper(selector) {
                const element = document.querySelector(selector);
                if (element) {
                    const slidesCount = element.querySelectorAll('.swiper-slide').length;
                    
                    new Swiper(selector, {
                        loop: slidesCount > 1, // Only enable loop if more than 1 slide
                        autoplay: slidesCount > 1 ? {
                            delay: 3000,
                            disableOnInteraction: false,
                        } : false,
                        pagination: {
                            el: `${selector} .swiper-pagination`,
                            clickable: true,
                        },
                        navigation: {
                            nextEl: `${selector} .swiper-button-next`,
                            prevEl: `${selector} .swiper-button-prev`,
                        },
                        speed: 800,
                        effect: 'slide',
                    });
                }
            }
            
            // Initialize both sliders
            initSwiper('.regularOrderSwiper');
            initSwiper('.productSwiper');
        });
    </script>
    @endpush
</x-admin-layout>
