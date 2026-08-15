<x-admin-layout title="Chatbot Management">
    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        @vite(['resources/css/admin/chatbot-management.css'])
    @endpush

    <div class="chatbot-container">
        <!-- Sidebar - Conversation List -->
        <div class="conversation-sidebar">
            <div class="sidebar-header">
                <h2>Percakapan</h2>
                <div class="filter-controls">
                    <button class="filter-btn {{ $filter === 'all' ? 'active' : '' }}" onclick="filterConversations('all')">
                        Semua
                    </button>
                    <button class="filter-btn {{ $filter === 'needs_response' ? 'active' : '' }}" onclick="filterConversations('needs_response')">
                        Butuh Respons
                    </button>
                    <button class="filter-btn {{ $filter === 'handled' ? 'active' : '' }}" onclick="filterConversations('handled')">
                        Ditangani
                    </button>
                </div>
            </div>

            <div class="sidebar-search">
                <input type="text" class="search-input" id="searchInput" placeholder="Cari percakapan...">
            </div>

            <div class="conversation-list" id="conversationList">
                @forelse($conversations as $conversation)
                    <div class="conversation-item" onclick="selectConversation({{ $conversation->id }})" style="position: relative;">
                        <div class="conversation-avatar">
                            {{ strtoupper(substr($conversation->user->name, 0, 1)) }}
                        </div>
                        <div class="conversation-info">
                            <div class="conversation-header">
                                <span class="conversation-name">{{ $conversation->user->name }}</span>
                                <span class="conversation-time">{{ $conversation->updated_at->format('H:i') }}</span>
                            </div>
                            <div class="conversation-preview">
                                {{ Str::limit($conversation->latestMessage->message ?? 'Tidak ada pesan', 35) }}
                            </div>
                        </div>
                        @if($conversation->unread_count > 0)
                            <div class="conversation-badges">
                                <span class="unread-badge">{{ $conversation->unread_count > 99 ? '99+' : $conversation->unread_count }}</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div style="padding: 20px; text-align: center; color: #6c757d;">
                        <p>Tidak ada percakapan</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="chat-main" id="chatMain">
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <p>Pilih percakapan untuk memulai</p>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/admin/chatbot-management.js'])
    @endpush
</x-admin-layout>
