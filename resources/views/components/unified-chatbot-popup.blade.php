{{-- Unified Chatbot Popup Component --}}
{{-- Gunakan di semua halaman customer: home, catalog, all-products --}}

@php
    $userAvatar = null;
    if (auth('web')->check() && auth('web')->user()->avatar) {
        $avatar = auth('web')->user()->avatar;
        $userAvatar = str_starts_with($avatar, 'http') ? $avatar : asset('storage/' . $avatar);
    }
@endphp

<!-- Chatbot Trigger Button -->
<button class="unified-chatbot-trigger" id="unifiedChatbotTrigger" aria-label="Buka chat">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
    </svg>
    <!-- Chat Unread Badge -->
    <span class="chat-unread-badge" id="chatUnreadBadge" style="display: none;">0</span>
</button>

<!-- Chatbot Popup -->
<div class="unified-chatbot-popup" id="unifiedChatbotPopup">
    <!-- Chatbot Header -->
    <div class="unified-chatbot-header">
        <div class="unified-chatbot-avatar bot-avatar">
            <img src="{{ asset('images/logo.png') }}" alt="LGI Store" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <span class="avatar-fallback" style="display:none;">🏪</span>
        </div>
        <div class="unified-chatbot-info">
            <div class="unified-chatbot-name">LGI STORE</div>
            <div class="unified-chatbot-status">
                <span class="status-dot"></span>
                Online - Balas Cepat
            </div>
        </div>
        <button class="unified-chatbot-close" id="unifiedChatbotClose" aria-label="Tutup chat">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <!-- Product Context Badge (hidden by default) -->
    <div class="unified-product-context" id="unifiedProductContext" style="display: none;">
        <span class="product-context-label">Produk:</span>
        <span class="product-context-name" id="unifiedProductName"></span>
        <button class="product-context-clear" id="unifiedClearProduct" aria-label="Hapus konteks produk">×</button>
    </div>

    <div class="unified-chatbot-body">
        <!-- Chatbot Messages -->
        <div class="unified-chatbot-messages" id="unifiedChatbotMessages">
            <div class="unified-message bot-message">
                <div class="unified-message-content">
                    <div class="unified-message-bubble">
                        <p>Halo! Selamat datang di LGI Store! Ada yang bisa saya bantu hari ini?</p>
                        <span class="unified-message-time" id="welcomeTime"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Replies -->
        <div class="unified-quick-replies">
            <button type="button" class="unified-quick-reply" data-question="stok">Cek stok</button>
            <button type="button" class="unified-quick-reply" data-question="harga">Estimasi harga</button>
            <button type="button" class="unified-quick-reply" data-question="kirim">Estimasi kirim</button>
            <button type="button" class="unified-quick-reply" data-question="custom">Custom desain</button>
            <button type="button" class="unified-quick-reply" data-question="promo">Promo</button>
        </div>

        <!-- Chatbot Input -->
        <div class="unified-chatbot-input-wrapper">
            <div class="unified-chatbot-input-container">
                <input
                    type="text"
                    class="unified-chatbot-input"
                    id="unifiedChatbotInput"
                    placeholder="Ketik pesan..."
                    maxlength="500"
                >
                <button class="unified-chatbot-send" id="unifiedChatbotSend" aria-label="Kirim">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Store user avatar for JS -->
<script>
    window.userChatAvatar = @json($userAvatar);
</script>

<style>
/* Unified Chatbot Popup Styles - Reset and Base */
.unified-chatbot-popup,
.unified-chatbot-popup * {
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
}

.unified-chatbot-popup p {
    margin: 0;
    padding: 0;
}

.unified-chatbot-trigger {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    background-color: #fbbf24;
    color: #1a2332;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 9998;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.unified-chatbot-trigger:hover {
    background-color: #e0a800;
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.unified-chatbot-trigger.hidden {
    display: none;
}

/* Chat Unread Badge - Green dot with number */
.chat-unread-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    min-width: 20px;
    height: 20px;
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: #ffffff;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 5px;
    box-shadow: 0 2px 6px rgba(34, 197, 94, 0.4);
    border: 2px solid #ffffff;
    animation: chatBadgePulse 2s infinite;
}

@keyframes chatBadgePulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 2px 6px rgba(34, 197, 94, 0.4);
    }
    50% {
        transform: scale(1.1);
        box-shadow: 0 3px 10px rgba(34, 197, 94, 0.6);
    }
}

