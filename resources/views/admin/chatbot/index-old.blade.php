<x-admin-layout title="Chatbot Management">
    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <style>
            /* Override halaman utama - no scroll pada body */
            html, body { overflow: hidden !important; }
            .page-content { 
                overflow: hidden !important; 
                padding: 0 !important;
                display: flex !important;
                flex-direction: column !important;
            }
            .page-content::-webkit-scrollbar { display: none !important; }

            * {
                box-sizing: border-box;
            }

            .chatbot-container {
                display: flex;
                flex: 1;
                background: #f8f9fa;
                gap: 16px;
                padding: 16px;
                overflow: hidden;
                min-height: 0;
            }

            .conversation-sidebar {
                width: 350px;
                min-width: 300px;
                max-width: 400px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                display: flex;
                flex-direction: column;
                overflow: hidden;
                height: 100%;
            }

            .sidebar-header {
                padding: 16px 20px;
                border-bottom: 1px solid #e9ecef;
                flex-shrink: 0;
            }

            .sidebar-header h2 {
                margin: 0;
                font-size: 18px;
                font-weight: 600;
            }

            .filter-controls {
                display: flex;
                gap: 6px;
                margin-top: 12px;
                flex-wrap: wrap;
            }

            .filter-btn {
                padding: 5px 10px;
                border: 1px solid #dee2e6;
                background: white;
                border-radius: 16px;
                cursor: pointer;
                font-size: 11px;
                transition: all 0.3s ease;
            }

            .filter-btn.active {
                background: #007bff;
                color: white;
                border-color: #007bff;
            }

            .filter-btn:hover {
                border-color: #007bff;
            }

            .sidebar-search {
                padding: 12px 16px;
                border-bottom: 1px solid #e9ecef;
                flex-shrink: 0;
            }

            .search-input {
                width: 100%;
                padding: 8px 12px;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                font-size: 13px;
            }

            .conversation-list {
                flex: 1;
                overflow-y: auto;
                padding: 0;
                min-height: 0;
            }

            /* Custom scrollbar for conversation list */
            .conversation-list::-webkit-scrollbar {
                width: 6px;
            }

            .conversation-list::-webkit-scrollbar-track {
                background: #f1f1f1;
            }

            .conversation-list::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 3px;
            }

            .conversation-list::-webkit-scrollbar-thumb:hover {
                background: #a1a1a1;
            }

            .conversation-item {
                padding: 12px 16px;
                border-bottom: 1px solid #f1f3f5;
                cursor: pointer;
                transition: background 0.2s ease;
                display: flex;
                gap: 10px;
                align-items: flex-start;
            }

            .conversation-item:hover {
                background: #f8f9fa;
            }

            .conversation-item.active {
                background: #e7f3ff;
                border-left: 3px solid #007bff;
            }

            .conversation-avatar {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                background: #007bff;
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
                font-size: 14px;
                flex-shrink: 0;
            }

            .conversation-info {
                flex: 1;
                min-width: 0;
                padding-right: 35px; /* Space for unread badge */
            }

            .conversation-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 4px;
            }

            .conversation-name {
                font-weight: 600;
                font-size: 13px;
                color: #212529;
            }

            .conversation-time {
                font-size: 11px;
                color: #6c757d;
            }

            .conversation-preview {
                font-size: 12px;
                color: #6c757d;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .conversation-badges {
                position: absolute;
                top: 50%;
                right: 12px;
                transform: translateY(-50%);
            }

            .unread-badge {
                min-width: 20px;
                height: 20px;
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                color: #fff;
                border-radius: 10px;
                font-size: 11px;
                font-weight: 700;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0 6px;
                box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
            }

            .chat-main {
                flex: 1;
                display: flex;
                flex-direction: column;
                background: white;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                overflow: hidden;
                height: 100%;
                min-width: 0;
            }

            .chat-header {
                padding: 16px 20px;
                border-bottom: 1px solid #e9ecef;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-shrink: 0;
                background: white;
            }

            .chat-header-left {
                display: flex;
                gap: 12px;
                align-items: center;
            }

            .chat-header-info h3 {
                margin: 0;
                font-size: 15px;
                font-weight: 600;
            }

            .chat-header-info p {
                margin: 2px 0 0 0;
                font-size: 12px;
                color: #6c757d;
            }

            .chat-header-actions {
                display: flex;
                gap: 8px;
            }

            .action-btn {
                padding: 8px 14px;
                border: 1px solid #dee2e6;
                background: white;
                border-radius: 6px;
                cursor: pointer;
                font-size: 12px;
                transition: all 0.3s ease;
                white-space: nowrap;
            }

            .action-btn:hover {
                background: #f8f9fa;
            }

            .action-btn.primary {
                background: #007bff;
                color: white;
                border-color: #007bff;
            }

            .action-btn.primary:hover {
                background: #0056b3;
            }

            .action-btn.danger {
                background: #dc3545;
                color: white;
                border-color: #dc3545;
            }

            .action-btn.danger:hover {
                background: #c82333;
            }

            .chat-messages {
                flex: 1;
                overflow-y: auto;
                padding: 16px 20px;
                display: flex;
                flex-direction: column;
                gap: 8px;
                min-height: 0;
                background: #f8f9fa;
            }

            /* Custom scrollbar for chat messages */
            .chat-messages::-webkit-scrollbar {
                width: 6px;
            }

            .chat-messages::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 3px;
            }

            .chat-messages::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 3px;
            }

            .chat-messages::-webkit-scrollbar-thumb:hover {
                background: #a1a1a1;
            }

            .message {
                display: flex;
                gap: 8px;
                margin-bottom: 8px;
                animation: slideIn 0.3s ease;
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .message.customer {
                flex-direction: row;
                justify-content: flex-start;
            }

            .message.admin, .message.bot {
                flex-direction: row-reverse;
                justify-content: flex-start;
            }

            .message-bubble {
                padding: 10px 14px;
                border-radius: 12px;
                font-size: 13px;
                line-height: 1.5;
                word-wrap: break-word;
            }

            .message.customer .message-bubble {
                background: white;
                color: #212529;
                border: 1px solid #e9ecef;
                border-bottom-left-radius: 4px;
            }

            .message.admin .message-bubble {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                color: white;
                border-bottom-right-radius: 4px;
            }
            
            .message.bot .message-bubble {
                background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);
                color: white;
                border-bottom-right-radius: 4px;
            }
            
            .message-label {
                font-size: 10px;
                font-weight: 600;
                margin-bottom: 4px;
                text-align: right;
            }
            
            .message.admin .message-label {
                color: #28a745;
            }
            
            .message.bot .message-label {
                color: #007bff;
            }
            
            .message-content {
                display: flex;
                flex-direction: column;
                max-width: 65%;
            }

            .message.system .message-bubble {
                background: #fff3e0;
                color: #e65100;
                text-align: center;
                max-width: 100%;
                font-size: 11px;
                font-weight: 500;
                padding: 8px 16px;
                border-radius: 16px;
                margin: 8px auto;
            }

            .message-time {
                font-size: 10px;
                color: #9e9e9e;
                margin-top: 4px;
                display: block;
            }

            .message.admin .message-time {
                text-align: right;
            }
            
            /* Product Context Preview Styles */
            .product-context-preview {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                border: 1px solid #dee2e6;
                border-radius: 10px;
                padding: 10px 12px;
                margin-bottom: 8px;
                max-width: 280px;
            }
            
            .product-context-header {
                font-size: 10px;
                color: #6c757d;
                font-weight: 600;
                margin-bottom: 6px;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .product-context-header i {
                color: #007bff;
            }
            
            .product-context-body {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }
            
            .product-context-name {
                font-size: 13px;
                font-weight: 600;
                color: #212529;
                line-height: 1.3;
            }
            
            .product-context-price {
                font-size: 12px;
                color: #28a745;
                font-weight: 600;
            }
            
            .product-context-badge {
                display: inline-block;
                background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);
                color: white;
                font-size: 9px;
                padding: 2px 8px;
                border-radius: 10px;
                font-weight: 500;
                margin-top: 4px;
                width: fit-content;
            }
            
            .product-context-link {
                font-size: 10px;
                color: #007bff;
                margin-top: 6px;
                display: flex;
                align-items: center;
                gap: 4px;
                font-weight: 500;
            }
            
            a.product-context-preview {
                text-decoration: none;
                display: block;
                cursor: pointer;
                transition: all 0.2s ease;
                position: relative;
                z-index: 10;
            }
            
            a.product-context-preview:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
                border-color: #007bff;
            }
            
            a.product-context-preview:hover .product-context-link {
                color: #0056b3;
            }

            .chat-input-area {
                padding: 16px 20px;
                border-top: 1px solid #e9ecef;
                background: white;
                flex-shrink: 0;
            }

            .input-group {
                display: flex;
                gap: 10px;
                align-items: flex-end;
            }

            .chat-input {
                flex: 1;
                padding: 10px 14px;
                border: 1px solid #dee2e6;
                border-radius: 20px;
                font-size: 13px;
                resize: none;
                max-height: 80px;
                min-height: 40px;
                line-height: 1.4;
            }

            .chat-input:focus {
                outline: none;
                border-color: #007bff;
                box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
            }

            .send-btn {
                padding: 10px 18px;
                background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                color: white;
                border: none;
                border-radius: 20px;
                cursor: pointer;
                font-weight: 500;
                font-size: 13px;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                gap: 6px;
                white-space: nowrap;
            }

            .send-btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
            }

            .send-btn:disabled {
                background: #6c757d;
                cursor: not-allowed;
                transform: none;
                box-shadow: none;
            }

            .empty-state {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: 100%;
                color: #6c757d;
                padding: 40px;
            }

            .empty-state-icon {
                font-size: 64px;
                margin-bottom: 16px;
                opacity: 0.3;
                color: #007bff;
            }

            .empty-state p {
                font-size: 14px;
                margin: 0;
            }

            .notification-badge {
                position: absolute;
                top: -8px;
                right: -8px;
                background: #dc3545;
                color: white;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 11px;
                font-weight: 600;
            }

            /* Responsive - Tablet */
            @media (max-width: 1024px) {
                .chatbot-container {
                    flex-direction: column;
                    padding: 12px;
                    gap: 12px;
                }

                .conversation-sidebar {
                    width: 100%;
                    max-width: 100%;
                    height: 250px;
                    min-height: 200px;
                }

                .chat-main {
                    flex: 1;
                    min-height: 300px;
                }

                .chat-header-actions {
                    flex-wrap: wrap;
                }

                .action-btn {
                    padding: 6px 10px;
                    font-size: 11px;
                }
            }

            /* Responsive - Mobile */
            @media (max-width: 768px) {
                .chatbot-container {
                    padding: 8px;
                    gap: 8px;
                }

                .conversation-sidebar {
                    height: 200px;
                    min-height: 180px;
                }

                .sidebar-header {
                    padding: 12px 14px;
                }

                .sidebar-header h2 {
                    font-size: 16px;
                }

                .filter-btn {
                    padding: 4px 8px;
                    font-size: 10px;
                }

                .sidebar-search {
                    padding: 8px 12px;
                }

                .conversation-item {
                    padding: 10px 12px;
                }

                .conversation-avatar {
                    width: 32px;
                    height: 32px;
                    font-size: 12px;
                }

                .conversation-name {
                    font-size: 12px;
                }

                .conversation-preview {
                    font-size: 11px;
                }

                .chat-header {
                    padding: 12px 14px;
                    flex-wrap: wrap;
                    gap: 8px;
                }

                .chat-header-left {
                    flex: 1;
                }

                .chat-header-info h3 {
                    font-size: 14px;
                }

                .chat-header-actions {
                    width: 100%;
                    justify-content: flex-end;
                }

                .chat-messages {
                    padding: 12px;
                }

                .message-bubble {
                    max-width: 80%;
                    padding: 8px 12px;
                    font-size: 12px;
                }

                .chat-input-area {
                    padding: 10px 12px;
                }

                .chat-input {
                    padding: 8px 12px;
                    font-size: 14px;
                }

                .send-btn {
                    padding: 8px 14px;
                    font-size: 12px;
                }

                .send-btn span {
                    display: none;
                }
            }
        </style>
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
        <script>
            let currentConversationId = null;
            let pollInterval = null;
            let lastMessageCount = 0;
            let isFirstLoad = true;

            function selectConversation(conversationId) {
                currentConversationId = conversationId;
                isFirstLoad = true;
                lastMessageCount = 0;
                
                // Update active state
                document.querySelectorAll('.conversation-item').forEach(item => {
                    item.classList.remove('active');
                });
                event.currentTarget.classList.add('active');

                // Clear old polling
                if (pollInterval) clearInterval(pollInterval);

                // Load conversation
                loadConversation(conversationId);

                // Start polling for new messages every 2 seconds
                pollInterval = setInterval(() => {
                    pollForNewMessages(conversationId);
                }, 2000);
            }

            function loadConversation(conversationId) {
                fetch(`/admin/chatbot/conversation/${conversationId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayConversation(data.conversation, true); // Initial load
                        }
                    })
                    .catch(error => {
                        console.error('Error loading conversation:', error);
                    });
            }

            function pollForNewMessages(conversationId) {
                fetch(`/admin/chatbot/conversation/${conversationId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.conversation.messages) {
                            const messageCount = data.conversation.messages.length;
                            
                            // Only update if there are new messages
                            if (messageCount > lastMessageCount) {
                                addNewMessages(data.conversation.messages);
                                lastMessageCount = messageCount;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error polling messages:', error);
                    });
            }

            function addNewMessages(allMessages) {
                const chatMessages = document.getElementById('chatMessages');
                if (!chatMessages) return;

                // Get existing message elements
                const existingMessages = chatMessages.querySelectorAll('.message');
                const messagesToAdd = allMessages.slice(lastMessageCount);

                // Add only new messages
                messagesToAdd.forEach(msg => {
                    const messageDiv = createMessageElement(msg);
                    chatMessages.appendChild(messageDiv);
                });

                // Scroll to bottom
                setTimeout(() => {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }, 100);
            }

            function createMessageElement(msg) {
                const messageDiv = document.createElement('div');
                let messageClass = msg.sender_type;
                let label = '';
                let productPreview = '';
                
                // Map sender types correctly
                if (msg.sender_type === 'customer' || msg.sender_type === 'user') {
                    messageClass = 'customer';
                    
                    // Check for product context in metadata
                    if (msg.metadata && msg.metadata.product_context) {
                        let product = msg.metadata.product_context;
                        // Handle if product_context is a JSON string
                        if (typeof product === 'string') {
                            try {
                                product = JSON.parse(product);
                            } catch (e) {
                                console.log('Failed to parse product context:', e);
                            }
                        }
                        
                        if (product && product.name) {
                            const price = product.price ? new Intl.NumberFormat('id-ID').format(product.price) : '-';
                            const productUrl = product.id ? `/product-detail?id=${product.id}` : '#';
                            productPreview = `
                                <a href="${productUrl}" target="_blank" class="product-context-preview" title="Klik untuk lihat detail produk" onclick="event.stopPropagation(); window.open('${productUrl}', '_blank'); return false;">
                                    <div class="product-context-header">
                                        <i class="fas fa-box"></i> Produk yang ditanyakan:
                                    </div>
                                    <div class="product-context-body">
                                        <div class="product-context-name">${product.name}</div>
                                        <div class="product-context-price">Rp ${price}</div>
                                        ${product.custom_allowed ? '<span class="product-context-badge">Custom Design</span>' : ''}
                                    </div>
                                    <div class="product-context-link"><i class="fas fa-external-link-alt"></i> Lihat Detail</div>
                                </a>
                            `;
                        }
                    }
                } else if (msg.sender_type === 'admin') {
                    messageClass = 'admin';
                    label = '<div class="message-label">👤 Admin</div>';
                } else if (msg.sender_type === 'bot') {
                    messageClass = 'bot';
                    label = '<div class="message-label">🤖 Bot</div>';
                } else if (msg.sender_type === 'system') {
                    messageClass = 'system';
                }
                
                messageDiv.className = `message ${messageClass}`;
                
                const time = new Date(msg.created_at);
                const timeStr = time.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                
                messageDiv.innerHTML = `
                    <div class="message-content">
                        ${label}
                        ${productPreview}
                        <div class="message-bubble">${msg.message}</div>
                        <div class="message-time">${timeStr}</div>
                    </div>
                `;
                
                return messageDiv;
            }

            function displayConversation(conversation, isInitialLoad = false) {
                const chatMain = document.getElementById('chatMain');

                // Only rebuild if it's initial load OR if header info changed
                if (isInitialLoad || !document.getElementById('chatMessages')) {
                    // Build header
                    let headerHTML = `
                        <div class="chat-header">
                            <div class="chat-header-left">
                                <div class="conversation-avatar">${conversation.user.name.charAt(0).toUpperCase()}</div>
                                <div class="chat-header-info">
                                    <h3>${conversation.user.name}</h3>
                                    <p>${conversation.product ? conversation.product.name : conversation.subject}</p>
                                </div>
                            </div>
                            <div class="chat-header-actions">
                                ${conversation.taken_over_by_admin ? `
                                    <button class="action-btn" onclick="releaseConversation(${conversation.id})">Lepas ke Bot</button>
                                    <button class="action-btn danger" onclick="closeConversation(${conversation.id})">Tutup</button>
                                ` : `
                                    <button class="action-btn primary" onclick="takeOverConversation(${conversation.id})">Ambil Alih</button>
                                `}
                                <button class="action-btn danger" onclick="clearChatHistory(${conversation.id})" title="Hapus Riwayat Chat">
                                    <i class="fas fa-trash"></i> Hapus Riwayat
                                </button>
                            </div>
                        </div>

                        <div class="chat-messages" id="chatMessages">
                    `;

                    // Add messages
                    if (conversation.messages && conversation.messages.length > 0) {
                        conversation.messages.forEach(msg => {
                            let messageClass = msg.sender_type;
                            let label = '';
                            let productPreview = '';
                            
                            // Map sender types correctly
                            if (msg.sender_type === 'customer' || msg.sender_type === 'user') {
                                messageClass = 'customer';
                                
                                // Check for product context in metadata
                                if (msg.metadata && msg.metadata.product_context) {
                                    let product = msg.metadata.product_context;
                                    // Handle if product_context is a JSON string
                                    if (typeof product === 'string') {
                                        try {
                                            product = JSON.parse(product);
                                        } catch (e) {
                                            console.log('Failed to parse product context:', e);
                                        }
                                    }
                                    
                                    if (product && product.name) {
                                        const price = product.price ? new Intl.NumberFormat('id-ID').format(product.price) : '-';
                                        const productUrl = product.id ? `/product-detail?id=${product.id}` : '#';
                                        productPreview = `
                                            <a href="${productUrl}" target="_blank" class="product-context-preview" title="Klik untuk lihat detail produk" onclick="event.stopPropagation(); window.open('${productUrl}', '_blank'); return false;">
                                                <div class="product-context-header">
                                                    <i class="fas fa-box"></i> Produk yang ditanyakan:
                                                </div>
                                                <div class="product-context-body">
                                                    <div class="product-context-name">${product.name}</div>
                                                    <div class="product-context-price">Rp ${price}</div>
                                                    ${product.custom_allowed ? '<span class="product-context-badge">Custom Design</span>' : ''}
                                                </div>
                                                <div class="product-context-link"><i class="fas fa-external-link-alt"></i> Lihat Detail</div>
                                            </a>
                                        `;
                                    }
                                }
                            } else if (msg.sender_type === 'admin') {
                                messageClass = 'admin';
                                label = '<div class="message-label">👤 Admin</div>';
                            } else if (msg.sender_type === 'bot') {
                                messageClass = 'bot';
                                label = '<div class="message-label">🤖 Bot</div>';
                            } else if (msg.sender_type === 'system') {
                                messageClass = 'system';
                            }
                            
                            const time = new Date(msg.created_at);
                            const timeStr = time.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                            
                            headerHTML += `
                                <div class="message ${messageClass}">
                                    <div class="message-content">
                                        ${label}
                                        ${productPreview}
                                        <div class="message-bubble">${msg.message}</div>
                                        <div class="message-time">${timeStr}</div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        headerHTML += `<div style="text-align: center; color: #6c757d; padding: 40px;">Tidak ada pesan</div>`;
                    }

                    headerHTML += `
                        </div>

                        <div class="chat-input-area">
                            <div class="input-group">
                                <textarea class="chat-input" id="messageInput" placeholder="Ketik pesan Anda..." rows="2"></textarea>
                                <button class="send-btn" onclick="sendAdminMessage(${conversation.id})">
                                    <i class="fas fa-paper-plane"></i> Kirim
                                </button>
                            </div>
                        </div>
                    `;

                    chatMain.innerHTML = headerHTML;
                    lastMessageCount = conversation.messages ? conversation.messages.length : 0;

                    // Scroll to bottom
                    setTimeout(() => {
                        const chatMessages = document.getElementById('chatMessages');
                        if (chatMessages) {
                            chatMessages.scrollTop = chatMessages.scrollHeight;
                        }
                    }, 100);
                }
            }

            function takeOverConversation(conversationId) {
                fetch(`/admin/chatbot/conversation/${conversationId}/take-over`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update button states tanpa reload
                        loadConversation(conversationId);
                        alert('Anda telah mengambil alih konversasi');
                    }
                })
                .catch(error => console.error('Error:', error));
            }

            function sendAdminMessage(conversationId) {
                const messageInput = document.getElementById('messageInput');
                const message = messageInput.value.trim();

                if (!message) return;

                // Disable button
                const sendBtn = event.target;
                sendBtn.disabled = true;

                fetch(`/admin/chatbot/conversation/${conversationId}/send-message`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ message })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageInput.value = '';
                        // Don't reload entire conversation, just add the message
                        // It will be picked up by the next poll
                    } else {
                        alert('Gagal mengirim pesan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi error saat mengirim pesan');
                })
                .finally(() => {
                    sendBtn.disabled = false;
                    messageInput.focus();
                });
            }

            function releaseConversation(conversationId) {
                if (!confirm('Lepas konversasi ini kembali ke chatbot otomatis?')) return;

                fetch(`/admin/chatbot/conversation/${conversationId}/release`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadConversation(conversationId);
                    }
                })
                .catch(error => console.error('Error:', error));
            }

            function closeConversation(conversationId) {
                const notes = prompt('Catatan penyelesaian (opsional):');
                
                fetch(`/admin/chatbot/conversation/${conversationId}/close`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ resolution_notes: notes })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (pollInterval) clearInterval(pollInterval);
                        alert('Konversasi ditutup');
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
            }

            function clearChatHistory(conversationId) {
                if (!confirm('Apakah Anda yakin ingin menghapus riwayat chat ini? Tindakan ini tidak dapat dibatalkan.')) return;

                fetch(`/admin/chatbot/conversation/${conversationId}/clear-history`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Riwayat chat berhasil dihapus');
                        loadConversation(conversationId);
                    } else {
                        alert(data.message || 'Gagal menghapus riwayat chat');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus riwayat chat');
                });
            }

            function filterConversations(filter) {
                window.location.href = `?filter=${filter}`;
            }

            // Search functionality
            document.getElementById('searchInput').addEventListener('keyup', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                // TODO: Implement actual search
            });

            // Load unread count on page load
            function updateUnreadCount() {
                fetch('/admin/chatbot/api/unread-count')
                    .then(response => response.json())
                    .then(data => {
                        console.log('Unread conversations:', data);
                        // Update UI with badge counts
                    });
            }

            setInterval(updateUnreadCount, 30000); // Update every 30 seconds
            updateUnreadCount();

            // Cleanup on page unload
            window.addEventListener('beforeunload', () => {
                if (pollInterval) clearInterval(pollInterval);
            });
        </script>
    @endpush
</x-admin-layout>
