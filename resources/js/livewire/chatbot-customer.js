/**
 * Chatbot Customer JavaScript
 * Separated from chatbot-customer.blade.php for better maintainability
 * 
 * This file is loaded via Livewire @script directive in blade.
 * It handles:
 * - Auto-scroll to bottom on new messages
 * - MutationObserver for DOM changes
 */

// Auto scroll to bottom when new message arrives
document.addEventListener('livewire:initialized', () => {
    Livewire.on('messageAdded', () => {
        setTimeout(() => {
            scrollChatToBottom();
        }, 100);
    });
});

/**
 * Scroll chat messages container to bottom
 */
function scrollChatToBottom() {
    const container = document.getElementById('chatbotMessages');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

/**
 * Initialize chat scroll behavior
 */
function initChatScroll() {
    // Initial scroll
    setTimeout(scrollChatToBottom, 300);
    
    // Watch for DOM changes (new messages)
    const messagesContainer = document.getElementById('chatbotMessages');
    if (messagesContainer) {
        const observer = new MutationObserver(scrollChatToBottom);
        observer.observe(messagesContainer, { childList: true, subtree: true });
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', initChatScroll);

// Also initialize on Livewire navigate (for SPA mode)
document.addEventListener('livewire:navigated', initChatScroll);
