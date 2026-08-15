<x-customer-layout title="Riwayat Chat" active="chat">
    @push('styles')
    <style>
        .chat-history-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .chat-history-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .chat-history-header h1 {
            font-size: 24px;
            font-weight: 600;
            color: #0f1e3d;
            margin-bottom: 8px;
        }
        
        .chat-history-header p {
            color: #6c757d;
            font-size: 14px;
        }
        
        .chat-history-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .chat-history-item {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
        }
        
        .chat-history-item:hover {
            border-color: #0f1e3d;
            box-shadow: 0 4px 12px rgba(15, 30, 61, 0.1);
            transform: translateY(-2px);
        }
        
        .chat-item-info {
            flex: 1;
        }
        
        .chat-item-product {
            font-weight: 600;
            font-size: 16px;
            color: #212529;
            margin-bottom: 4px;
        }
        
        .chat-item-message {
            font-size: 14px;
            color: #6c757d;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .chat-item-meta {
            text-align: right;
        }
        
        .chat-item-time {
            font-size: 12px;
            color: #999;
            margin-bottom: 4px;
        }
        
        .chat-item-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .chat-item-status.open {
            background: #d4edda;
            color: #155724;
        }
        
        .chat-item-status.closed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .empty-chat-history {
            text-align: center;
            padding: 60px 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        
        .empty-chat-history i {
            font-size: 48px;
            color: #dee2e6;
            margin-bottom: 16px;
        }
        
        .empty-chat-history h3 {
            font-size: 18px;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .empty-chat-history p {
            color: #6c757d;
            font-size: 14px;
        }
        
        .start-chat-btn {
            display: inline-block;
            margin-top: 16px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #0f1e3d 0%, #1a2d5a 100%);
            color: white;
            border-radius: 24px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .start-chat-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 30, 61, 0.3);
            color: white;
        }
    </style>
    @endpush

    <div class="chat-history-container">
        <div class="chat-history-header">
            <h1><i class="fas fa-comments"></i> Riwayat Chat</h1>
            <p>Daftar percakapan Anda dengan customer service</p>
        </div>

        @if($conversations->isEmpty())
            <div class="empty-chat-history">
                <i class="fas fa-comments"></i>
                <h3>Belum ada riwayat chat</h3>
                <p>Mulai percakapan dengan customer service kami</p>
                <a href="{{ route('chatpage') }}" class="start-chat-btn">
                    <i class="fas fa-comment-dots"></i> Mulai Chat
                </a>
            </div>
        @else
            <div class="chat-history-list">
                @foreach($conversations as $conversation)
                    <a href="{{ route('chatpage', ['conversation_id' => $conversation->id]) }}" class="chat-history-item">
                        <div class="chat-item-info">
                            <div class="chat-item-product">
                                @if($conversation->product)
                                    {{ $conversation->product->name }}
                                @else
                                    Percakapan Umum
                                @endif
                            </div>
                            <div class="chat-item-message">
                                @if($conversation->latestMessage)
                                    {{ $conversation->latestMessage->message }}
                                @else
                                    Tidak ada pesan
                                @endif
                            </div>
                        </div>
                        <div class="chat-item-meta">
                            <div class="chat-item-time">
                                {{ $conversation->updated_at->diffForHumans() }}
                            </div>
                            <span class="chat-item-status {{ $conversation->status }}">
                                {{ $conversation->status === 'open' ? 'Aktif' : 'Selesai' }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-customer-layout>
