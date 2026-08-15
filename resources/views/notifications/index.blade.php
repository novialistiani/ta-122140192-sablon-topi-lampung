<x-customer-layout title="Notifikasi" active="notifications">
    @push('styles')
        <style>
            .notifications-container {
                max-width: 800px;
                margin: 0 auto;
                padding: 24px;
            }
            
            .page-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 24px;
            }
            
            .page-title {
                font-size: 24px;
                font-weight: 600;
                color: #1f2937;
            }
            
            .filter-tabs {
                display: flex;
                gap: 8px;
            }
            
            .filter-tab {
                padding: 8px 16px;
                border-radius: 8px;
                text-decoration: none;
                font-size: 14px;
                transition: all 0.2s;
            }
            
            .filter-tab.active {
                background: #0a1d37;
                color: white;
            }
            
            .filter-tab:not(.active) {
                background: #f3f4f6;
                color: #6b7280;
            }
            
            .filter-tab:hover:not(.active) {
                background: #e5e7eb;
            }
            
            .notifications-card {
                background: white;
                border-radius: 12px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                overflow: hidden;
            }
            
            .card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 16px 20px;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .card-title {
                font-size: 16px;
                font-weight: 600;
                color: #374151;
            }
            
            .mark-all-btn {
                background: none;
                border: none;
                color: #2563eb;
                cursor: pointer;
                font-size: 14px;
            }
            
            .mark-all-btn:hover {
                text-decoration: underline;
            }
            
            .notification-list {
                list-style: none;
                margin: 0;
                padding: 0;
            }
            
            .notification-item {
                display: flex;
                align-items: flex-start;
                padding: 16px 20px;
                border-bottom: 1px solid #f0f0f0;
                transition: background 0.2s;
            }
            
            .notification-item:hover {
                background: #f9fafb;
            }
            
            .notification-item.unread {
                background: #f0f9ff;
            }
            
            .notification-icon {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 16px;
                flex-shrink: 0;
            }
            
            .notification-icon.order {
                background: #dbeafe;
                color: #2563eb;
            }
            
            .notification-icon.payment {
                background: #d1fae5;
                color: #059669;
            }
            
            .notification-icon.default {
                background: #e5e7eb;
                color: #6b7280;
            }
            
            .notification-content {
                flex: 1;
            }
            
            .notification-title {
                font-weight: 500;
                color: #1f2937;
                margin-bottom: 4px;
            }
            
            .notification-item.unread .notification-title {
                font-weight: 600;
            }
            
            .notification-message {
                font-size: 14px;
                color: #6b7280;
                margin-bottom: 8px;
            }
            
            .notification-time {
                font-size: 12px;
                color: #9ca3af;
            }
            
            .notification-actions {
                display: flex;
                gap: 8px;
                margin-left: 16px;
            }
            
            .btn-action {
                padding: 8px 16px;
                border-radius: 6px;
                text-decoration: none;
                font-size: 13px;
                cursor: pointer;
                border: none;
            }
            
            .btn-primary {
                background: #0a1d37;
                color: white;
            }
            
            .btn-secondary {
                background: #f3f4f6;
                color: #374151;
            }
            
            .empty-state {
                text-align: center;
                padding: 48px;
                color: #9ca3af;
            }
            
            .empty-state i {
                font-size: 48px;
                margin-bottom: 16px;
            }
            
            .pagination-wrapper {
                padding: 16px 20px;
                border-top: 1px solid #f0f0f0;
            }
        </style>
    @endpush

    <div class="notifications-container">
        <header class="page-header">
            <h1 class="page-title">Notifikasi</h1>
            <div class="filter-tabs">
                <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
                   class="filter-tab {{ $filter === 'all' ? 'active' : '' }}">
                    Semua
                </a>
                <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                   class="filter-tab {{ $filter === 'unread' ? 'active' : '' }}">
                    Belum Dibaca
                </a>
                <a href="{{ route('notifications.index', ['filter' => 'read']) }}" 
                   class="filter-tab {{ $filter === 'read' ? 'active' : '' }}">
                    Sudah Dibaca
                </a>
            </div>
        </header>
        
        <div class="notifications-card">
            <div class="card-header">
                <h2 class="card-title">
                    @php
                        $unreadCount = $notifications->where('read_at', null)->count();
                    @endphp
                    Notifikasi {{ $unreadCount > 0 ? "($unreadCount belum dibaca)" : '' }}
                </h2>
                @if($unreadCount > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="mark-all-btn">
                        Tandai Semua Dibaca
                    </button>
                </form>
                @endif
            </div>
            
            @if($notifications->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <p>Tidak ada notifikasi</p>
                </div>
            @else
                <ul class="notification-list">
                    @foreach($notifications as $notification)
                    <li class="notification-item {{ $notification->read_at ? '' : 'unread' }}">
                        <div class="notification-icon {{ Str::contains($notification->type, 'order') ? 'order' : (Str::contains($notification->type, 'payment') ? 'payment' : 'default') }}">
                            <i class="fas {{ Str::contains($notification->type, 'order') ? 'fa-shopping-bag' : (Str::contains($notification->type, 'payment') ? 'fa-credit-card' : 'fa-bell') }}"></i>
                        </div>
                        
                        <div class="notification-content">
                            <h3 class="notification-title">{{ $notification->title }}</h3>
                            <p class="notification-message">{{ Str::limit($notification->message, 120) }}</p>
                            <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        
                        <div class="notification-actions">
                            @if($notification->action_url)
                            <a href="{{ $notification->action_url }}" class="btn-action btn-primary">
                                {{ $notification->action_text ?? 'Lihat Detail' }}
                            </a>
                            @endif
                            
                            @if(!$notification->read_at)
                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="btn-action btn-secondary">
                                    Tandai Dibaca
                                </button>
                            </form>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
            @endif
            
            @if($notifications->hasPages())
            <div class="pagination-wrapper">
                {{ $notifications->appends(['filter' => $filter])->links() }}
            </div>
            @endif
        </div>
    </div>
</x-customer-layout>
