<?php

return [
    /**
     * Chatbot Configuration
     */
    'chatbot' => [
        /**
         * Enable/Disable chatbot globally
         * Can be overridden per product or conversation
         */
        'enabled' => env('CHATBOT_ENABLED', true),

        /**
         * N8N Webhook URL for chatbot processing
         */
        'webhook_url' => env('N8N_WEBHOOK_URL', 'https://tokolgilampung.app.n8n.cloud/webhook/chatbot'),

        /**
         * Timeout for n8n requests (seconds)
         */
        'timeout' => env('CHATBOT_TIMEOUT', 30),

        /**
         * Escalation settings
         */
        'escalation' => [
            /**
             * Auto escalate if bot confidence is below this threshold
             */
            'confidence_threshold' => 0.7,

            /**
             * Keywords that trigger automatic escalation
             */
            'keywords' => [
                'complaint', 'problem', 'issue', 'error', 'wrong', 'broken',
                'keluhan', 'masalah', 'gangguan', 'salah', 'rusak', 'komplain',
                'refund', 'pengembalian', 'garansi', 'warranty', 'sengketa'
            ],

            /**
             * Escalate if conversation has more than N messages without resolution
             */
            'max_messages_before_escalation' => 5,

            /**
             * Escalate if unanswered for more than N minutes
             */
            'unanswered_timeout_minutes' => 10
        ],

        /**
         * Admin takeover settings
         */
        'admin_takeover' => [
            /**
             * Enable admin to take over conversations
             */
            'enabled' => true,

            /**
             * Notify customer when admin takes over
             */
            'notify_customer' => true,

            /**
             * Disable bot responses when admin is handling
             */
            'disable_bot_when_active' => true,

            /**
             * Auto-release conversation back to bot after N minutes of inactivity
             */
            'auto_release_timeout_minutes' => 30
        ],

        /**
         * Template questions to show customer
         */
        'template_questions' => [
            'Apa harga produk ini?',
            'Apakah produk ini ready stock?',
            'Tersedia warna apa saja?',
            'Berapa lama waktu pengirimannya?',
            'Apa bahan yang digunakan?',
            'Bisa request custom design?',
            'Ada diskon untuk pembelian grosir?',
            'Bagaimana cara perawatan produk?',
            'Apa ukuran yang tersedia?'
        ],

        /**
         * Conversation expiration
         */
        'expiration' => [
            /**
             * Delete conversations after N days (0 = never)
             */
            'days' => 30
        ]
    ]
];
