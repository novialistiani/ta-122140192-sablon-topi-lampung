/**
 * WhatsApp Configuration Utility
 * Centralized WhatsApp number management for the application
 */

export const waConfig = {
    // Admin WhatsApp number from environment
    adminNumber: null,
    appName: null,

    /**
     * Initialize WhatsApp config
     * Should be called once in the main application layout
     */
    init(adminNumber, appName = 'Topi Store') {
        this.adminNumber = adminNumber;
        this.appName = appName;
        return this;
    },

    /**
     * Get cleaned WhatsApp number (only digits, no + or special chars)
     * @returns {string} Cleaned phone number
     */
    getCleanNumber() {
        if (!this.adminNumber) {
            console.warn('WhatsApp admin number not initialized');
            return '62895085858888'; // Fallback
        }
        return this.adminNumber.replace(/\D/g, '');
    },

    /**
     * Get WhatsApp URL for opening chat
     * @param {string} message - Pre-filled message text
     * @returns {string} WhatsApp.me URL
     */
    getWhatsAppUrl(message = '') {
        const number = this.getCleanNumber();
        const encodedMessage = encodeURIComponent(message);
        return `https://wa.me/${number}?text=${encodedMessage}`;
    },

    /**
     * Open WhatsApp chat with message
     * @param {string} message - Message to send
     */
    openChat(message = '') {
        const url = this.getWhatsAppUrl(message);
        window.open(url, '_blank');
        console.log('Opening WhatsApp with URL:', url);
    },

    /**
     * Format phone number for display (e.g., 62895085858888 -> +62 895-0858-5888)
     * @returns {string} Formatted phone number
     */
    getFormattedNumber() {
        const cleaned = this.getCleanNumber();
        if (cleaned.startsWith('62')) {
            return `+${cleaned.slice(0, 2)} ${cleaned.slice(2, 5)}-${cleaned.slice(5, 9)}-${cleaned.slice(9)}`;
        }
        return `+${cleaned}`;
    }
};

/**
 * Global WhatsApp helper - make available in window object
 */
if (typeof window !== 'undefined') {
    window.waConfig = waConfig;
}

export default waConfig;

