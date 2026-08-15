/**
 * Admin Chatbot Management JavaScript
 * File: resources/js/admin/chatbot-management.js
 */

// Global state
let currentConversationId = null;
let pollInterval = null;
let lastMessageCount = 0;
let isFirstLoad = true;

/**
 * Select and load a conversation
 */
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

/**
 * Load conversation data from server
 */
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

/**
 * Poll for new messages
 */
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

/**
 * Add new messages to chat
 */
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

/**
 * Create a message element
 */
function createMessageElement(msg) {
    const messageDiv = document.createElement('div');
    let messageClass = msg.sender_type;
    let label = '';
    let productPreview = '';
    let messageContent = msg.message;
    
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
        // Format bot message for better display - pass metadata for product links
        messageContent = formatBotMessage(msg.message, msg.metadata);
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
            <div class="message-bubble">${messageContent}</div>
            <div class="message-time">${timeStr}</div>
        </div>
    `;
    
    return messageDiv;
}

/**
 * Format bot message to be more readable
 * @param {string} message - The bot message text
 * @param {object} metadata - Optional metadata containing product info
 */
function formatBotMessage(message, metadata = null) {
    if (!message) return '';
    
    // Check if we have product metadata from the message
    let productsFromMetadata = [];
    if (metadata && metadata.products && Array.isArray(metadata.products)) {
        productsFromMetadata = metadata.products;
    }
    
    // Check if message contains product recommendations pattern
    // Pattern: numbered list with price and stock
    const productPattern = /(\d+)\.\s*([^💰]+)\s*💰\s*Rp\s*([\d.,]+)\s*📦\s*Stok:\s*(\d+)/g;
    
    let matches = [];
    let match;
    
    while ((match = productPattern.exec(message)) !== null) {
        const cleanName = match[2].replace(/[🔥💰📦📋🏷️✅⚠️]/g, '').trim();
        
        // Try to find product ID from metadata
        let productId = null;
        if (productsFromMetadata.length > 0) {
            const foundProduct = productsFromMetadata.find(p => 
                p.name && p.name.toLowerCase().trim() === cleanName.toLowerCase().trim()
            );
            if (foundProduct) {
                productId = foundProduct.id;
            }
        }
        
        matches.push({
            number: match[1],
            name: cleanName,
            price: match[3],
            stock: match[4],
            id: productId
        });
    }
    
    // If we found product recommendations, format them nicely
    if (matches.length > 0) {
        // Extract the header/intro text (before the first product)
        let headerMatch = message.match(/^[🔥📋🏷️]*\s*([^:]+):/);
        let header = headerMatch ? headerMatch[1].trim() : 'Rekomendasi Produk';
        
        // Clean header from emojis
        header = header.replace(/[🔥📋🏷️]/g, '').trim();
        
        // Extract footer text (after last stock info, before any remaining text)
        let lastStockIndex = message.lastIndexOf('Stok:');
        let afterStock = message.substring(lastStockIndex);
        let footerMatch = afterStock.match(/Stok:\s*\d+\s*(.+?)$/s);
        let footer = '';
        if (footerMatch && footerMatch[1]) {
            footer = footerMatch[1].replace(/[🔥💰📦📋🏷️✅⚠️]/g, '').trim();
        }
        
        let formattedHtml = `<div class="bot-msg-header">📋 ${header}</div>`;
        formattedHtml += '<div class="bot-product-list">';
        
        matches.forEach(product => {
            // Build product URL - use ID if available, otherwise search
            let productUrl;
            if (product.id) {
                productUrl = `/product-detail?id=${product.id}`;
            } else {
                productUrl = `/catalog?search=${encodeURIComponent(product.name)}`;
            }
            
            formattedHtml += `
                <a href="${productUrl}" target="_blank" class="bot-product-item" title="Klik untuk lihat detail ${product.name}">
                    <span class="bot-product-number">${product.number}</span>
                    <div class="bot-product-info">
                        <span class="bot-product-name">${product.name}</span>
                        <span class="bot-product-price">Rp ${product.price}</span>
                    </div>
                    <span class="bot-product-stock">${product.stock}</span>
                </a>
            `;
        });
        
        formattedHtml += '</div>';
        
        if (footer) {
            formattedHtml += `<div class="bot-msg-footer">💡 ${footer}</div>`;
        }
        
        return formattedHtml;
    }
    
    // If no product list, just clean up and format normally
    return message
        .replace(/\n/g, '<br>')
        .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
}

/**
 * Build product preview HTML
 */
function buildProductPreviewHtml(product) {
    if (!product || !product.name) return '';
    
    const price = product.price ? new Intl.NumberFormat('id-ID').format(product.price) : '-';
    const productUrl = product.id ? `/product-detail?id=${product.id}` : '#';
    
    return `
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

/**
 * Display conversation in chat main area
 */
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
                let messageContent = msg.message;
                
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
                        productPreview = buildProductPreviewHtml(product);
                    }
                } else if (msg.sender_type === 'admin') {
                    messageClass = 'admin';
                    label = '<div class="message-label">👤 Admin</div>';
                } else if (msg.sender_type === 'bot') {
                    messageClass = 'bot';
                    label = '<div class="message-label">🤖 Bot</div>';
                    // Format bot message for better display - pass metadata for product links
                    messageContent = formatBotMessage(msg.message, msg.metadata);
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
                            <div class="message-bubble">${messageContent}</div>
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
                    <input type="text" class="chat-input" id="messageInput" placeholder="Ketik pesan Anda..." autocomplete="off">
                    <button class="send-btn" id="sendBtn" onclick="sendAdminMessage(${conversation.id})">
                        <i class="fas fa-paper-plane"></i> <span>Kirim</span>
                    </button>
                </div>
            </div>
        `;

        chatMain.innerHTML = headerHTML;
        lastMessageCount = conversation.messages ? conversation.messages.length : 0;

        // Setup Enter key listener for message input
        setupMessageInputListener(conversation.id);

        // Scroll to bottom
        setTimeout(() => {
            const chatMessages = document.getElementById('chatMessages');
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }, 100);
    }
}

/**
 * Take over conversation from bot
 */
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

/**
 * Setup message input listener for Enter key
 */
function setupMessageInputListener(conversationId) {
    const messageInput = document.getElementById('messageInput');
    if (!messageInput) return;

    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendAdminMessage(conversationId);
        }
    });

    // Focus input automatically
    messageInput.focus();
}

/**
 * Send admin message
 */
function sendAdminMessage(conversationId) {
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    const message = messageInput.value.trim();

    if (!message) return;

    // Disable button and input
    if (sendBtn) sendBtn.disabled = true;
    messageInput.disabled = true;

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
        if (sendBtn) sendBtn.disabled = false;
        messageInput.disabled = false;
        messageInput.focus();
    });
}

/**
 * Release conversation back to bot
 */
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

/**
 * Close conversation
 */
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

/**
 * Clear chat history
 */
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

/**
 * Filter conversations
 */
function filterConversations(filter) {
    window.location.href = `?filter=${filter}`;
}

/**
 * Update unread count
 */
function updateUnreadCount() {
    fetch('/admin/chatbot/api/unread-count')
        .then(response => response.json())
        .then(data => {
            console.log('Unread conversations:', data);
            // Update UI with badge counts
        });
}

/**
 * Initialize chatbot management
 */
function initChatbotManagement() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            // TODO: Implement actual search
        });
    }

    // Load unread count on page load
    setInterval(updateUnreadCount, 30000); // Update every 30 seconds
    updateUnreadCount();

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (pollInterval) clearInterval(pollInterval);
    });
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initChatbotManagement);

// Expose functions to global scope for onclick handlers
window.selectConversation = selectConversation;
window.filterConversations = filterConversations;
window.sendAdminMessage = sendAdminMessage;
window.takeOverConversation = takeOverConversation;
window.releaseConversation = releaseConversation;
window.closeConversation = closeConversation;
window.clearChatHistory = clearChatHistory;
window.openProductDetail = openProductDetail;
