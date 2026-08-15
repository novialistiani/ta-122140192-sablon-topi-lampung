<?php

namespace App\Livewire;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Product;
use App\Models\Subcategory;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatbotCustomer extends Component
{
    public $message = '';
    public $messages = [];
    public $conversation;
    public $isTyping = false;
    public $quickReplies = [];
    public $productRecommendations = [];
    
    // Categories and Subcategories
    public $categories = [];
    public $subcategories = [];

    protected $listeners = ['refreshChat' => '$refresh'];

    public function mount()
    {
        // Load or create conversation
        $this->loadOrCreateConversation();
        
        // Load chat history
        $this->loadChatHistory();
        
        // Load categories
        $this->loadCategories();
        
        // Set initial quick replies
        $this->setInitialQuickReplies();
        
        // Add welcome message if new conversation
        if ($this->messages->isEmpty()) {
            $this->addWelcomeMessages();
        }
    }

    protected function loadOrCreateConversation()
    {
        $userId = Auth::id();
        
        // Find existing open conversation or create new one - use unified 'chatbot' source
        $this->conversation = ChatConversation::where('user_id', $userId)
            ->where('chat_source', 'chatbot')
            ->where('status', 'open')
            ->orderBy('updated_at', 'desc')
            ->first();
            
        if (!$this->conversation) {
            // Check for closed conversation to reopen (keep history unified)
            $closedConv = ChatConversation::where('user_id', $userId)
                ->where('chat_source', 'chatbot')
                ->where('status', 'closed')
                ->orderBy('updated_at', 'desc')
                ->first();
                
            if ($closedConv) {
                $closedConv->update([
                    'status' => 'open',
                    'expires_at' => now()->addDays(30)
                ]);
                $this->conversation = $closedConv;
            } else {
                // Only create new if absolutely none exists
                $this->conversation = ChatConversation::create([
                    'user_id' => $userId,
                    'status' => 'open',
                    'chat_source' => 'chatbot',
                    'subject' => 'Customer Chatbot',
                    'expires_at' => now()->addDays(30)
                ]);
            }
        }
    }

    protected function loadChatHistory()
    {
        // Load ALL messages from this conversation (unified)
        $this->messages = ChatMessage::where('conversation_id', $this->conversation->id)
            ->orderBy('created_at', 'asc')
            ->get();
            
        // Mark all admin/bot messages as read by user when loading chat
        ChatMessage::where('conversation_id', $this->conversation->id)
            ->whereIn('sender_type', ['admin', 'bot'])
            ->where('is_read_by_user', false)
            ->update(['is_read_by_user' => true]);
    }

    protected function loadCategories()
    {
        // Get unique categories from products
        $this->categories = Product::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->where('is_active', true)
            ->pluck('category')
            ->toArray();
            
        // Get subcategories
        $this->subcategories = Product::select('subcategory')
            ->distinct()
            ->whereNotNull('subcategory')
            ->where('is_active', true)
            ->pluck('subcategory')
            ->toArray();
    }

    protected function setInitialQuickReplies()
    {
        $this->quickReplies = [
            'Rekomendasi harga murah',
            'Lihat kategori produk',
            'Custom design',
            'Promo terbaru'
        ];
    }

    protected function addWelcomeMessages()
    {
        // Welcome message from bot
        $welcomeMessage = ChatMessage::create([
            'conversation_id' => $this->conversation->id,
            'chat_conversation_id' => $this->conversation->id,
            'sender_type' => 'bot',
            'message' => 'Hallo! Selamat datang di LGI Store! 👋 Saya adalah asisten belanja Anda. Ada yang bisa saya bantu hari ini?',
            'is_read_by_user' => true
        ]);

        // Info message
        $infoMessage = ChatMessage::create([
            'conversation_id' => $this->conversation->id,
            'chat_conversation_id' => $this->conversation->id,
            'sender_type' => 'bot',
            'message' => "Kami menyediakan berbagai produk fashion berkualitas dengan harga terjangkau:\n• Kaos polos dan bermotif\n• Jaket dan hoodie\n• Topi dan aksesoris\n• Custom design tersedia",
            'is_read_by_user' => true
        ]);

        // Reload messages
        $this->loadChatHistory();
    }

    public function sendMessage()
    {
        if (empty(trim($this->message))) {
            return;
        }

        $userMessage = trim($this->message);
        $this->message = '';

        // Save user message
        ChatMessage::create([
            'conversation_id' => $this->conversation->id,
            'chat_conversation_id' => $this->conversation->id,
            'user_id' => Auth::id(),
            'sender_type' => 'user',
            'message' => $userMessage,
            'is_read_by_admin' => false
        ]);

        // Reload messages to show user message immediately
        $this->loadChatHistory();
        
        // Clear quick replies after first message
        $this->quickReplies = [];

        // Refresh conversation to get latest taken_over status
        $this->conversation->refresh();
        
        // Only process bot response if admin hasn't taken over
        if (!$this->conversation->taken_over_by_admin) {
            // Process message and get bot response
            $this->processUserMessage($userMessage);
        } else {
            // Admin has taken over - mark conversation needs response
            $this->conversation->update(['needs_admin_response' => true]);
        }
        
        // Dispatch event to scroll to bottom
        $this->dispatch('messageAdded');
    }

    public function selectQuickReply($reply)
    {
        $this->message = $reply;
        $this->sendMessage();
    }

    protected function processUserMessage($message)
    {
        $this->isTyping = true;
        
        $lowerMessage = strtolower($message);
        $response = '';
        $this->productRecommendations = [];

        // Check for greetings first
        $greetingResponse = $this->handleGreeting($lowerMessage);
        if ($greetingResponse) {
            $response = $greetingResponse;
        }
        // Process different intents
        elseif ($this->containsAny($lowerMessage, ['harga murah', 'murah', 'termurah', 'budget', 'hemat', 'promo'])) {
            $response = $this->handleCheapPriceRecommendation();
        } elseif ($this->containsAny($lowerMessage, ['kategori', 'category', 'jenis produk', 'lihat kategori'])) {
            $response = $this->handleCategoryList();
        } elseif ($this->containsAny($lowerMessage, ['custom', 'desain', 'sablon', 'design'])) {
            $response = $this->handleCustomDesign();
        } elseif ($this->containsAny($lowerMessage, ['topi', 'cap', 'hat'])) {
            $response = $this->handleCategoryProducts('Topi');
        } elseif ($this->containsAny($lowerMessage, ['kaos', 'baju', 't-shirt', 'shirt'])) {
            $response = $this->handleCategoryProducts('Kaos');
        } elseif ($this->containsAny($lowerMessage, ['jaket', 'hoodie', 'jacket'])) {
            $response = $this->handleCategoryProducts('Jaket');
        } elseif ($this->containsAny($lowerMessage, ['subcategory', 'sub kategori', 'subkategori'])) {
            $response = $this->handleSubcategoryList();
        } elseif ($this->containsAny($lowerMessage, ['stok', 'stock', 'ready', 'tersedia'])) {
            $response = $this->handleStockQuery();
        } elseif ($this->containsAny($lowerMessage, ['kontak', 'hubungi', 'wa', 'whatsapp', 'admin'])) {
            $response = $this->handleContactInfo();
        } else {
            // Check if message contains category name
            $foundCategory = $this->findCategory($lowerMessage);
            if ($foundCategory) {
                $response = $this->handleCategoryProducts($foundCategory);
            } else {
                $response = $this->handleDefaultResponse();
            }
        }

        // Save bot response
        ChatMessage::create([
            'conversation_id' => $this->conversation->id,
            'chat_conversation_id' => $this->conversation->id,
            'sender_type' => 'bot',
            'message' => $response,
            'is_read_by_user' => true,
            'metadata' => !empty($this->productRecommendations) ? ['products' => $this->productRecommendations] : null
        ]);

        $this->isTyping = false;
        $this->loadChatHistory();
        
        // Set contextual quick replies
        $this->setContextualQuickReplies($lowerMessage);
    }

    protected function containsAny($haystack, $needles)
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }

    protected function findCategory($message)
    {
        foreach ($this->categories as $category) {
            if (str_contains($message, strtolower($category))) {
                return $category;
            }
        }
        return null;
    }

    protected function handleCheapPriceRecommendation()
    {
        // Get cheapest products from database
        $products = Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('price', 'asc')
            ->take(5)
            ->get();

        if ($products->isEmpty()) {
            return 'Maaf, saat ini tidak ada produk yang tersedia. Silakan cek kembali nanti.';
        }

        $this->productRecommendations = $products->toArray();

        $response = "🔥 Rekomendasi Produk Harga Terjangkau:\n\n";
        foreach ($products as $index => $product) {
            $price = number_format($product->price, 0, ',', '.');
            $response .= ($index + 1) . ". {$product->name}\n";
            $response .= "   💰 Rp {$price}\n";
            $response .= "   📦 Stok: {$product->stock}\n\n";
        }
        $response .= "Klik nama produk untuk melihat detail lengkap!";

        return $response;
    }

    protected function handleCategoryList()
    {
        if (empty($this->categories)) {
            return 'Maaf, kategori produk belum tersedia.';
        }

        $response = "📂 Kategori Produk yang Tersedia:\n\n";
        foreach ($this->categories as $index => $category) {
            $count = Product::where('category', $category)
                ->where('is_active', true)
                ->count();
            $response .= "• {$category} ({$count} produk)\n";
        }
        $response .= "\nKetik nama kategori untuk melihat produk, misalnya: 'Topi' atau 'Kaos'";

        return $response;
    }

    protected function handleSubcategoryList()
    {
        if (empty($this->subcategories)) {
            return 'Maaf, sub-kategori produk belum tersedia.';
        }

        $response = "📁 Sub-Kategori Produk:\n\n";
        foreach ($this->subcategories as $subcategory) {
            $count = Product::where('subcategory', $subcategory)
                ->where('is_active', true)
                ->count();
            $response .= "• {$subcategory} ({$count} produk)\n";
        }
        $response .= "\nKetik nama sub-kategori untuk melihat produk.";

        return $response;
    }

    protected function handleCategoryProducts($category)
    {
        $products = Product::where('category', 'like', "%{$category}%")
            ->where('is_active', true)
            ->orderBy('sales', 'desc')
            ->take(5)
            ->get();

        if ($products->isEmpty()) {
            return "Maaf, saat ini tidak ada produk di kategori '{$category}'. Silakan coba kategori lain.";
        }

        $this->productRecommendations = $products->toArray();

        $response = "🏷️ Produk Kategori {$category}:\n\n";
        foreach ($products as $index => $product) {
            $price = number_format($product->price, 0, ',', '.');
            $response .= ($index + 1) . ". {$product->name}\n";
            $response .= "   💰 Rp {$price}\n";
            if ($product->stock > 0) {
                $response .= "   ✅ Ready Stock\n\n";
            } else {
                $response .= "   ⚠️ Stok Habis\n\n";
            }
        }

        return $response;
    }

    protected function handleCustomDesign()
    {
        // Get products that allow custom design
        $products = Product::where('custom_design_allowed', true)
            ->where('is_active', true)
            ->orderBy('price', 'asc')
            ->take(5)
            ->get();

        $response = "🎨 Custom Design adalah spesialisasi kami!\n\n";
        $response .= "Anda bisa upload gambar/logo Anda sendiri dan kami akan sablon di:\n";
        $response .= "• Kaos\n• Jaket\n• Topi\n• Tas\n\n";

        if ($products->isNotEmpty()) {
            $this->productRecommendations = $products->toArray();
            
            $response .= "📋 Produk yang Support Custom Design:\n\n";
            foreach ($products as $index => $product) {
                $price = number_format($product->price, 0, ',', '.');
                $response .= ($index + 1) . ". {$product->name} - Rp {$price}\n";
            }
            $response .= "\n";
        }

        $response .= "💡 Cara Order Custom Design:\n";
        $response .= "1. Pilih produk dasar\n";
        $response .= "2. Upload desain Anda\n";
        $response .= "3. Pilih area sablon\n";
        $response .= "4. Tentukan jumlah\n\n";
        $response .= "Mulai dari Rp 75.000,- per item tergantung kompleksitas desain.";

        return $response;
    }

    protected function handleStockQuery()
    {
        $products = Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('stock', 'desc')
            ->take(5)
            ->get();

        if ($products->isEmpty()) {
            return 'Maaf, semua produk sedang habis. Silakan cek kembali nanti atau hubungi admin.';
        }

        $this->productRecommendations = $products->toArray();

        $response = "📦 Produk dengan Stok Tersedia:\n\n";
        foreach ($products as $index => $product) {
            $price = number_format($product->price, 0, ',', '.');
            $response .= ($index + 1) . ". {$product->name}\n";
            $response .= "   💰 Rp {$price}\n";
            $response .= "   📦 Stok: {$product->stock} pcs\n\n";
        }

        return $response;
    }

    protected function handleContactInfo()
    {
        return "📞 Kontak LGI Store:\n\n" .
            "📱 WhatsApp: 0821-7839-6916\n" .
            "📧 Email: noreply@lgistore.com\n\n" .
            "Kami siap membantu Anda 24/7! 😊\n\n" .
            "Atau Anda bisa langsung klik tombol WhatsApp di halaman produk untuk konsultasi.";
    }

    protected function handleGreeting($message)
    {
        $userName = Auth::user()->name ?? 'Kak';
        $currentHour = (int) date('H');
        
        // Detect time-based greetings and respond appropriately
        $greetings = [
            // Morning greetings (00:00 - 11:59)
            'selamat pagi' => "Selamat pagi juga, {$userName}! 🌅\n\nSemoga harimu menyenangkan. Ada yang bisa saya bantu hari ini?",
            'pagi' => "Pagi juga, {$userName}! ☀️\n\nApa yang bisa saya bantu pagi ini?",
            'met pagi' => "Met pagi juga, {$userName}! 🌄\n\nAda yang bisa saya bantu?",
            'morning' => "Good morning, {$userName}! 🌅\n\nHow can I help you today?",
            
            // Afternoon greetings (12:00 - 14:59)
            'selamat siang' => "Selamat siang juga, {$userName}! ☀️\n\nSudah makan siang belum? Ada yang bisa saya bantu?",
            'siang' => "Siang juga, {$userName}! 🌞\n\nAda yang bisa saya bantu siang ini?",
            'met siang' => "Met siang juga, {$userName}! ☀️\n\nApa yang bisa saya bantu?",
            
            // Late afternoon greetings (15:00 - 17:59)
            'selamat sore' => "Selamat sore juga, {$userName}! 🌤️\n\nSemoga sorenya menyenangkan. Ada yang bisa saya bantu?",
            'sore' => "Sore juga, {$userName}! 🌅\n\nAda yang bisa saya bantu sore ini?",
            'met sore' => "Met sore juga, {$userName}! 🌤️\n\nApa yang bisa dibantu?",
            
            // Evening/Night greetings (18:00 - 23:59)
            'selamat malam' => "Selamat malam juga, {$userName}! 🌙\n\nTerima kasih sudah mampir. Ada yang bisa saya bantu malam ini?",
            'malam' => "Malam juga, {$userName}! 🌃\n\nAda yang bisa saya bantu?",
            'met malam' => "Met malam juga, {$userName}! 🌙\n\nApa yang bisa dibantu?",
            'evening' => "Good evening, {$userName}! 🌆\n\nWhat can I help you with?",
            'night' => "Good night, {$userName}! 🌙\n\nHow can I assist you?",
            
            // General greetings (any time)
            'hallo' => "Hallo juga, {$userName}! 👋\n\nSenang bisa membantu! Ada yang bisa saya bantu?",
            'halo' => "Halo juga, {$userName}! 👋\n\nAda yang bisa saya bantu hari ini?",
            'hai' => "Hai juga, {$userName}! 😊\n\nApa kabar? Ada yang bisa dibantu?",
            'hi' => "Hi juga, {$userName}! 👋\n\nAda yang bisa saya bantu?",
            'hello' => "Hello, {$userName}! 👋\n\nHow can I help you today?",
            'hey' => "Hey, {$userName}! 👋\n\nAda yang bisa saya bantu?",
            'assalamualaikum' => "Wa'alaikumsalam, {$userName}! 🙏\n\nAda yang bisa saya bantu hari ini?",
            'assalamu\'alaikum' => "Wa'alaikumsalam, {$userName}! 🙏\n\nAda yang bisa saya bantu?",
            'aslkm' => "Waalaikumsalam, {$userName}! 🙏\n\nApa yang bisa dibantu?",
            'waalaikumsalam' => "Terima kasih! Ada yang bisa saya bantu, {$userName}? 😊",
            'permisi' => "Silakan, {$userName}! 😊\n\nAda yang bisa saya bantu?",
            'punten' => "Mangga, {$userName}! 😊\n\nAda yang bisa dibantu?",
            'apa kabar' => "Alhamdulillah baik, {$userName}! Terima kasih sudah bertanya 😊\n\nAda yang bisa saya bantu?",
            'kabar baik' => "Syukurlah! 😊 Ada yang bisa saya bantu, {$userName}?",
            
            // Thanks & pleasantries
            'terima kasih' => "Sama-sama, {$userName}! 😊\n\nSenang bisa membantu. Ada lagi yang bisa dibantu?",
            'makasih' => "Sama-sama, {$userName}! 🙏\n\nAda lagi yang bisa saya bantu?",
            'thanks' => "You're welcome, {$userName}! 😊\n\nAnything else I can help with?",
            'thank you' => "You're welcome! 😊\n\nIs there anything else I can help you with?",
        ];

        // Check each greeting pattern
        foreach ($greetings as $pattern => $response) {
            if (str_contains($message, $pattern)) {
                return $response;
            }
        }

        // Check for generic "selamat" and respond based on current time
        if (str_contains($message, 'selamat') || preg_match('/^(met|good)\s/i', $message)) {
            return $this->getTimeBasedGreeting($userName, $currentHour);
        }

        return null; // No greeting detected
    }

    protected function getTimeBasedGreeting($userName, $hour)
    {
        if ($hour >= 0 && $hour < 12) {
            return "Selamat pagi juga, {$userName}! 🌅\n\nAda yang bisa saya bantu pagi ini?";
        } elseif ($hour >= 12 && $hour < 15) {
            return "Selamat siang juga, {$userName}! ☀️\n\nAda yang bisa saya bantu?";
        } elseif ($hour >= 15 && $hour < 18) {
            return "Selamat sore juga, {$userName}! 🌤️\n\nAda yang bisa saya bantu?";
        } else {
            return "Selamat malam juga, {$userName}! 🌙\n\nAda yang bisa saya bantu malam ini?";
        }
    }

    protected function handleDefaultResponse()
    {
        $responses = [
            "Terima kasih atas pertanyaannya! Ada yang bisa saya bantu lainnya?\n\nAnda bisa bertanya tentang:\n• Rekomendasi harga murah\n• Kategori produk\n• Custom design\n• Stok produk",
            "Saya siap membantu Anda menemukan produk yang tepat! Coba tanyakan tentang kategori produk atau rekomendasi harga.",
            "Silakan beri tahu saya lebih detail tentang yang Anda cari. Misalnya: 'kaos murah' atau 'topi custom'",
        ];

        return $responses[array_rand($responses)];
    }

    protected function setContextualQuickReplies($lastMessage)
    {
        if ($this->containsAny($lastMessage, ['kategori', 'category'])) {
            $this->quickReplies = array_slice($this->categories, 0, 4);
        } elseif ($this->containsAny($lastMessage, ['harga', 'murah', 'budget'])) {
            $this->quickReplies = ['Lihat kategori produk', 'Custom design', 'Produk ready stock'];
        } elseif ($this->containsAny($lastMessage, ['custom', 'desain'])) {
            $this->quickReplies = ['Lihat harga custom', 'Hubungi admin', 'Lihat contoh desain'];
        } else {
            $this->quickReplies = ['Rekomendasi harga murah', 'Lihat kategori', 'Custom design', 'Hubungi admin'];
        }
    }

    public function clearHistory()
    {
        // Delete all messages in this conversation
        ChatMessage::where('conversation_id', $this->conversation->id)->delete();
        
        // Create new welcome messages
        $this->addWelcomeMessages();
        
        // Reset quick replies
        $this->setInitialQuickReplies();
    }

    public function getProductUrl($productId)
    {
        $product = Product::find($productId);
        if ($product) {
            return route('product.detail', ['id' => $product->id]);
        }
        return '#';
    }

    public function render()
    {
        return view('livewire.chatbot-customer');
    }
}
