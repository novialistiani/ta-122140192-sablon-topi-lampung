<x-customer-layout title="Chatbot" active="chatpage">
    @push('styles')
        @vite(['resources/css/guest/chatpage.css', 'resources/css/livewire/chatbot-customer.css'])
        <style>
            /* Force hide all floating chat elements on chatpage */
            .unified-chatbot-trigger,
            .unified-chatbot-popup,
            .chatbot-trigger,
            .chatbot-popup,
            .floating-chat-btn,
            #chatbotTrigger,
            #chatbotPopup {
                display: none !important;
                visibility: hidden !important;
            }
            /* Fix container to take full height */
            .flex-1.overflow-auto.p-2 {
                padding: 0 !important;
                overflow: hidden !important;
            }
        </style>
    @endpush

    <div class="chatpage-container">
        <div class="chatpage-chat">
            @livewire('chatbot-customer')
        </div>
    </div>
</x-customer-layout>