.unified-chatbot-popup {
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 360px;
    max-width: calc(100vw - 40px);
    height: 520px;
    max-height: calc(100vh - 120px);
    background-color: #ffffff;
    border-radius: 16px;
    display: none;
    flex-direction: column;
    overflow: hidden;
    z-index: 9999;
    animation: unifiedSlideUp 0.3s ease;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.unified-chatbot-popup.active {
    display: flex;
}

@keyframes unifiedSlideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.unified-chatbot-header {
    background: #fff;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.unified-chatbot-avatar {
    width: 40px;
    height: 40px;
    min-width: 40px;
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    flex-shrink: 0;
    overflow: hidden;
}

.unified-chatbot-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.unified-chatbot-avatar .avatar-fallback {
    font-size: 18px;
    line-height: 1;
}

.unified-chatbot-info {
    flex: 1;
    min-width: 0;
}

.unified-chatbot-name {
    font-size: 14px;
    font-weight: 600;
    color: #0f1e3d;
    margin-bottom: 2px;
    line-height: 1.2;
}

.unified-chatbot-status {
    font-size: 11px;
    color: #666;
    line-height: 1.3;
    display: flex;
    align-items: center;
    gap: 6px;
}

.unified-chatbot-status .status-dot {
    width: 8px;
    height: 8px;
    background: #22c55e;
    border-radius: 50%;
    display: inline-block;
}

.unified-chatbot-close {
    background: transparent;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 6px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.unified-chatbot-close:hover {
    background: #e5e7eb;
    color: #374151;
}

/* Product Context Badge */
.unified-product-context {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: linear-gradient(135deg, #0f1e3d 0%, #1a2d5a 100%);
    color: #fff;
    font-size: 13px;
}

.product-context-label {
    color: rgba(255, 255, 255, 0.7);
}

.product-context-name {
    flex: 1;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-context-clear {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: #fff;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    line-height: 1;
}

.product-context-clear:hover {
    background: rgba(255, 255, 255, 0.3);
}

.unified-chatbot-body {
    display: flex;
    flex-direction: column;
    flex: 1;
    overflow: hidden;
    min-height: 0;
}

.unified-chatbot-messages {
    flex: 1;
    padding: 12px;
    overflow-y: auto;
    overflow-x: hidden;
    background-color: #ffffff;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.unified-chatbot-messages::-webkit-scrollbar {
    width: 4px;
}

.unified-chatbot-messages::-webkit-scrollbar-track {
    background: transparent;
}

.unified-chatbot-messages::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 2px;
}

/* Clean Chat Messages */
.unified-chatbot-popup .unified-message {
    display: inline-flex;
    flex-direction: column;
    max-width: 75%;
    width: auto;
    animation: unifiedFadeIn 0.2s ease;
    padding: 0 !important;
    margin: 0 0 6px 0 !important;
    border: none !important;
    background: none !important;
}

@keyframes unifiedFadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

.unified-chatbot-popup .unified-message.bot-message {
    align-self: flex-start;
    background: none !important;
}

.unified-chatbot-popup .unified-message.user-message {
    align-self: flex-end;
    background: none !important;
}

/* Hide avatars */
.unified-chatbot-popup .unified-message-avatar {
    display: none !important;
}

/* System message style */
.unified-chatbot-popup .unified-message.system-message {
    max-width: 90%;
    align-self: center;
    background: none !important;
}

.unified-chatbot-popup .unified-message.system-message .unified-message-bubble {
    background: #fef3c7 !important;
    color: #92400e;
    border-radius: 8px;
    font-size: 12px;
    text-align: center;
    padding: 4px 10px !important;
}

.unified-chatbot-popup .unified-message-content {
    display: inline-block;
    background: none !important;
    padding: 0 !important;
    margin: 0 !important;
}

    display: inline-block;
    padding: 6px 10px !important;
    border-radius: 10px;
    font-size: 13px;
    line-height: 1.35;
    word-wrap: break-word;
    overflow-wrap: break-word;
    white-space: pre-line;
}

/* Bot message - Light gray bubble */
.unified-chatbot-popup .unified-message.bot-message .unified-message-bubble {
    background: #f0f0f0 !important;
    color: #1a1a1a;
    border-top-left-radius: 3px;
}

/* User message - Green bubble ONLY */
.unified-chatbot-popup .unified-message.user-message .unified-message-bubble {
    background: #dcf8c6 !important;
    color: #1a1a1a;
    border-top-right-radius: 3px;
}

.unified-chatbot-popup .unified-message-bubble p {
    margin: 0 !important;
    padding: 0 !important;
    font-size: 13px;
    line-height: 1.35;
    display: inline;
    white-space: pre-line;
    text-indent: 0 !important;
}

/* User message text - inline for compact display */
}

/* Time inside bubble */
.unified-chatbot-popup .unified-message-bubble .unified-message-time {
    font-size: 10px;
    color: #667781;
}

/* Bot message time - block at bottom right */
.unified-chatbot-popup .unified-message.bot-message .unified-message-bubble .unified-message-time {
    display: block;
    text-align: right;
    margin-top: 2px !important;
    padding: 0 !important;
}

/* Product Card inside Chat Message */
.unified-chatbot-popup .chat-product-card {
    display: flex;
    flex-direction: column;
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    margin-top: 10px;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    max-width: 200px;
}

.unified-chatbot-popup .chat-product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.unified-chatbot-popup .chat-product-image {
    width: 100%;
    height: 120px;
    overflow: hidden;
    background: #f5f5f5;
}

.unified-chatbot-popup .chat-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.unified-chatbot-popup .chat-product-info {
    padding: 10px 12px;
    background: #fff;
}

.unified-chatbot-popup .chat-product-name {
    font-size: 13px;
    font-weight: 600;
    color: #1a2332;
    margin-bottom: 4px;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.unified-chatbot-popup .chat-product-price {
    font-size: 14px;
    font-weight: 700;
    color: #e53935;
    margin-bottom: 6px;
}

.unified-chatbot-popup .chat-product-link {
    font-size: 12px;
    color: #25D366;
    font-weight: 500;
}

.unified-chatbot-popup .chat-product-card:hover .chat-product-link {
    text-decoration: underline;
}

/* Product Cards Grid in Bot Response */
.unified-chatbot-popup .chat-product-cards-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    margin-top: 10px;
    margin-bottom: 4px;
}

.unified-chatbot-popup .chat-product-mini-card {
    display: flex;
    flex-direction: column;
    background: #ffffff;
    border-radius: 8px;
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.unified-chatbot-popup .chat-product-mini-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
}

.unified-chatbot-popup .chat-product-mini-image {
    width: 100%;
    height: 70px;
    overflow: hidden;
    background: #f5f5f5;
}

.unified-chatbot-popup .chat-product-mini-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.unified-chatbot-popup .chat-product-mini-info {
    padding: 6px 8px;
    background: #fff;
}

.unified-chatbot-popup .chat-product-mini-name {
    font-size: 11px;
    font-weight: 600;
    color: #1a2332;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 2px;
}

.unified-chatbot-popup .chat-product-mini-price {
    font-size: 12px;
    font-weight: 700;
    color: #e53935;
}

/* Adjust user message with product card */
.unified-chatbot-popup .unified-message.user-message.product-inquiry-message .unified-message-bubble {
    background: #dcf8c6;
    max-width: 280px;
}

.unified-chatbot-popup .unified-message.user-message.product-inquiry-message .unified-message-bubble p {
    display: block;
    margin-bottom: 5px;
}

.unified-quick-replies {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 8px 12px;
    background: #fff;
    border-top: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.unified-quick-reply {
    padding: 6px 12px;
    border-radius: 16px;
    border: 1px solid #d1d5db;
    background: #fff;
    font-size: 12px;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.unified-quick-reply:hover {
    background: #0f172a;
    color: #fff;
    border-color: #0f172a;
}

.unified-chatbot-input-wrapper {
    padding: 10px 12px;
    padding-bottom: 12px;
    background: #fff;
    border-top: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.unified-chatbot-input-container {
    display: flex;
    align-items: center;
    gap: 8px;
}

.unified-chatbot-input {
    flex: 1;
    padding: 10px 14px;
    border: 1.5px solid #e5e7eb;
    border-radius: 22px;
    font-size: 13px;
    outline: none;
    background: #f9fafb;
    transition: all 0.2s;
}

.unified-chatbot-input:focus {
    border-color: #0f172a;
    background: #fff;
}

.unified-chatbot-send {
    background: #0f172a;
    color: white;
    border: none;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    cursor: pointer;
    transition: background 0.2s;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.unified-chatbot-send:hover {
    background: #1e293b;
}

/* Typing indicator */
.unified-typing-indicator {
    display: flex;
    gap: 4px;
    padding: 4px 0;
}

.unified-typing-indicator span {
    width: 6px;
    height: 6px;
    background: #9ca3af;
    border-radius: 50%;
    animation: unifiedTypingBounce 1.4s infinite ease-in-out;
}

.unified-typing-indicator span:nth-child(1) { animation-delay: 0s; }
.unified-typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.unified-typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

@keyframes unifiedTypingBounce {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
    30% { transform: translateY(-8px); opacity: 1; }
}

/* Responsive */
@media (max-width: 480px) {
    .unified-chatbot-popup {
        width: calc(100% - 20px);
        right: 10px;
        bottom: 80px;
        height: 65vh;
        max-height: calc(100vh - 100px);
    }
    
    .unified-quick-reply {
        font-size: 12px;
        padding: 6px 12px;
    }
    
    .unified-message-avatar {
        width: 28px;
        height: 28px;
        min-width: 28px;
    }
    
    .unified-message-avatar .material-icons {
        font-size: 16px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const trigger = document.getElementById('unifiedChatbotTrigger');
    const popup = document.getElementById('unifiedChatbotPopup');
    const closeBtn = document.getElementById('unifiedChatbotClose');
    const messages = document.getElementById('unifiedChatbotMessages');
    const input = document.getElementById('unifiedChatbotInput');
    const sendBtn = document.getElementById('unifiedChatbotSend');
    const quickReplies = document.querySelectorAll('.unified-quick-reply');
    const welcomeTime = document.getElementById('welcomeTime');
    
    // Set welcome time
    if (welcomeTime) {
        welcomeTime.textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }
    
    // Conversation ID for database integration
    let conversationId = null;
    
    // Toggle popup
    if (trigger) {
        trigger.addEventListener('click', function() {
            popup.classList.add('active');
            trigger.classList.add('hidden');
            input.focus();
            loadChatHistory();
        });
    }
    
    // Close popup
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            popup.classList.remove('active');
            trigger.classList.remove('hidden');
        });
    }
    
    // Click outside to close
    document.addEventListener('click', function(e) {
        if (popup && trigger && !popup.contains(e.target) && !trigger.contains(e.target) && popup.classList.contains('active')) {
            popup.classList.remove('active');
            trigger.classList.remove('hidden');
        }
    });
    
    // Send message on button click
    if (sendBtn) {
        sendBtn.addEventListener('click', sendMessage);
    }
    
    // Send message on Enter
    if (input) {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }
    
    // Quick replies
    quickReplies.forEach(btn => {
        btn.addEventListener('click', function() {
            const questionType = this.dataset.question;
            handleQuickReply(questionType);
        });
    });
    
    // Load chat history from database
    async function loadChatHistory() {
        try {
            const response = await fetch('/api/chatbot/history', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.messages) {
                    conversationId = data.conversation_id;
                    // Clear existing messages except welcome
                    const welcomeMsg = messages.querySelector('.bot-message');
                    messages.innerHTML = '';
                    
                    if (data.messages.length === 0 && welcomeMsg) {
                        messages.appendChild(welcomeMsg);
                    } else {
                        data.messages.forEach(msg => {
                            // Check if message has product context in metadata (user message)
                            const hasProductContext = msg.metadata && msg.metadata.product_context;
                            // Check if message has products in metadata (bot message)
                            const hasProducts = msg.metadata && msg.metadata.products && msg.metadata.products.length > 0;
                            
                            if (hasProductContext && msg.sender_type === 'user') {
                                // Parse product context and display with product card
                                let productData = msg.metadata.product_context;
                                if (typeof productData === 'string') {
                                    try {
                                        productData = JSON.parse(productData);
                                    } catch (e) {
                                        console.log('Could not parse product context:', e);
                                    }
                                }
                                
                                if (productData && typeof productData === 'object') {
                                    addMessageWithProductCard(msg.message, productData, msg.created_at);
                                } else {
                                    addMessageToUI(msg.message, 'user', msg.created_at);
                                }
                            } else if (hasProducts && msg.sender_type === 'bot') {
                                // Bot message with product cards
                                addMessageWithProductCardsFromHistory(msg.message, msg.metadata.products, msg.created_at);
                            } else {
                                addMessageToUI(msg.message, msg.sender_type === 'user' ? 'user' : 'bot', msg.created_at);
                            }
                        });
                    }
                    scrollToBottom();
                }
            }
        } catch (error) {
            console.log('Chat history not available:', error);
        }
    }
    
    // Add message with product card (for product inquiries from history)
    function addMessageWithProductCard(text, product, timestamp = null) {
        const time = timestamp ? new Date(timestamp).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) 
                              : new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        
        const productCardHTML = buildProductCardHTML(product);
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'unified-message user-message product-inquiry-message';
        messageDiv.innerHTML = `
            <div class="unified-message-content">
                <div class="unified-message-bubble">
                    <p>${text}</p>
                    ${productCardHTML}
                    <span class="unified-message-time">${time}</span>
                </div>
            </div>
        `;
        
        messages.appendChild(messageDiv);
    }
    
    // Send message
    async function sendMessage() {
        const text = input.value.trim();
        if (!text) return;
        
        // Add user message to UI
        addMessageToUI(text, 'user');
        input.value = '';
        
        // Show typing indicator
        showTyping();
        
        // Build request body with product context if available
        const requestBody = {
            message: text,
            conversation_id: conversationId
        };
        
        // Add product context if we have it (including selected variants)
        if (currentProduct) {
            requestBody.product_context = {
                id: currentProduct.id,
                name: currentProduct.name,
                price: currentProduct.price || currentProduct.price_min,
                custom_allowed: currentProduct.custom_allowed,
                selected_color: currentProduct.selected_color || null,
                selected_size: currentProduct.selected_size || null,
                quantity: currentProduct.quantity || 1
            };
        }
        
        // Save to database and get response
        try {
            const response = await fetch('/api/chatbot/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify(requestBody)
            });
            
            hideTyping();
            
            if (response.ok) {
                const data = await response.json();
                console.log('Chatbot API response:', data);
                if (data.success) {
                    conversationId = data.conversation_id;
                    
                    // Check if admin is handling the conversation
                    if (data.admin_handling) {
                        // Admin has taken over - NO response at all
                        // Customer message is saved, wait for admin to reply manually
                        // Don't show any message - just let it be empty
                    } else if (data.bot_response) {
                        // Show bot response with product cards if available
                        console.log('Products in response:', data.products);
                        if (data.products && data.products.length > 0) {
                            addMessageWithProductCards(data.bot_response, data.products);
                        } else {
                            addMessageToUI(data.bot_response, 'bot');
                        }
                    }
                }
            } else {
                // Fallback to local response
                const botResponse = generateLocalResponse(text);
                addMessageToUI(botResponse, 'bot');
            }
        } catch (error) {
            hideTyping();
            // Fallback to local response
            const botResponse = generateLocalResponse(text);
            addMessageToUI(botResponse, 'bot');
        }
        
        scrollToBottom();
    }
    
    // Handle quick reply
    function handleQuickReply(type) {
        const questions = {
            'stok': 'Apakah stok produk ini tersedia?',
            'harga': 'Rekomendasi produk harga murah',
            'kirim': 'Berapa lama estimasi pengiriman?',
            'custom': 'Apakah bisa custom desain?',
            'promo': 'Ada diskon atau promo saat ini?'
        };
        
        const text = questions[type] || 'Halo';
        input.value = text;
        sendMessage();
    }
    
    // Get bot avatar HTML
    function getBotAvatarHTML() {
        return `
            <img src="/images/logo.png" alt="Bot" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <span class="avatar-fallback" style="display:none;">🏪</span>
        `;
    }
    
    // Get user avatar HTML
    function getUserAvatarHTML() {
        if (window.userChatAvatar) {
            return `
                <img src="${window.userChatAvatar}" alt="User" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <span class="avatar-fallback" style="display:none;">👤</span>
            `;
        }
        return `<span class="avatar-fallback">👤</span>`;
    }
    
    // Add bot message with product cards from history
    function addMessageWithProductCardsFromHistory(text, products, timestamp) {
        const time = timestamp ? new Date(timestamp).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) 
                              : new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        
        // Build product cards HTML
        let productCardsHTML = '<div class="chat-product-cards-grid">';
        products.forEach(product => {
            const productUrl = `/produk/${product.slug || product.id}`;
            const imageUrl = product.image || '/images/no-image.png';
            const fallbackImage = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120"%3E%3Crect fill="%23e0e0e0" width="120" height="120"/%3E%3Ctext fill="%23999" font-family="Arial" font-size="12" x="50%25" y="50%25" text-anchor="middle" dy=".3em"%3ENo Image%3C/text%3E%3C/svg%3E';
            
            productCardsHTML += `
                <a href="${productUrl}" class="chat-product-mini-card" target="_blank">
                    <div class="chat-product-mini-image">
                        <img src="${imageUrl}" alt="${product.name}" onerror="this.onerror=null; this.src='${fallbackImage}'">
                    </div>
                    <div class="chat-product-mini-info">
                        <div class="chat-product-mini-name">${product.name}</div>
                        <div class="chat-product-mini-price">Rp ${product.formatted_price}</div>
                    </div>
                </a>
            `;
        });
        productCardsHTML += '</div>';
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'unified-message bot-message';
        messageDiv.innerHTML = `
            <div class="unified-message-content">
                <div class="unified-message-bubble">
                    <p>${text}</p>
                    ${productCardsHTML}
                    <span class="unified-message-time">${time}</span>
                </div>
            </div>
        `;
        
        messages.appendChild(messageDiv);
    }
    
    // Add bot message with product cards
    function addMessageWithProductCards(text, products) {
        const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        
        // Build product cards HTML
        let productCardsHTML = '<div class="chat-product-cards-grid">';
        products.forEach(product => {
            const productUrl = `/produk/${product.slug || product.id}`;
            const imageUrl = product.image || '/images/no-image.png';
            const fallbackImage = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120"%3E%3Crect fill="%23e0e0e0" width="120" height="120"/%3E%3Ctext fill="%23999" font-family="Arial" font-size="12" x="50%25" y="50%25" text-anchor="middle" dy=".3em"%3ENo Image%3C/text%3E%3C/svg%3E';
            
            productCardsHTML += `
                <a href="${productUrl}" class="chat-product-mini-card" target="_blank">
                    <div class="chat-product-mini-image">
                        <img src="${imageUrl}" alt="${product.name}" onerror="this.onerror=null; this.src='${fallbackImage}'">
                    </div>
                    <div class="chat-product-mini-info">
                        <div class="chat-product-mini-name">${product.name}</div>
                        <div class="chat-product-mini-price">Rp ${product.formatted_price}</div>
                    </div>
                </a>
            `;
        });
        productCardsHTML += '</div>';
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'unified-message bot-message';
        messageDiv.innerHTML = `
            <div class="unified-message-content">
                <div class="unified-message-bubble">
                    <p>${text}</p>
                    ${productCardsHTML}
                    <span class="unified-message-time">${time}</span>
                </div>
            </div>
        `;
        
        messages.appendChild(messageDiv);
        scrollToBottom();
    }
    
    // Add message to UI - WhatsApp style (no avatars)
    function addMessageToUI(text, sender, timestamp = null) {
        const time = timestamp ? new Date(timestamp).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) 
                              : new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        
        const messageDiv = document.createElement('div');
        
        // Handle system messages (waiting for admin)
        if (sender === 'system') {
            messageDiv.className = 'unified-message system-message';
            messageDiv.innerHTML = `
                <div class="unified-message-content">
                    <div class="unified-message-bubble">
                        <p>${text}</p>
                    </div>
                </div>
            `;
            messages.appendChild(messageDiv);
            scrollToBottom();
            return;
        }
        
        messageDiv.className = `unified-message ${sender === 'user' ? 'user-message' : 'bot-message'}`;
        
        // WhatsApp style - no avatars, just bubble with time inside
        messageDiv.innerHTML = `
            <div class="unified-message-content">
                <div class="unified-message-bubble">
                    <p>${text}</p>
                    <span class="unified-message-time">${time}</span>
                </div>
            </div>
        `;
        
        messages.appendChild(messageDiv);
        scrollToBottom();
    }
    
    // Show typing indicator
    function showTyping() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'unified-message bot-message';
        typingDiv.id = 'unifiedTypingIndicator';
        typingDiv.innerHTML = `
            <div class="unified-message-content">
                <div class="unified-message-bubble">
                    <div class="unified-typing-indicator">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>
        `;
        messages.appendChild(typingDiv);
        scrollToBottom();
    }
    
    // Hide typing indicator
    function hideTyping() {
        const typing = document.getElementById('unifiedTypingIndicator');
        if (typing) typing.remove();
    }
    
    // Generate local response (fallback)
    function generateLocalResponse(userMessage) {
        const msg = userMessage.toLowerCase();
        
        if (msg.includes('harga') || msg.includes('berapa')) {
            return 'Untuk informasi harga lengkap, silakan kunjungi halaman katalog atau detail produk. Harga bervariasi tergantung produk dan ukuran yang dipilih.';
        } else if (msg.includes('stok') || msg.includes('tersedia')) {
            return 'Untuk cek ketersediaan stok, silakan lihat detail produk atau hubungi admin kami. Kebanyakan produk kami ready stock!';
        } else if (msg.includes('kirim') || msg.includes('pengiriman')) {
            return 'Kami melayani pengiriman ke seluruh Indonesia. Estimasi 2-4 hari untuk Jawa dan 3-7 hari untuk luar Jawa.';
        } else if (msg.includes('custom') || msg.includes('desain')) {
            return 'Ya, kami menerima custom design! Produk dengan label CUSTOM bisa didesain sesuai keinginan Anda. Silakan upload desain saat checkout.';
        } else if (msg.includes('promo') || msg.includes('diskon')) {
            return 'Untuk promo terbaru, silakan cek halaman utama atau katalog kami. Kami sering mengadakan diskon menarik!';
        } else if (msg.includes('terima kasih') || msg.includes('thanks')) {
            return 'Sama-sama! Senang bisa membantu. Jika ada pertanyaan lain, jangan ragu untuk bertanya ya! 😊';
        } else if (msg.includes('halo') || msg.includes('hai') || msg.includes('hello')) {
            return 'Halo! Ada yang bisa saya bantu hari ini?';
        }
        
        return 'Terima kasih atas pertanyaan Anda! Untuk informasi lebih detail, silakan hubungi admin kami atau kunjungi halaman chat untuk berbicara dengan tim support.';
    }
    
    // Scroll to bottom
    function scrollToBottom() {
        if (messages) {
            messages.scrollTop = messages.scrollHeight;
        }
    }
    
    // Product context elements
    const productContext = document.getElementById('unifiedProductContext');
    const productNameEl = document.getElementById('unifiedProductName');
    const clearProductBtn = document.getElementById('unifiedClearProduct');
    
    // Current product data
    let currentProduct = null;
    
    // Clear product context
    if (clearProductBtn) {
        clearProductBtn.addEventListener('click', function() {
            currentProduct = null;
            if (productContext) productContext.style.display = 'none';
        });
    }
    
    // Set product context with variant info
    function setProductContext(product) {
        currentProduct = product;
        if (productContext && productNameEl && product) {
            // Build display text with variant info
            let displayText = product.name;
            
            // Add variant info if selected
            let variantParts = [];
            if (product.selected_color) {
                variantParts.push(product.selected_color.label);
            }
            if (product.selected_size) {
                variantParts.push('Size ' + product.selected_size.label);
            }
            if (variantParts.length > 0) {
                displayText += ' (' + variantParts.join(', ') + ')';
            }
            
            productNameEl.textContent = displayText;
            productContext.style.display = 'flex';
        }
    }
    
    // Build user inquiry message text
    function buildProductInquiryText(product) {
        let msg = `Halo, saya tertarik dengan produk ${product.name}`;
        
        // Add variant selection info if available
        if (product.selected_color || product.selected_size) {
            let variants = [];
            if (product.selected_color) {
                variants.push(`warna ${product.selected_color.label}`);
            }
            if (product.selected_size) {
                variants.push(`ukuran ${product.selected_size.label}`);
            }
            msg += ` (${variants.join(', ')})`;
        }
        
        if (product.quantity && product.quantity > 1) {
            msg += ` sebanyak ${product.quantity} pcs`;
        }
        
        msg += `. Boleh saya tahu lebih lanjut tentang produk ini?`;
        return msg;
    }
    
    // Build product card HTML for chat
    function buildProductCardHTML(product) {
        const productUrl = `/produk/${product.slug || product.id}`;
        const imageUrl = product.image || '/images/no-image.png';
        // Use a simple gray placeholder as data URI to avoid external requests
        const fallbackImage = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120"%3E%3Crect fill="%23e0e0e0" width="120" height="120"/%3E%3Ctext fill="%23999" font-family="Arial" font-size="12" x="50%25" y="50%25" text-anchor="middle" dy=".3em"%3ENo Image%3C/text%3E%3C/svg%3E';
        
        return `
            <a href="${productUrl}" class="chat-product-card" target="_blank">
                <div class="chat-product-image">
                    <img src="${imageUrl}" alt="${product.name}" onerror="this.onerror=null; this.src='${fallbackImage}'">
                </div>
                <div class="chat-product-info">
                    <div class="chat-product-name">${product.name}</div>
                    <div class="chat-product-price">Rp ${product.formatted_price}</div>
                    <div class="chat-product-link">Lihat Detail →</div>
                </div>
            </a>
        `;
    }
    
    // Open chatbot with product context (called from product card or detail page)
    window.openUnifiedChatbotWithProduct = function(product) {
        console.log('Opening chatbot with product:', product);
        
        // Set product context (this will be sent with messages)
        setProductContext(product);
        
        // Open popup
        if (popup) popup.classList.add('active');
        if (trigger) trigger.classList.add('hidden');
        
        // Load existing chat history first
        loadChatHistory().then(() => {
            const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            
            // Create USER message with product inquiry
            const inquiryText = buildProductInquiryText(product);
            const productCardHTML = buildProductCardHTML(product);
            
            const messageDiv = document.createElement('div');
            messageDiv.className = 'unified-message user-message product-inquiry-message';
            messageDiv.innerHTML = `
                <div class="unified-message-content">
                    <div class="unified-message-bubble">
                        <p>${inquiryText}</p>
                        ${productCardHTML}
                        <span class="unified-message-time">${time}</span>
                    </div>
                </div>
            `;
            messages.appendChild(messageDiv);
            scrollToBottom();
            
            // Show typing indicator while waiting for bot response
            showTyping();
            
            // Send to server and get bot response
            sendProductInquiryAndGetResponse(product, inquiryText);
        });
        
        // Focus input
        if (input) input.focus();
        
        // Mark messages as read when opening chat
        markMessagesAsRead();
    };
    
    // Send product inquiry to server and display bot response
    async function sendProductInquiryAndGetResponse(product, inquiryText) {
        console.log('sendProductInquiryAndGetResponse called', { product, inquiryText });
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            console.log('CSRF Token:', csrfToken ? 'Found' : 'NOT FOUND');
            
            const requestBody = {
                message: inquiryText,
                product_id: product.id,
                product_name: product.name,
                product_context: JSON.stringify(product)
            };
            console.log('Request body:', requestBody);
            
            const response = await fetch('/api/chatbot/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(requestBody)
            });
            
            console.log('Response status:', response.status);
            
            hideTyping();
            
            if (response.ok) {
                const data = await response.json();
                console.log('Product inquiry response:', data);
                
                if (data.success) {
                    conversationId = data.conversation_id;
                    
                    // Show bot response if not handled by admin
                    if (!data.admin_handling && data.bot_response) {
                        addMessageToUI(data.bot_response, 'bot');
                    } else if (data.admin_handling) {
                        // Admin handling - show waiting message
                        addMessageToUI('Pesan Anda telah terkirim. Admin akan segera membalas. 😊', 'bot');
                    }
                }
            } else {
                const errorText = await response.text();
                console.error('Response error:', errorText);
                // Fallback response
                addMessageToUI(`Terima kasih atas ketertarikan Anda pada ${product.name}! Ada yang bisa saya bantu?`, 'bot');
            }
            
            scrollToBottom();
        } catch (error) {
            hideTyping();
            console.error('Fetch error:', error);
            // Fallback response
            addMessageToUI(`Terima kasih atas ketertarikan Anda pada ${product.name}! Ada yang bisa saya bantu?`, 'bot');
            scrollToBottom();
        }
    }
    
    // Also expose simple open function
    window.openUnifiedChatbot = function() {
        if (popup) popup.classList.add('active');
        if (trigger) trigger.classList.add('hidden');
        if (input) input.focus();
        loadChatHistory();
        
        // Mark messages as read when opening chat
        markMessagesAsRead();
    };
    
    // ===== UNREAD BADGE FUNCTIONALITY =====
    const unreadBadge = document.getElementById('chatUnreadBadge');
    let unreadCount = 0;
    
    // Fetch unread count from server
    async function fetchUnreadCount() {
        try {
            const response = await fetch('/api/chatbot/unread-count', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    updateUnreadBadge(data.unread_count);
                }
            }
        } catch (error) {
            console.log('Could not fetch unread count:', error);
        }
    }
    
    // Update badge display
    function updateUnreadBadge(count) {
        unreadCount = count;
        if (unreadBadge) {
            if (count > 0) {
                unreadBadge.textContent = count > 99 ? '99+' : count;
                unreadBadge.style.display = 'flex';
            } else {
                unreadBadge.style.display = 'none';
            }
        }
    }
    
    // Mark all messages as read
    async function markMessagesAsRead() {
        if (unreadCount === 0) return;
        
        try {
            const response = await fetch('/api/chatbot/mark-read', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            
            if (response.ok) {
                updateUnreadBadge(0);
            }
        } catch (error) {
            console.log('Could not mark messages as read:', error);
        }
    }
    
    // Initial fetch on page load
    fetchUnreadCount();
    
    // Periodically check for new messages (every 30 seconds)
    setInterval(fetchUnreadCount, 30000);
});
</script>
