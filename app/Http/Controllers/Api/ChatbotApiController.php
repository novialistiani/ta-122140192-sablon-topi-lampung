<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatbotApiController extends Controller
{
    /**
     * Get or create unified conversation for current user
     * One user = one active conversation for all chatbot sources
     * If multiple exist, reuses the most recent one
     */
    protected function getOrCreateUnifiedConversation($userId)
    {
        if (!$userId) return null;
        
        // Find existing OPEN conversation for this user with 'chatbot' source
        $conversation = ChatConversation::where('user_id', $userId)
            ->where('chat_source', 'chatbot')
            ->where('status', 'open')
            ->orderBy('updated_at', 'desc')
            ->first();
            
        if (!$conversation) {
            // Check if there's a closed chatbot conversation we can reopen
            $closedConversation = ChatConversation::where('user_id', $userId)
                ->where('chat_source', 'chatbot')
                ->where('status', 'closed')
                ->orderBy('updated_at', 'desc')
                ->first();
            
            if ($closedConversation) {
                // Reopen existing conversation to keep history unified
                $closedConversation->update([
                    'status' => 'open',
                    'expires_at' => now()->addDays(30)
                ]);
                $conversation = $closedConversation;
            } else {
                // Create new conversation only if none exists
                $conversation = ChatConversation::create([
                    'user_id' => $userId,
                    'status' => 'open',
                    'chat_source' => 'chatbot',
                    'subject' => 'Customer Chatbot',
                    'expires_at' => now()->addDays(30)
                ]);
            }
        }
        
        return $conversation;
    }

    /**
     * Get chat history for current user
     * Gets ALL messages from ALL user's conversations (unified view)
     */
    public function getHistory(Request $request)
    {
        try {
            $userId = Auth::id();
            
            if (!$userId) {
                return response()->json([
                    'success' => true,
                    'messages' => [],
                    'conversation_id' => null
                ]);
            }
            
            $conversation = $this->getOrCreateUnifiedConversation($userId);
            
            if (!$conversation) {
                return response()->json([
                    'success' => true,
                    'messages' => [],
                    'conversation_id' => null
                ]);
            }
            
            // Get ALL conversation IDs for this user
            $userConversationIds = ChatConversation::where('user_id', $userId)->pluck('id');
            
            // Get ALL messages from ALL user's conversations
            $messages = ChatMessage::whereIn('conversation_id', $userConversationIds)
                ->orderBy('created_at', 'asc')
                ->get(['id', 'message', 'sender_type', 'created_at', 'metadata']);
            
            return response()->json([
                'success' => true,
                'messages' => $messages,
                'conversation_id' => $conversation->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Chatbot getHistory error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to load chat history'
            ], 500);
        }
    }
    
    /**
     * Send message and get bot response
     * Bot will NOT respond if admin has taken over the conversation
     */
    public function sendMessage(Request $request)
    {
        try {
            Log::info('Chatbot sendMessage called', [
                'message' => $request->input('message'),
                'product_context' => $request->input('product_context'),
                'user_id' => Auth::id()
            ]);
            
            $request->validate([
                'message' => 'required|string|max:500'
            ]);
            
            $userId = Auth::id();
            $userMessage = $request->input('message');
            $productContext = $request->input('product_context'); // Optional product info
            
            if (!$userId) {
                // For guest users, just return bot response without saving
                $botResponse = $this->generateBotResponse($userMessage, $productContext);
                return response()->json([
                    'success' => true,
                    'bot_response' => $botResponse,
                    'conversation_id' => null,
                    'admin_handling' => false
                ]);
            }
            
            // Get or create unified conversation
            $conversation = $this->getOrCreateUnifiedConversation($userId);
            Log::info('Using conversation', ['conversation_id' => $conversation->id, 'status' => $conversation->status]);
            
            // Build metadata for user message
            $userMetadata = null;
            if ($productContext) {
                $userMetadata = ['product_context' => $productContext];
            }
            
            // Save user message
            $savedMessage = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'chat_conversation_id' => $conversation->id,
                'user_id' => $userId,
                'sender_type' => 'user',
                'message' => $userMessage,
                'metadata' => $userMetadata,
                'is_read_by_admin' => false
            ]);
            Log::info('User message saved', ['message_id' => $savedMessage->id]);
            
            // Check if admin has taken over this conversation
            // If admin is handling, DON'T send bot response - wait for admin
            if ($conversation->taken_over_by_admin) {
                // Update conversation to mark customer needs response
                $conversation->update([
                    'needs_admin_response' => true
                ]);
                
                $conversation->touch();
                
                return response()->json([
                    'success' => true,
                    'bot_response' => null, // No bot response - admin will reply
                    'conversation_id' => $conversation->id,
                    'admin_handling' => true,
                    'message' => 'Pesan Anda telah terkirim. Admin akan segera membalas.'
                ]);
            }
            
            // Bot responds only if admin hasn't taken over
            $botResponseData = $this->generateBotResponse($userMessage, $productContext);
            
            // Handle both old string format and new array format
            $botResponse = is_array($botResponseData) ? $botResponseData['message'] : $botResponseData;
            $products = is_array($botResponseData) ? ($botResponseData['products'] ?? []) : [];
            
            // Save bot response with products metadata
            $botMetadata = !empty($products) ? ['products' => $products] : null;
            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'chat_conversation_id' => $conversation->id,
                'sender_type' => 'bot',
                'message' => $botResponse,
                'metadata' => $botMetadata,
                'is_read_by_user' => true
            ]);
            
            // Update conversation timestamp
            $conversation->touch();
            
            return response()->json([
                'success' => true,
                'bot_response' => $botResponse,
                'products' => $products,
                'conversation_id' => $conversation->id,
                'admin_handling' => false
            ]);
            
        } catch (\Exception $e) {
            Log::error('Chatbot sendMessage error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to send message'
            ], 500);
        }
    }
    
    /**
     * Generate bot response based on user message
     * Fetches real product data from database when product context is provided
     */
    protected function generateBotResponse(string $userMessage, $productContext = null): string
    {
        $msg = strtolower($userMessage);
        
        // If there's product context with product_id, fetch real data from database
        if ($productContext) {
            // Parse product context if it's a string
            if (is_string($productContext)) {
                $productContext = json_decode($productContext, true);
            }
            
            // Get product_id from context
            $productId = $productContext['id'] ?? $productContext['product_id'] ?? null;
            
            if ($productId) {
                // Fetch real product data from database
                // Note: 'category' is a string field, not a relationship
                $product = Product::with(['variants'])->find($productId);
                
                if ($product) {
                    return $this->generateProductSpecificResponse($msg, $product);
                }
            }
            
            // Fallback to context data if product not found in DB
            if (isset($productContext['name'])) {
                return $this->generateContextBasedResponse($msg, $productContext);
            }
        }
        
        // General responses when no product context
        return $this->generateGeneralResponse($msg);
    }
    
    /**
     * Generate response based on real product data from database
     */
    protected function generateProductSpecificResponse(string $msg, Product $product): string
    {
        $productName = $product->name;
        $formattedPrice = number_format((float)($product->price ?? 0), 0, ',', '.');
        
        // Check for price-related questions
        if (str_contains($msg, 'harga') || str_contains($msg, 'berapa') || str_contains($msg, 'price')) {
            $response = "💰 *Harga {$productName}*\n\n";
            $response .= "Harga: *Rp {$formattedPrice}*\n\n";
            
            // Add minimum order info if exists
            if ($product->min_order && $product->min_order > 1) {
                $response .= "📦 Minimal order: {$product->min_order} pcs\n\n";
            }
            
            $response .= "Untuk pemesanan, silakan tambahkan ke keranjang atau hubungi admin kami.";
            return $response;
        }
        
        // Check for stock-related questions
        if (str_contains($msg, 'stok') || str_contains($msg, 'tersedia') || str_contains($msg, 'ada') || str_contains($msg, 'stock')) {
            $response = "📦 *Ketersediaan {$productName}*\n\n";
            
            if ($product->stock !== null) {
                if ($product->stock > 0) {
                    $response .= "✅ Stok tersedia: *{$product->stock} pcs*\n";
                } else {
                    $response .= "❌ Maaf, stok sedang habis.\n";
                }
            } else {
                $response .= "✅ Produk tersedia (Made to order)\n";
            }
            
            // Check variant stock if exists
            if ($product->variants && $product->variants->count() > 0) {
                $response .= "\n*Stok per varian:*\n";
                foreach ($product->variants->take(5) as $variant) {
                    $variantStock = $variant->stock ?? 'Tersedia';
                    $variantInfo = [];
                    if ($variant->color) $variantInfo[] = $variant->color;
                    if ($variant->size) $variantInfo[] = $variant->size;
                    $variantLabel = implode(' - ', $variantInfo) ?: 'Default';
                    $response .= "• {$variantLabel}: {$variantStock}\n";
                }
                if ($product->variants->count() > 5) {
                    $response .= "• ... dan lainnya\n";
                }
            }
            
            return $response;
        }
        
        // Check for custom design questions
        if (str_contains($msg, 'custom') || str_contains($msg, 'desain') || str_contains($msg, 'design')) {
            if ($product->custom_design_allowed) {
                return "🎨 *Custom Design untuk {$productName}*\n\n✅ Ya! Produk ini mendukung custom design.\n\nCara order custom:\n1. Tambahkan produk ke keranjang\n2. Upload desain Anda saat checkout\n3. Tim kami akan review dalam 1x24 jam\n\nFormat desain yang diterima: PNG, JPG, AI, PSD";
            } else {
                return "❌ Maaf, *{$productName}* tidak mendukung custom design.\n\nNamun produk ini tersedia dalam berbagai pilihan warna standar yang menarik! Cek halaman detail produk untuk melihat pilihan warna.";
            }
        }
        
        // Check for color/variant questions
        if (str_contains($msg, 'warna') || str_contains($msg, 'color') || str_contains($msg, 'pilihan')) {
            $response = "🎨 *Pilihan Warna {$productName}*\n\n";
            
            if ($product->variants && $product->variants->count() > 0) {
                $colors = $product->variants->pluck('color')->filter()->unique()->values();
                if ($colors->count() > 0) {
                    $response .= "Warna tersedia:\n";
                    foreach ($colors as $color) {
                        $response .= "• {$color}\n";
                    }
                } else {
                    $response .= "Silakan cek halaman detail produk untuk melihat pilihan warna.";
                }
            } else {
                $response .= "Silakan cek halaman detail produk untuk melihat pilihan warna yang tersedia.";
            }
            
            return $response;
        }
        
        // Check for size questions
        if (str_contains($msg, 'ukuran') || str_contains($msg, 'size')) {
            $response = "📏 *Ukuran {$productName}*\n\n";
            
            if ($product->variants && $product->variants->count() > 0) {
                $sizes = $product->variants->pluck('size')->filter()->unique()->values();
                if ($sizes->count() > 0) {
                    $response .= "Ukuran tersedia:\n";
                    foreach ($sizes as $size) {
                        $response .= "• {$size}\n";
                    }
                } else {
                    $response .= "Silakan cek halaman detail produk untuk melihat pilihan ukuran.";
                }
            } else {
                $response .= "Silakan cek halaman detail produk untuk melihat ukuran yang tersedia.";
            }
            
            return $response;
        }
        
        // Check for material/bahan questions
        if (str_contains($msg, 'bahan') || str_contains($msg, 'material') || str_contains($msg, 'kualitas')) {
            $response = "🧵 *Detail {$productName}*\n\n";
            
            if ($product->description) {
                // Extract first 200 chars of description
                $desc = strip_tags($product->description);
                $desc = strlen($desc) > 200 ? substr($desc, 0, 200) . '...' : $desc;
                $response .= "{$desc}\n\n";
            }
            
            $response .= "Untuk informasi lebih lengkap, silakan kunjungi halaman detail produk.";
            return $response;
        }
        
        // Check for shipping questions
        if (str_contains($msg, 'kirim') || str_contains($msg, 'pengiriman') || str_contains($msg, 'ongkir')) {
            $response = "📦 *Info Pengiriman {$productName}*\n\n";
            $response .= "• Estimasi Jawa: 2-4 hari kerja\n";
            $response .= "• Estimasi Luar Jawa: 3-7 hari kerja\n";
            $response .= "• Pengiriman via JNE, J&T, SiCepat\n\n";
            
            if ($product->weight) {
                $response .= "Berat produk: {$product->weight} gram\n";
            }
            
            $response .= "\nOngkos kirim dihitung saat checkout berdasarkan lokasi Anda.";
            return $response;
        }
        
        // Default product info response
        $response = "📦 *Info Produk: {$productName}*\n\n";
        $response .= "💰 Harga: Rp {$formattedPrice}\n";
        
        if ($product->stock !== null && $product->stock > 0) {
            $response .= "📦 Stok: {$product->stock} pcs\n";
        }
        
        if ($product->custom_design_allowed) {
            $response .= "🎨 Custom Design: ✅ Tersedia\n";
        }
        
        // category is a string field, not a relationship
        if ($product->getAttributes()['category']) {
            $response .= "📂 Kategori: {$product->getAttributes()['category']}\n";
        }
        
        $response .= "\nApa yang ingin Anda ketahui tentang produk ini?\n";
        $response .= "• Stok & ketersediaan\n";
        $response .= "• Pilihan warna/ukuran\n";
        $response .= "• Custom design\n";
        $response .= "• Pengiriman";
        
        return $response;
    }
    
    /**
     * Fallback response using context data when product not in DB
     */
    protected function generateContextBasedResponse(string $msg, array $productContext): string
    {
        $productName = $productContext['name'];
        $productPrice = $productContext['price'] ?? 0;
        $customAllowed = $productContext['custom_allowed'] ?? false;
        
        if (str_contains($msg, 'harga') || str_contains($msg, 'berapa')) {
            $formattedPrice = number_format($productPrice, 0, ',', '.');
            return "💰 *Harga {$productName}*\n\nHarga: *Rp {$formattedPrice}*\n\nUntuk info lebih lanjut atau pemesanan, silakan hubungi admin kami.";
        }
        
        if (str_contains($msg, 'stok') || str_contains($msg, 'tersedia')) {
            return "📦 *Ketersediaan {$productName}*\n\nProduk ini tersedia dan siap dipesan. Untuk memastikan stok terkini, silakan cek halaman detail produk.";
        }
        
        if (str_contains($msg, 'custom') || str_contains($msg, 'desain')) {
            if ($customAllowed) {
                return "🎨 *Custom Design untuk {$productName}*\n\nYa! Produk ini mendukung custom design. Anda bisa mengunggah desain Anda sendiri saat checkout.";
            } else {
                return "❌ Maaf, {$productName} tidak mendukung custom design. Namun tersedia dalam berbagai pilihan warna standar yang menarik!";
            }
        }
        
        // Default
        $formattedPrice = number_format($productPrice, 0, ',', '.');
        return "📦 *{$productName}*\n\n💰 Harga: Rp {$formattedPrice}\n\nApa yang ingin Anda ketahui tentang produk ini? Tanyakan tentang stok, warna, ukuran, atau custom design.";
    }
    
    /**
     * Generate general response when no product context
     * Returns array with 'message' and optional 'products' for product cards
     */
    protected function generateGeneralResponse(string $msg): array|string
    {
        // Check for price-related questions
        if (str_contains($msg, 'harga') || str_contains($msg, 'berapa') || str_contains($msg, 'price') || str_contains($msg, 'murah') || str_contains($msg, 'rekomendasi')) {
            $products = Product::where('is_active', true)
                ->where('stock', '>', 0)
                ->orderBy('price', 'asc')
                ->take(5)
                ->get(['id', 'name', 'slug', 'price', 'image', 'stock']);
            
            if ($products->count() > 0) {
                $productData = $products->map(function($p) {
                    $imageUrl = $p->image;
                    if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
                        $imageUrl = asset('storage/' . $imageUrl);
                    }
                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                        'slug' => $p->slug,
                        'price' => $p->price,
                        'formatted_price' => number_format($p->price, 0, ',', '.'),
                        'image' => $imageUrl ?: '/images/no-image.png',
                        'stock' => $p->stock
                    ];
                })->toArray();
                
                return [
                    'message' => "Berikut beberapa produk dengan harga terjangkau:\n\nKlik produk untuk melihat detail lengkap.",
                    'products' => $productData
                ];
            }
            return 'Untuk informasi harga lengkap, silakan kunjungi halaman katalog atau detail produk.';
        }
        
        // Check promo/diskon BEFORE stock check (karena kata "ada" bisa memicu stock check)
        if (str_contains($msg, 'promo') || str_contains($msg, 'diskon') || str_contains($msg, 'sale')) {
            return "🎉 Promo Saat Ini:\n\n• Diskon untuk pembelian pertama\n• Free ongkir min. belanja Rp 200.000\n• Potongan harga untuk order custom dalam jumlah besar\n\nKunjungi halaman utama untuk promo terbaru!";
        }
        
        if (str_contains($msg, 'stok') || str_contains($msg, 'tersedia') || str_contains($msg, 'stock')) {
            $inStockCount = Product::where('is_active', true)->where('stock', '>', 0)->count();
            return "Kami memiliki {$inStockCount} produk yang tersedia saat ini! Untuk cek ketersediaan produk spesifik, silakan lihat halaman detail produk.";
        }
        
        if (str_contains($msg, 'kirim') || str_contains($msg, 'pengiriman') || str_contains($msg, 'ongkir')) {
            return "📦 Info Pengiriman:\n\n• Jawa: 2-4 hari kerja\n• Luar Jawa: 3-7 hari kerja\n• Pengiriman via JNE, J&T, SiCepat\n\nOngkos kirim dihitung saat checkout berdasarkan lokasi Anda.";
        }
        
        if (str_contains($msg, 'custom') || str_contains($msg, 'desain') || str_contains($msg, 'design')) {
            $customCount = Product::where('is_active', true)->where('custom_design_allowed', true)->count();
            return "🎨 Custom Design:\n\nYa! Kami menerima custom design. Saat ini ada {$customCount} produk yang mendukung custom design.\n\nCara order custom:\n1. Pilih produk dengan label CUSTOM\n2. Upload desain Anda saat checkout\n3. Tim kami akan review dalam 1x24 jam";
        }
        
        if (str_contains($msg, 'kategori') || str_contains($msg, 'produk') || str_contains($msg, 'jual')) {
            $categories = Product::select('category')
                ->distinct()
                ->whereNotNull('category')
                ->where('is_active', true)
                ->pluck('category')
                ->toArray();
            
            if (count($categories) > 0) {
                $categoryList = array_map(function($c) { return "• " . ucfirst($c); }, $categories);
                return "📂 Kategori Produk:\n\n" . implode("\n", $categoryList) . "\n\nKunjungi halaman Katalog untuk lihat semua produk.";
            }
            return "Kami menyediakan berbagai macam produk fashion. Kunjungi halaman Katalog untuk melihat semua kategori.";
        }
        
        if (str_contains($msg, 'warna') || str_contains($msg, 'color')) {
            return "🎨 Pilihan Warna:\n\nSetiap produk memiliki pilihan warna yang berbeda. Silakan kunjungi halaman detail produk untuk melihat pilihan warna yang tersedia.";
        }
        
        if (str_contains($msg, 'ukuran') || str_contains($msg, 'size')) {
            return "📏 Ukuran:\n\nSetiap produk memiliki pilihan ukuran yang berbeda. Silakan kunjungi halaman detail produk untuk melihat ukuran yang tersedia.";
        }
        
        if (str_contains($msg, 'bahan') || str_contains($msg, 'material')) {
            return "🧵 Material/Bahan:\n\nInformasi detail tentang bahan produk tersedia di halaman detail produk masing-masing. Kami menggunakan bahan berkualitas tinggi untuk semua produk.";
        }
        
        if (str_contains($msg, 'halo') || str_contains($msg, 'hai') || str_contains($msg, 'hello') || str_contains($msg, 'hi')) {
            return "Halo! 👋 Selamat datang di LGI Store. Ada yang bisa saya bantu hari ini?\n\nAnda bisa tanya tentang:\n• Harga produk\n• Ketersediaan stok\n• Custom design\n• Pengiriman\n• Promo";
        }
        
        if (str_contains($msg, 'terima kasih') || str_contains($msg, 'thanks') || str_contains($msg, 'makasih')) {
            return "Sama-sama! 😊 Senang bisa membantu Anda. Jika ada pertanyaan lain, jangan ragu untuk bertanya ya!\n\nSelamat berbelanja di LGI Store!";
        }
        
        return "Terima kasih atas pertanyaan Anda! 😊\n\nUntuk bantuan lebih lanjut, Anda bisa:\n• Kunjungi halaman Chat untuk berbicara dengan tim support\n• Cek halaman FAQ untuk pertanyaan umum\n• Atau tanyakan langsung tentang harga, stok, custom design, atau pengiriman.";
    }

    /**
     * Get unread message count for current user (customer)
     * Returns count of messages from admin that user hasn't read
     */
    public function getUnreadCount()
    {
        try {
            $userId = Auth::id();
            
            if (!$userId) {
                return response()->json([
                    'success' => true,
                    'unread_count' => 0
                ]);
            }
            
            // Find user's conversations
            $conversationIds = ChatConversation::where('user_id', $userId)
                ->pluck('id');
            
            if ($conversationIds->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'unread_count' => 0
                ]);
            }
            
            // Count unread messages from admin/bot that user hasn't read
            $unreadCount = ChatMessage::whereIn('conversation_id', $conversationIds)
                ->whereIn('sender_type', ['admin', 'bot'])
                ->where('is_read_by_user', false)
                ->count();
            
            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Chatbot getUnreadCount error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'unread_count' => 0,
                'error' => 'Failed to get unread count'
            ], 500);
        }
    }

    /**
     * Mark all messages as read for current user
     */
    public function markAsRead()
    {
        try {
            $userId = Auth::id();
            
            if (!$userId) {
                return response()->json([
                    'success' => true
                ]);
            }
            
            // Find user's conversations
            $conversationIds = ChatConversation::where('user_id', $userId)
                ->pluck('id');
            
            if ($conversationIds->isEmpty()) {
                return response()->json([
                    'success' => true
                ]);
            }
            
            // Mark all admin/bot messages as read by user
            ChatMessage::whereIn('conversation_id', $conversationIds)
                ->whereIn('sender_type', ['admin', 'bot'])
                ->where('is_read_by_user', false)
                ->update(['is_read_by_user' => true]);
            
            return response()->json([
                'success' => true
            ]);
            
        } catch (\Exception $e) {
            Log::error('Chatbot markAsRead error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark messages as read'
            ], 500);
        }
    }
}
