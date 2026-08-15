<div wire:poll.3s="$refresh">
<div class="chatbot-customer-wrapper">
    <!-- Chat Header -->
    <div class="chatbot-header-bar">
        <div class="chatbot-header-avatar">
            <img src="{{ asset('images/logo.png') }}" alt="LGI Store" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <span class="avatar-fallback" style="display:none;"><i class="fas fa-store"></i></span>
        </div>
        <div class="chatbot-header-info">
            <div class="chatbot-header-name">LGI STORE</div>
            <div class="chatbot-header-status">
                <span class="status-dot {{ $conversation && $conversation->taken_over_by_admin ? 'admin-active' : '' }}"></span>
                @if($conversation && $conversation->taken_over_by_admin)
                    <span class="admin-handling-text">Admin sedang merespons</span>
                @else
                    Online - Balas Cepat
                @endif
            </div>
        </div>
    </div>

    <!-- Chat Messages -->
    <div class="chatpage-messages" id="chatbotMessages" wire:ignore.self>
        @foreach($messages as $message)
            @if($message->sender_type === 'user')
                {{-- User Message --}}
                <div class="message user-message">
                    <div class="message-content">
                        @if($message->metadata && isset($message->metadata['product_context']))
                            @php $product = $message->metadata['product_context']; @endphp
                            <a href="{{ route('product.detail', ['id' => $product['id'] ?? 0]) }}" 
                               target="_blank" 
                               class="product-context-preview-customer">
                                <div class="product-context-header-customer">
                                    <i class="fas fa-box"></i> Produk yang ditanyakan:
                                </div>
                                <div class="product-context-body-customer">
                                    <div class="product-context-name-customer">{{ $product['name'] ?? 'Produk' }}</div>
                                    <div class="product-context-price-customer">Rp {{ number_format($product['price'] ?? 0, 0, ',', '.') }}</div>
                                    @if(isset($product['custom_allowed']) && $product['custom_allowed'])
                                        <span class="product-context-badge-customer">Custom Design</span>
                                    @endif
                                </div>
                                <div class="product-context-link-customer"><i class="fas fa-external-link-alt"></i> Lihat Detail</div>
                            </a>
                        @endif
                        <div class="message-bubble">
                            <div class="message-text">{!! nl2br(e($message->message)) !!}</div>
                        </div>
                        <div class="message-time">{{ $message->created_at->format('H:i') }}</div>
                    </div>
                    <div class="message-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            @elseif($message->sender_type === 'admin')
                {{-- Admin Message - PENTING: harus ditampilkan dengan styling khusus --}}
                <div class="message admin-message">
                    <div class="message-avatar admin-avatar">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="message-content">
                        <div class="message-sender-label">
                            <i class="fas fa-user-shield"></i> Admin
                        </div>
                        <div class="message-bubble admin-bubble">
                            <div class="message-text">{!! nl2br(e($message->message)) !!}</div>
                        </div>
                        <div class="message-time">{{ $message->created_at->format('H:i') }}</div>
                    </div>
                </div>
            @elseif($message->sender_type === 'system')
                {{-- System Message --}}
                <div class="message system-message">
                    <div class="system-bubble">
                        {!! nl2br(e($message->message)) !!}
                    </div>
                </div>
            @else
                {{-- Bot Message --}}
                <div class="message bot-message">
                    <div class="message-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="message-content">
                        <div class="message-bubble">
                            <div class="message-text">{!! nl2br(e($message->message)) !!}</div>
                            
                            @if($message->metadata && isset($message->metadata['products']))
                                <div class="product-recommendations">
                                    @foreach($message->metadata['products'] as $product)
                                        <a href="{{ route('product.detail', ['id' => $product['id']]) }}" 
                                           class="product-card-mini" 
                                           target="_blank">
                                            <div class="product-info">
                                                <span class="product-name">{{ $product['name'] }}</span>
                                                <span class="product-price">Rp {{ number_format($product['price'], 0, ',', '.') }}</span>
                                            </div>
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="message-time">{{ $message->created_at->format('H:i') }}</div>
                    </div>
                </div>
            @endif
        @endforeach

        @if($isTyping)
            <div class="message bot-message typing-message">
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <div class="message-bubble">
                        <div class="typing-indicator">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Quick Replies -->
    @if(count($quickReplies) > 0)
        <div class="quick-replies">
            @foreach($quickReplies as $reply)
                <button class="quick-reply-btn" wire:click="selectQuickReply('{{ $reply }}')">
                    {{ $reply }}
                </button>
            @endforeach
        </div>
    @endif

    <!-- Chat Input -->
    <div class="chatpage-input-wrapper">
        <div class="chatpage-input-container">
            <input
                type="text"
                class="chatpage-input"
                wire:model.live="message"
                wire:keydown.enter="sendMessage"
                placeholder="Ketik pesan Anda..."
                maxlength="500"
                @if($isTyping) disabled @endif
            >
            <button 
                class="chatpage-send" 
                wire:click="sendMessage"
                @if(empty(trim($message)) || $isTyping) disabled @endif
            >
                <i class="fas fa-paper-plane"></i> Kirim
            </button>
        </div>
    </div>

    @script
    <script>
    // Auto scroll to bottom when new message arrives
    $wire.on('messageAdded', () => {
        setTimeout(() => {
            const container = document.getElementById('chatbotMessages');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }, 100);
    });
    
    // Scroll to bottom on initial load and after Livewire updates
    const scrollToBottom = () => {
        const container = document.getElementById('chatbotMessages');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    };
    
    // Initial scroll
    setTimeout(scrollToBottom, 300);
    
    // Watch for DOM changes (new messages)
    const observer = new MutationObserver(scrollToBottom);
    const messagesContainer = document.getElementById('chatbotMessages');
    if (messagesContainer) {
        observer.observe(messagesContainer, { childList: true, subtree: true });
    }
    </script>
    @endscript
</div>
</div>
