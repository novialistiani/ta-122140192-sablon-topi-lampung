<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Product;
use App\Services\ChatBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    private $chatBotService;

    public function __construct(ChatBotService $chatBotService)
    {
        $this->chatBotService = $chatBotService;
    }

    public function startChat(Request $request, $productId = null)
    {
        $product = null;
        $productData = null;

        if ($productId) {
            $product = Product::find($productId);
            $productData = [
                'id' => $product->id,
                'name' => $product->nama,
                'price' => $product->harga,
                'image' => $product->gambar
            ];
        }

        // Cari atau buat conversation
        $conversation = ChatConversation::firstOrCreate([
            'user_id' => Auth::id(),
            'product_id' => $productId,
            'status' => 'active'
        ], [
            'subject' => $product ? "Tanya tentang {$product->nama}" : "Pertanyaan Umum"
        ]);

        $templateQuestions = $this->chatBotService->getTemplateQuestions($product);

        return view('chat.chat-window', compact('conversation', 'productData', 'templateQuestions'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:chat_conversations,id',
            'message' => 'required|string|max:1000'
        ]);

        $conversation = ChatConversation::find($request->conversation_id);

        // Authorization check
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $productData = $conversation->product ? [
            'id' => $conversation->product->id,
            'name' => $conversation->product->nama,
            'price' => $conversation->product->harga
        ] : null;

        $botResponse = $this->chatBotService->processMessage(
            $request->conversation_id, 
            $request->message, 
            $productData
        );

        return response()->json([
            'success' => true,
            'bot_response' => $botResponse,
            'conversation' => $conversation->load('messages')
        ]);
    }

    /**
     * Customer trigger untuk meminta jawaban langsung dari admin
     * Ini akan mengirim notifikasi ke admin dan mark conversation as needs response
     */
    public function requestAdminResponse(Request $request, $conversationId)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $conversation = ChatConversation::find($conversationId);

        if (!$conversation || $conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $result = $this->chatBotService->notifyCustomerNeedsResponse(
                $conversationId, 
                $request->reason ?? 'Customer meminta jawaban langsung admin'
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Admin telah diberitahu dan akan segera membantu Anda'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error requesting admin response', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim permintaan ke admin'
            ], 500);
        }
    }

    public function getConversation($conversationId)
    {
        $conversation = ChatConversation::with(['messages' => function($query) {
            $query->orderBy('created_at', 'asc');
        }])->findOrFail($conversationId);

        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($conversation);
    }

    public function getChatHistory()
    {
        $conversations = ChatConversation::with(['latestMessage', 'product'])
            ->where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('chat.chat-history', compact('conversations'));
    }

    /**
     * TEST METHOD: Untuk testing n8n integration dengan fresh stock data
     * 
     * Enhanced version yang query database untuk mendapatkan stock realtime
     */
    public function testSendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'product_id' => 'nullable|integer',
            'product' => 'nullable|array'
        ]);

        // Get N8N URL from config, fallback to production URL
        $n8nUrl = config('services.n8n.webhook_url') ?? 'https://sablontopilampung.app.n8n.cloud/webhook/chatbot';

        // DEBUG: Log request data
        \Log::info('testSendMessage Request:', $request->all());

        // Build base payload
        $payload = [
            'message' => $request->message,
            'conversation_id' => $request->conversation_id ?? $request->product_id ?? rand(1000, 9999),
            'user_id' => Auth::check() ? Auth::id() : 1
        ];

        // ===== ENHANCED: Query fresh stock data =====
        if ($request->has('product') && is_array($request->product)) {
            $productData = $request->product;
            $productId = $productData['id'] ?? null;
            
            // Query fresh stock info jika ada product_id
            if ($productId) {
                try {
                    $stockInfo = $this->chatBotService->getProductStockInfo($productId);
                    
                    if ($stockInfo['success']) {
                        // Enhance product data dengan fresh stock info
                        $productData = $this->chatBotService->enrichProductDataWithFreshStock($productData);
                        
                        \Log::info('Fresh stock data loaded:', [
                            'product_id' => $productId,
                            'stock' => $stockInfo['stock'],
                            'available_colors' => $stockInfo['colors'],
                            'available_sizes' => $stockInfo['sizes'],
                            'is_in_stock' => $stockInfo['is_in_stock']
                        ]);
                    } else {
                        \Log::warning('Failed to load fresh stock, using frontend data', [
                            'product_id' => $productId,
                            'error' => $stockInfo['error'] ?? 'Unknown error'
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Exception loading fresh stock data: ' . $e->getMessage());
                    // Continue dengan data yang ada
                }
            }
            
            $payload['product'] = $productData;
            \Log::info('Product data prepared for N8N:', $productData);
        } 
        // Fallback: cari dari database jika hanya product_id yang dikirim
        elseif ($request->product_id) {
            try {
                $stockInfo = $this->chatBotService->getProductStockInfo($request->product_id);
                
                if ($stockInfo['success']) {
                    $payload['product'] = [
                        'id' => $stockInfo['product_id'],
                        'name' => $stockInfo['product_name'],
                        'stock' => $stockInfo['stock'],
                        'total_variant_stock' => $stockInfo['total_variant_stock'],
                        'colors' => $stockInfo['colors'],
                        'sizes' => $stockInfo['sizes'],
                        'is_in_stock' => $stockInfo['is_in_stock'],
                        'metadata' => $stockInfo['metadata'] ?? []
                    ];
                    
                    \Log::info('Product data from database:', $payload['product']);
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching product from database: ' . $e->getMessage());
            }
        }

        // DEBUG: Log final payload
        \Log::info('Final payload to N8N:', $payload);

        try {
            $response = Http::timeout(30)->post($n8nUrl, $payload);

            // DEBUG: Log n8n response
            \Log::info('N8N Response:', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Simpan ke database menggunakan helper function
                $botMessage = $responseData['message'] ?? 'Response received';
                $saveResult = $this->saveConversationToDatabase(
                    $request->message,
                    $botMessage,
                    $payload['product'] ?? null
                );
                
                $savedConversationId = $saveResult['conversation_id'] ?? null;
                $adminActive = $saveResult['admin_active'] ?? false;

                // Jika admin sudah take over, jangan kirim bot response ke customer
                if ($adminActive) {
                    return response()->json([
                        'success' => true,
                        'bot_response' => [
                            'message' => '⏳ Admin sedang menangani chat Anda. Mohon tunggu balasan dari admin.'
                        ],
                        'user_message' => $request->message,
                        'conversation_id' => $savedConversationId,
                        'admin_active' => true,
                        'admin_name' => $saveResult['admin_name'] ?? null,
                        'metadata' => [
                            'admin_takeover' => true,
                            'saved_to_database' => $savedConversationId !== null
                        ]
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'bot_response' => $responseData,
                    'user_message' => $request->message,
                    'conversation_id' => $savedConversationId,
                    'admin_active' => false,
                    'metadata' => [
                        'stock_query_executed' => isset($payload['product']['metadata']),
                        'fresh_stock_data' => $payload['product']['metadata'] ?? null,
                        'saved_to_database' => $savedConversationId !== null
                    ]
                ]);
            } else {
                // N8N tidak tersedia - gunakan fallback response berdasarkan data produk
                \Log::warning('N8N unavailable, using fallback response', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                $fallbackResponse = $this->generateFallbackResponse(
                    $request->message, 
                    $payload['product'] ?? null
                );
                
                // TETAP SIMPAN KE DATABASE meskipun n8n tidak tersedia
                $saveResult = $this->saveConversationToDatabase(
                    $request->message,
                    $fallbackResponse['message'] ?? 'Fallback response',
                    $payload['product'] ?? null
                );
                
                $savedConversationId = $saveResult['conversation_id'] ?? null;
                $adminActive = $saveResult['admin_active'] ?? false;
                
                // Jika admin sudah take over
                if ($adminActive) {
                    return response()->json([
                        'success' => true,
                        'bot_response' => [
                            'message' => '⏳ Admin sedang menangani chat Anda. Mohon tunggu balasan dari admin.'
                        ],
                        'user_message' => $request->message,
                        'conversation_id' => $savedConversationId,
                        'admin_active' => true,
                        'admin_name' => $saveResult['admin_name'] ?? null,
                        'metadata' => [
                            'admin_takeover' => true,
                            'saved_to_database' => $savedConversationId !== null
                        ]
                    ]);
                }
                
                return response()->json([
                    'success' => true,
                    'bot_response' => $fallbackResponse,
                    'user_message' => $request->message,
                    'conversation_id' => $savedConversationId,
                    'admin_active' => false,
                    'metadata' => [
                        'fallback_used' => true,
                        'n8n_status' => $response->status(),
                        'saved_to_database' => $savedConversationId !== null
                    ]
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('testSendMessage Exception:', [
                'error' => $e->getMessage(),
                'payload' => $payload ?? []
            ]);
            
            // Fallback jika terjadi exception
            $fallbackResponse = $this->generateFallbackResponse(
                $request->message, 
                $payload['product'] ?? null
            );
            
            // TETAP SIMPAN KE DATABASE meskipun ada exception
            $saveResult = $this->saveConversationToDatabase(
                $request->message,
                $fallbackResponse['message'] ?? 'Fallback response',
                $payload['product'] ?? null
            );
            
            $savedConversationId = $saveResult['conversation_id'] ?? null;
            $adminActive = $saveResult['admin_active'] ?? false;
            
            // Jika admin sudah take over
            if ($adminActive) {
                return response()->json([
                    'success' => true,
                    'bot_response' => [
                        'message' => '⏳ Admin sedang menangani chat Anda. Mohon tunggu balasan dari admin.'
                    ],
                    'user_message' => $request->message,
                    'conversation_id' => $savedConversationId,
                    'admin_active' => true,
                    'metadata' => [
                        'admin_takeover' => true,
                        'saved_to_database' => $savedConversationId !== null
                    ]
                ]);
            }
            
            return response()->json([
                'success' => true,
                'bot_response' => $fallbackResponse,
                'user_message' => $request->message,
                'conversation_id' => $savedConversationId,
                'admin_active' => false,
                'metadata' => [
                    'fallback_used' => true,
                    'error' => $e->getMessage(),
                    'saved_to_database' => $savedConversationId !== null
                ]
            ]);
        }
    }
    
    /**
     * Save conversation and messages to database
     * Returns array with conversation_id and admin_active status
     * 
     * UNIFIED: All chat sources (popup, chatpage, product_detail) use the same conversation
     * to keep history synchronized
     */
    private function saveConversationToDatabase($userMessage, $botResponse, $productData = null)
    {
        if (!Auth::check()) {
            \Log::info('Chat not saved - user not authenticated');
            return ['conversation_id' => null, 'admin_active' => false];
        }
        
        try {
            $productId = $productData['id'] ?? null;
            $productName = $productData['name'] ?? 'Unknown Product';
            
            // UNIFIED: Use 'chatbot' as source for all customer chatbot conversations
            // This ensures popup, chatpage, and product_detail all share the same history
            $conversation = ChatConversation::where('user_id', Auth::id())
                ->where('chat_source', 'chatbot')
                ->where('status', 'open')
                ->orderBy('updated_at', 'desc')
                ->first();
            
            if (!$conversation) {
                // Check for closed conversation to reopen
                $closedConversation = ChatConversation::where('user_id', Auth::id())
                    ->where('chat_source', 'chatbot')
                    ->where('status', 'closed')
                    ->orderBy('updated_at', 'desc')
                    ->first();
                    
                if ($closedConversation) {
                    $closedConversation->update([
                        'status' => 'open',
                        'expires_at' => now()->addDays(30)
                    ]);
                    $conversation = $closedConversation;
                } else {
                    // Create new unified conversation
                    $conversation = ChatConversation::create([
                        'user_id' => Auth::id(),
                        'status' => 'open',
                        'subject' => 'Customer Chatbot',
                        'chat_source' => 'chatbot',
                        'expires_at' => now()->addDays(30)
                    ]);
                }
            }

            // Simpan user message with product context in metadata
            $userMetadata = null;
            if ($productData) {
                $userMetadata = ['product_context' => $productData];
            }
            
            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'chat_conversation_id' => $conversation->id,
                'user_id' => Auth::id(),
                'sender_type' => 'user', // Unified: use 'user' instead of 'customer'
                'message' => $userMessage,
                'metadata' => $userMetadata,
                'is_read_by_admin' => false
            ]);

            // CEK: Jika admin sudah take over, JANGAN simpan bot response
            // Biarkan admin yang membalas
            $adminActive = $conversation->taken_over_by_admin || $conversation->is_admin_active;
            
            if (!$adminActive && $botResponse) {
                // Simpan bot response hanya jika admin belum take over
                ChatMessage::create([
                    'conversation_id' => $conversation->id,
                    'chat_conversation_id' => $conversation->id,
                    'sender_type' => 'bot', // Unified: use 'bot' instead of 'admin'
                    'message' => $botResponse,
                    'is_read_by_user' => true
                ]);
            }
            
            // Update conversation timestamp
            $conversation->touch();
            
            \Log::info('Chat saved to database', [
                'conversation_id' => $conversation->id,
                'product_id' => $productId,
                'user_id' => Auth::id(),
                'admin_active' => $adminActive,
                'bot_response_saved' => !$adminActive
            ]);
            
            return [
                'conversation_id' => $conversation->id,
                'admin_active' => $adminActive,
                'admin_name' => $conversation->admin ? $conversation->admin->name : null
            ];
            
        } catch (\Exception $dbError) {
            \Log::error('Database save error:', ['error' => $dbError->getMessage()]);
            return null;
        }
    }
    
    /**
     * Generate fallback response when n8n is unavailable
     */
    private function generateFallbackResponse($userMessage, $productData = null)
    {
        $message = strtolower($userMessage);
        $productName = $productData['name'] ?? 'produk ini';
        $productPrice = $productData['price'] ?? null;
        $productStock = $productData['stock'] ?? $productData['total_variant_stock'] ?? null;
        $productColors = $productData['colors'] ?? [];
        $productSizes = $productData['sizes'] ?? [];
        $isInStock = $productData['is_in_stock'] ?? false;
        
        // Deteksi intent dari pesan
        if (str_contains($message, 'harga') || str_contains($message, 'berapa') || str_contains($message, 'price')) {
            if ($productPrice) {
                $formattedPrice = 'Rp ' . number_format($productPrice, 0, ',', '.');
                return [
                    'message' => "Harga {$productName} adalah {$formattedPrice}. Silakan hubungi admin jika ada pertanyaan lebih lanjut! 😊"
                ];
            }
            return ['message' => "Untuk informasi harga {$productName}, silakan hubungi admin kami."];
        }
        
        if (str_contains($message, 'stok') || str_contains($message, 'stock') || str_contains($message, 'ready') || str_contains($message, 'tersedia')) {
            if ($isInStock && $productStock > 0) {
                return [
                    'message' => "Ya, {$productName} ready stock! Stok tersedia: {$productStock} item. Silakan order sekarang! 🛒"
                ];
            } else {
                return ['message' => "Mohon maaf, stok {$productName} sedang habis. Silakan hubungi admin untuk info restock."];
            }
        }
        
        if (str_contains($message, 'warna') || str_contains($message, 'color') || str_contains($message, 'pilihan warna')) {
            if (!empty($productColors)) {
                $colorList = implode(', ', $productColors);
                return [
                    'message' => "Warna yang tersedia untuk {$productName}: {$colorList}. Silakan pilih warna favorit Anda! 🎨"
                ];
            }
            return ['message' => "Untuk pilihan warna {$productName}, silakan lihat opsi warna di halaman produk atau hubungi admin."];
        }
        
        if (str_contains($message, 'ukuran') || str_contains($message, 'size') || str_contains($message, 'besar')) {
            if (!empty($productSizes)) {
                $sizeList = implode(', ', $productSizes);
                return [
                    'message' => "Ukuran yang tersedia untuk {$productName}: {$sizeList}. Pilih ukuran yang sesuai! 📏"
                ];
            }
            return ['message' => "Untuk pilihan ukuran {$productName}, silakan lihat opsi ukuran di halaman produk."];
        }
        
        if (str_contains($message, 'custom') || str_contains($message, 'design') || str_contains($message, 'desain')) {
            return [
                'message' => "Kami menerima pesanan custom design untuk {$productName}! Silakan klik tombol Custom Design di halaman produk atau hubungi admin untuk detail. ✨"
            ];
        }
        
        if (str_contains($message, 'bahan') || str_contains($message, 'material') || str_contains($message, 'kualitas')) {
            return [
                'message' => "Produk kami menggunakan bahan berkualitas tinggi. Untuk detail spesifikasi bahan {$productName}, silakan hubungi admin. 👕"
            ];
        }
        
        if (str_contains($message, 'kirim') || str_contains($message, 'pengiriman') || str_contains($message, 'ongkir')) {
            return [
                'message' => "Kami melayani pengiriman ke seluruh Indonesia melalui berbagai ekspedisi. Ongkir dihitung saat checkout. Untuk estimasi, silakan hubungi admin. 🚚"
            ];
        }
        
        // Default response
        return [
            'message' => "Terima kasih atas pertanyaan Anda tentang {$productName}! Untuk informasi lebih lanjut, silakan hubungi admin kami atau gunakan tombol pertanyaan cepat di bawah. 😊"
        ];
    }

    /**
     * Get new messages for customer (polling endpoint)
     * Customer dapat menerima pesan baru dari admin melalui endpoint ini
     */
    public function getNewMessages(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $conversationId = $request->conversation_id;
        $lastMessageId = $request->last_message_id ?? 0;

        if (!$conversationId) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation ID required'
            ], 400);
        }

        try {
            $conversation = ChatConversation::where('id', $conversationId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation not found'
                ], 404);
            }

            // Get new messages after last_message_id
            $newMessages = ChatMessage::where('conversation_id', $conversationId)
                ->where('id', '>', $lastMessageId)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($msg) {
                    return [
                        'id' => $msg->id,
                        'message' => $msg->message,
                        'sender_type' => $msg->sender_type,
                        'is_admin_reply' => $msg->is_admin_reply,
                        'created_at' => $msg->created_at->format('H:i'),
                        'is_from_admin' => $msg->sender_type === 'admin' && $msg->is_admin_reply
                    ];
                });

            // Mark admin messages as read by user
            ChatMessage::where('conversation_id', $conversationId)
                ->where('sender_type', 'admin')
                ->where('is_read_by_user', false)
                ->update(['is_read_by_user' => true]);

            return response()->json([
                'success' => true,
                'messages' => $newMessages,
                'is_admin_active' => $conversation->is_admin_active,
                'taken_over_by_admin' => $conversation->taken_over_by_admin,
                'admin_name' => $conversation->admin ? $conversation->admin->name : null
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting new messages:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error fetching messages'
            ], 500);
        }
    }

    /**
     * Get conversation status for customer
     */
    public function getConversationStatus(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false], 401);
        }

        $conversationId = $request->conversation_id;
        
        if (!$conversationId) {
            return response()->json(['success' => false, 'message' => 'No conversation'], 404);
        }

        $conversation = ChatConversation::where('id', $conversationId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$conversation) {
            return response()->json(['success' => false], 404);
        }

        return response()->json([
            'success' => true,
            'is_admin_active' => $conversation->is_admin_active,
            'taken_over_by_admin' => $conversation->taken_over_by_admin,
            'status' => $conversation->status
        ]);
    }

    /**
     * TEST METHOD: Quick test dengan predefined messages
     */
    public function quickTest(Request $request)
    {
        $testType = $request->type ?? 'harga';
        $productId = $request->product_id;
        
        $tests = [
            'harga' => 'berapa harga produk ini?',
            'stok' => 'apakah ready stock?',
            'warna' => 'warna apa yang tersedia?',
            'bahan' => 'bahan apa yang digunakan?',
            'pengiriman' => 'berapa lama pengiriman?'
        ];

        $message = $tests[$testType] ?? 'harga produk berapa?';

        $n8nUrl = 'https://sablontopilampung.app.n8n.cloud/webhook/chatbot';
        
        $payload = [
            'message' => $message,
            'conversation_id' => $productId ?? rand(1000, 9999),
            'user_id' => Auth::id() ?? 1
        ];

        if ($productId) {
            $product = Product::find($productId);
            if ($product) {
                $payload['product'] = [
                    'name' => $product->nama,
                    'price' => $product->harga
                ];
            }
        }

        try {
            $response = Http::timeout(30)->post($n8nUrl, $payload);

            return response()->json([
                'success' => true,
                'test_type' => $testType,
                'message_sent' => $message,
                'bot_response' => $response->json(),
                'product_used' => $product ? $product->nama : 'none'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'test_type' => $testType,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}