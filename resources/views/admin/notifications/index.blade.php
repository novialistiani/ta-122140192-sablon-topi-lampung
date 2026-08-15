<x-admin-layout title="Notifikasi Admin">
    @push('styles')
        @vite(['resources/css/admin/admin-notifications.css'])
    @endpush

<div class="notification-container">
    {{-- Header Halaman --}}
    <header class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 class="page-title" style="margin: 0;">Pemberitahuan</h1>
        <div class="filter-section" style="display: flex; gap: 10px;">
            <a href="{{ route('admin.notifications.index', ['filter' => 'all']) }}" 
               class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-secondary' }}">
                Semua
            </a>
            <a href="{{ route('admin.notifications.index', ['filter' => 'unread']) }}" 
               class="btn btn-sm {{ $filter === 'unread' ? 'btn-primary' : 'btn-secondary' }}">
                Belum Dibaca
            </a>
            <a href="{{ route('admin.notifications.index', ['filter' => 'read']) }}" 
               class="btn btn-sm {{ $filter === 'read' ? 'btn-primary' : 'btn-secondary' }}">
                Sudah Dibaca
            </a>
        </div>
    </header>

    {{-- Kartu Daftar Notifikasi --}}
    <div class="card notification-card">
        
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; border-bottom: 1px solid #eee;">
            <h2 class="card-title" style="margin: 0; font-size: 16px;">
                Notifikasi 
                @php
                    $unreadCount = $notifications->where('read_at', null)->count();
                @endphp
                @if($unreadCount > 0)
                    ({{ $unreadCount }} Belum Dibaca)
                @endif
            </h2>
            @if($unreadCount > 0)
            <form action="{{ route('admin.notifications.read-all') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn btn-subtle mark-all-read" style="background: none; border: none; color: #2563eb; cursor: pointer;">
                    Tandai Semua Sudah Dibaca
                </button>
            </form>
            @endif
        </div>

        <div class="card-body" style="padding: 0;">
            @if($notifications->isEmpty())
                <div style="text-align: center; padding: 50px; color: #999;">
                    <i class="fas fa-bell-slash" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <p>Tidak ada notifikasi</p>
                </div>
            @else
            <ul class="notification-list" style="list-style: none; margin: 0; padding: 0;">
                @foreach($notifications as $notification)
                <li class="notification-item {{ $notification->read_at ? '' : 'unread' }}" 
                    style="display: flex; align-items: center; padding: 15px 20px; border-bottom: 1px solid #f0f0f0; {{ $notification->read_at ? '' : 'background-color: #f8fafc;' }}">
                    
                    <div class="notification-icon {{ $notification->type }}" 
                         style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px;
                         @if(Str::contains($notification->type, 'order'))
                             background-color: #dbeafe; color: #2563eb;
                         @elseif(Str::contains($notification->type, 'payment'))
                             background-color: #d1fae5; color: #059669;
                         @elseif(Str::contains($notification->type, 'custom'))
                             background-color: #fef3c7; color: #d97706;
                         @else
                             background-color: #e5e7eb; color: #6b7280;
                         @endif
                         ">
                        <i class="fas 
                            @if(Str::contains($notification->type, 'order'))
                                fa-shopping-bag
                            @elseif(Str::contains($notification->type, 'payment'))
                                fa-credit-card
                            @elseif(Str::contains($notification->type, 'custom'))
                                fa-palette
                            @else
                                fa-bell
                            @endif
                        "></i>
                    </div>
                    
                    <div class="notification-content" style="flex: 1;">
                        <span class="notification-title" style="font-weight: {{ $notification->read_at ? 'normal' : '600' }}; display: block; margin-bottom: 5px;">
                            {{ $notification->title }}
                        </span>
                        <span class="notification-message" style="color: #666; font-size: 14px; display: block; margin-bottom: 5px;">
                            {{ Str::limit($notification->message, 100) }}
                        </span>
                        <span class="notification-time" style="color: #999; font-size: 12px;">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>
                    
                    <div class="notification-actions" style="display: flex; gap: 10px;">
                        @if($notification->action_url)
                        <a href="{{ $notification->action_url }}" 
                           class="btn btn-primary" 
                           style="background-color: #2563eb; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px;">
                            {{ $notification->action_text ?? 'Lihat Detail' }}
                        </a>
                        @endif
                        
                        @if(!$notification->read_at)
                        <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" 
                                    class="btn btn-secondary" 
                                    style="background-color: #f3f4f6; color: #374151; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px;">
                                Tandai Dibaca
                            </button>
                        </form>
                        @endif
                    </div>
                </li>
                @endforeach
            </ul>
            @endif
        </div>
        
        @if($notifications->hasPages())
        <div class="card-footer" style="padding: 15px 20px; border-top: 1px solid #eee;">
            {{ $notifications->appends(['filter' => $filter])->links() }}
        </div>
        @endif
    </div>
</div>

</x-admin-layout>
