<x-admin-layout title="Detail History">
    @push('styles')
    @vite(['resources/css/admin/history.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .detail-page { padding: 0; }
        .detail-header { display: flex; align-items: center; gap: 16px; margin-bottom: 24px; }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: #f3f4f6; border-radius: 8px; color: #374151; text-decoration: none; font-size: 0.875rem; font-weight: 500; transition: background 0.2s; }
        .btn-back:hover { background: #e5e7eb; }
        .detail-title { font-size: 1.25rem; font-weight: 600; color: #111827; margin: 0; }
        
        .detail-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
        @media (max-width: 1024px) { .detail-grid { grid-template-columns: 1fr; } }
        
        .detail-card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; }
        .detail-card-header { padding: 16px 20px; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
        .detail-card-title { font-size: 0.875rem; font-weight: 600; color: #374151; margin: 0; text-transform: uppercase; letter-spacing: 0.5px; }
        .detail-card-body { padding: 20px; }
        
        .info-row { display: flex; padding: 12px 0; border-bottom: 1px solid #f3f4f6; }
        .info-row:last-child { border-bottom: none; }
        .info-label { width: 140px; flex-shrink: 0; font-size: 0.875rem; color: #6b7280; font-weight: 500; }
        .info-value { flex: 1; font-size: 0.875rem; color: #111827; word-break: break-word; }
        
        .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 500; }
        .badge-action { background: #dbeafe; color: #1e40af; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #e0e7ff; color: #3730a3; }
        
        .user-info { display: flex; align-items: center; gap: 12px; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; font-weight: 600; color: #6b7280; font-size: 0.875rem; }
        .user-details { flex: 1; }
        .user-name { font-weight: 600; color: #111827; font-size: 0.875rem; }
        .user-role { font-size: 0.75rem; color: #6b7280; }
        
        .properties-json { background: #f9fafb; border-radius: 8px; padding: 16px; font-family: 'Fira Code', 'Monaco', monospace; font-size: 0.8rem; color: #374151; overflow-x: auto; white-space: pre-wrap; word-break: break-all; max-height: 300px; overflow-y: auto; }
        
        .related-item { display: flex; align-items: flex-start; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f3f4f6; }
        .related-item:last-child { border-bottom: none; }
        .related-icon { width: 32px; height: 32px; border-radius: 8px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #6b7280; flex-shrink: 0; }
        .related-content { flex: 1; min-width: 0; }
        .related-desc { font-size: 0.875rem; color: #111827; margin-bottom: 4px; }
        .related-time { font-size: 0.75rem; color: #9ca3af; }
        
        .subject-card { background: #f9fafb; border-radius: 8px; padding: 16px; }
        .subject-title { font-weight: 600; color: #111827; font-size: 0.875rem; margin-bottom: 8px; }
        .subject-meta { font-size: 0.8rem; color: #6b7280; }
        
        .empty-state { text-align: center; padding: 24px; color: #9ca3af; font-size: 0.875rem; }
    </style>
    @endpush

    <div class="detail-page">
        <!-- Header -->
        <div class="detail-header">
            <a href="{{ route('admin.history') }}" class="btn-back">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                Kembali
            </a>
            <h1 class="detail-title">Detail History #{{ $log->id }}</h1>
        </div>

        <div class="detail-grid">
            <!-- Main Info -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3 class="detail-card-title">Informasi Aktivitas</h3>
                </div>
                <div class="detail-card-body">
                    <div class="info-row">
                        <span class="info-label">Waktu</span>
                        <span class="info-value">{{ $log->created_at->format('d M Y, H:i:s') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Aksi</span>
                        <span class="info-value">
                            @php
                                $actionClass = match($log->action) {
                                    'created' => 'badge-success',
                                    'updated' => 'badge-warning',
                                    'deleted' => 'badge-danger',
                                    default => 'badge-action'
                                };
                            @endphp
                            <span class="badge {{ $actionClass }}">{{ ucfirst($log->action) }}</span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Deskripsi</span>
                        <span class="info-value">{{ $log->description }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tipe Objek</span>
                        <span class="info-value">
                            <span class="badge badge-info">{{ class_basename($log->subject_type ?? '-') }}</span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">ID Objek</span>
                        <span class="info-value">{{ $log->subject_id ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">IP Address</span>
                        <span class="info-value">{{ $log->ip_address ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">User Agent</span>
                        <span class="info-value" style="font-size: 0.75rem;">{{ $log->user_agent ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Side Info -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <!-- Pelaku -->
                <div class="detail-card">
                    <div class="detail-card-header">
                        <h3 class="detail-card-title">Dilakukan Oleh</h3>
                    </div>
                    <div class="detail-card-body">
                        @if($performer)
                            <div class="user-info">
                                <div class="user-avatar">
                                    {{ strtoupper(substr($performer->name ?? 'U', 0, 2)) }}
                                </div>
                                <div class="user-details">
                                    <div class="user-name">{{ $performer->name ?? 'Unknown' }}</div>
                                    <div class="user-role">
                                        @if($log->user_type === 'App\\Models\\Admin')
                                            {{ ucfirst($performer->role ?? 'Admin') }}
                                        @else
                                            Customer
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6;">
                                <div style="font-size: 0.8rem; color: #6b7280;">
                                    <div>📧 {{ $performer->email ?? '-' }}</div>
                                </div>
                            </div>
                        @else
                            <div class="empty-state">User tidak ditemukan</div>
                        @endif
                    </div>
                </div>

                <!-- Objek yang Dipengaruhi -->
                <div class="detail-card">
                    <div class="detail-card-header">
                        <h3 class="detail-card-title">Objek Terkait</h3>
                    </div>
                    <div class="detail-card-body">
                        @if($subject)
                            <div class="subject-card">
                                <div class="subject-title">
                                    @if($log->subject_type === 'App\\Models\\Product')
                                        {{ $subject->name ?? 'Produk #'.$subject->id }}
                                    @elseif($log->subject_type === 'App\\Models\\CustomDesignOrder')
                                        Order #{{ $subject->order_code ?? $subject->id }}
                                    @elseif($log->subject_type === 'App\\Models\\User')
                                        {{ $subject->name ?? 'User #'.$subject->id }}
                                    @else
                                        {{ class_basename($log->subject_type) }} #{{ $subject->id }}
                                    @endif
                                </div>
                                <div class="subject-meta">
                                    @if($log->subject_type === 'App\\Models\\Product')
                                        Kategori: {{ $subject->category->name ?? '-' }}<br>
                                        Status: {{ $subject->status ?? '-' }}
                                    @elseif($log->subject_type === 'App\\Models\\CustomDesignOrder')
                                        Status: {{ $subject->status ?? '-' }}<br>
                                        Total: Rp {{ number_format($subject->total_price ?? 0, 0, ',', '.') }}
                                    @elseif($log->subject_type === 'App\\Models\\User')
                                        Email: {{ $subject->email ?? '-' }}
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="empty-state">
                                @if($log->action === 'deleted')
                                    Objek telah dihapus
                                @else
                                    Objek tidak ditemukan
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Properties / Data Perubahan -->
        @if($log->properties && count($log->properties) > 0)
        <div class="detail-card" style="margin-top: 24px;">
            <div class="detail-card-header">
                <h3 class="detail-card-title">Data Perubahan</h3>
            </div>
            <div class="detail-card-body">
                @if(isset($log->properties['old']) && isset($log->properties['new']))
                    <!-- Show diff -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <h4 style="font-size: 0.8rem; font-weight: 600; color: #dc2626; margin-bottom: 8px;">Data Lama</h4>
                            <pre class="properties-json">{{ json_encode($log->properties['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                        <div>
                            <h4 style="font-size: 0.8rem; font-weight: 600; color: #16a34a; margin-bottom: 8px;">Data Baru</h4>
                            <pre class="properties-json">{{ json_encode($log->properties['new'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                @else
                    <pre class="properties-json">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @endif
            </div>
        </div>
        @endif

        <!-- Related Activity -->
        @if($relatedLogs->count() > 0)
        <div class="detail-card" style="margin-top: 24px;">
            <div class="detail-card-header">
                <h3 class="detail-card-title">Aktivitas Terkait</h3>
            </div>
            <div class="detail-card-body">
                @foreach($relatedLogs as $related)
                    <a href="{{ route('admin.history.detail', $related->id) }}" class="related-item" style="text-decoration: none;">
                        <div class="related-icon">
                            @if($related->action === 'created')
                                <i class="fas fa-plus" style="color: #16a34a;"></i>
                            @elseif($related->action === 'updated')
                                <i class="fas fa-edit" style="color: #d97706;"></i>
                            @elseif($related->action === 'deleted')
                                <i class="fas fa-trash" style="color: #dc2626;"></i>
                            @else
                                <i class="fas fa-clock"></i>
                            @endif
                        </div>
                        <div class="related-content">
                            <div class="related-desc">{{ $related->description }}</div>
                            <div class="related-time">{{ $related->created_at->diffForHumans() }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-admin-layout>
