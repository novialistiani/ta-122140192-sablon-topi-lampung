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

    <script>
        // Tinggi kotak chat = tinggi layar yang terlihat dikurangi posisi atasnya
        function fitChatpage() {
            const c = document.querySelector('.chatpage-container');
            if (!c) return;
            const top = c.getBoundingClientRect().top;
            const vh = window.visualViewport ? window.visualViewport.height : window.innerHeight;
            const h = Math.max(300, vh - top);
            c.style.setProperty('height', h + 'px', 'important');
            c.style.setProperty('max-height', h + 'px', 'important');
        }
        window.addEventListener('load', fitChatpage);
        window.addEventListener('resize', fitChatpage);
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', fitChatpage);
        }
        fitChatpage();
    </script>
</x-customer-layout>