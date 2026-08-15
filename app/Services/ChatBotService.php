<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatBotService
{
    private $n8nWebhookUrl;

    public function __construct()
    {
        // Gunakan URL langsung untuk production
        $this->n8nWebhookUrl = 'https://sablontopilampung.app.n8n.cloud/webhook/chatbot';
        
        // Jika config tersedia, gunakan config (untuk override)
        if (config('services.n8n.webhook_url')) {
            $this->n8nWebhookUrl = config('services.n8n.webhook_url');
        }
    }

    public function processMessage($conversationId, $userMessage, $productData = null)
    {
        $conversation = ChatConversation::with('product')->find($conversationId);
        
        // Simpan pesan user ke database
        $userMessageRecord = ChatMessage::create([
            'conversation_id' => $conversationId,
            'sender_type' => 'user',
            'message' => $userMessage,
            'metadata' => $productData ? ['product' => $productData] : null
        ]);

        // Cek jika perlu escalate ke admin
        if ($this->shouldEscalateToAdmin($userMessage)) {
            return $this->escalateToAdmin($conversationId, $userMessageRecord);
        }

        // Kirim ke n8n untuk processing
        $botResponse = $this->sendToN8n($conversation, $userMessageRecord, $productData);

        // Simpan response bot ke database
        $botMessage = ChatMessage::create([
            'conversation_id' => $conversationId,
            'sender_type' => 'bot',
            'message' => $botResponse['message'],
            'metadata' => $botResponse['metadata'] ?? null
        ]);

        return $botMessage;
    }

    private function sendToN8n($conversation, $userMessage, $productData = null)
    {
        try {
            // Prepare payload dengan struktur yang sesuai n8n
            $payload = [
                'message' => $userMessage->message,
                'conversation_id' => $conversation->id,
                'user_id' => $conversation->user_id
            ];

            // Tambahkan product data jika ada
            if ($productData) {
                $payload['product'] = [
                    'name' => $productData['name'] ?? null,
                    'price' => $productData['price'] ?? null
                ];
            } elseif ($conversation->product) {
                $payload['product'] = [
                    'name' => $conversation->product->nama,
                    'price' => $conversation->product->harga
                ];
            }

            Log::info('Sending to n8n:', [
                'url' => $this->n8nWebhookUrl,
                'payload' => $payload
            ]);

            $response = Http::timeout(30)->post($this->n8nWebhookUrl, $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                Log::info('n8n Response:', $responseData);
                
                return $responseData;
            } else {
                Log::warning('n8n HTTP Error:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return $this->getDefaultResponse($userMessage->message);
            }

        } catch (\Exception $e) {
            Log::error('N8N Connection Error: ' . $e->getMessage(), [
                'url' => $this->n8nWebhookUrl,
                'conversation_id' => $conversation->id
            ]);
            
            return $this->analyzeAndRespond($userMessage->message, $conversation);
        }
    }

    /**
     * Analyze user intent and provide smart response
     */
    private function analyzeAndRespond($userMessage, $conversation = null)
    {
        $message = strtolower(trim($userMessage));
        $intent = $this->detectIntent($message);
        
        Log::info('Intent Analysis', [
            'message' => $message,
            'detected_intent' => $intent['type'],
            'confidence' => $intent['confidence']
        ]);

        return $this->generateResponse($intent, $conversation);
    }

    /**
     * Detect user intent from message
     */
    private function detectIntent($message)
    {
        // Greeting patterns
        $greetingPatterns = [
            'halo', 'hai', 'hi', 'hello', 'selamat pagi', 'selamat siang', 
            'selamat sore', 'selamat malam', 'assalamualaikum', 'permisi',
            'hey', 'yo', 'p', 'hei', 'oi'
        ];
        
        // Product inquiry patterns
        $productInquiryPatterns = [
            'harga' => ['harga', 'berapa', 'price', 'biaya', 'tarif', 'cost'],
            'stock' => ['stok', 'stock', 'ready', 'tersedia', 'ada', 'available', 'kosong'],
            'color' => ['warna', 'color', 'colour', 'pilihan warna'],
            'size' => ['ukuran', 'size', 'sizing', 'besar', 'kecil', 'xs', 's', 'm', 'l', 'xl', 'xxl'],
            'material' => ['bahan', 'material', 'kain', 'cotton', 'polyester', 'fabric'],
            'shipping' => ['kirim', 'pengiriman', 'ongkir', 'delivery', 'shipping', 'ekspedisi', 'jne', 'jnt', 'sicepat'],
            'payment' => ['bayar', 'pembayaran', 'payment', 'transfer', 'cod', 'dana', 'ovo', 'gopay'],
            'custom' => ['custom', 'desain', 'design', 'sablon', 'logo', 'gambar', 'print']
        ];
        
        // Recommendation request patterns
        $recommendationPatterns = [
            'rekomendasi', 'recommend', 'saran', 'suggest', 'bagus', 'terbaik',
            'best', 'favorit', 'populer', 'laris', 'murah', 'terjangkau',
            'pilihan', 'cocok', 'pas', 'budget'
        ];
        
        // Order/transaction patterns
        $orderPatterns = [
            'pesan', 'beli', 'order', 'checkout', 'transaksi', 'cara pesan',
            'cara beli', 'mau beli', 'pengen beli', 'mau order'
        ];
        
        // Help patterns
        $helpPatterns = [
            'bantuan', 'help', 'tolong', 'gmn', 'gimana', 'bagaimana', 'cara'
        ];
        
        // Gratitude patterns
        $gratitudePatterns = [
            'terima kasih', 'makasih', 'thanks', 'thank you', 'thx', 'tq',
            'mantap', 'ok', 'oke', 'baik', 'siap'
        ];

        // Check greeting first
        foreach ($greetingPatterns as $pattern) {
            if (str_contains($message, $pattern) && strlen($message) < 30) {
                return ['type' => 'greeting', 'confidence' => 0.95, 'subtype' => null];
            }
        }
        
        // Check gratitude
        foreach ($gratitudePatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return ['type' => 'gratitude', 'confidence' => 0.9, 'subtype' => null];
            }
        }

        // Check recommendation request
        foreach ($recommendationPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return ['type' => 'recommendation', 'confidence' => 0.85, 'subtype' => null];
            }
        }

        // Check order intent
        foreach ($orderPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return ['type' => 'order', 'confidence' => 0.85, 'subtype' => null];
            }
        }

        // Check product inquiry
        foreach ($productInquiryPatterns as $category => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($message, $pattern)) {
                    return ['type' => 'product_inquiry', 'confidence' => 0.8, 'subtype' => $category];
                }
            }
        }

        // Check help
        foreach ($helpPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return ['type' => 'help', 'confidence' => 0.7, 'subtype' => null];
            }
        }

        // Default - general question
        return ['type' => 'general', 'confidence' => 0.3, 'subtype' => null];
    }

    /**
     * Generate response based on detected intent
     */
    private function generateResponse($intent, $conversation = null)
    {
        $responses = [
            'greeting' => [
                'messages' => [
                    "Halo! 👋 Selamat datang di LGI Store. Ada yang bisa saya bantu hari ini?",
                    "Hai! 😊 Terima kasih sudah menghubungi LGI Store. Silakan tanyakan apa saja tentang produk kami!",
                    "Selamat datang! 🎉 Saya siap membantu Anda. Mau cari produk apa hari ini?"
                ],
                'quick_replies' => ['Lihat Produk Populer', 'Rekomendasi Produk', 'Info Pengiriman', 'Cara Pemesanan']
            ],
            'gratitude' => [
                'messages' => [
                    "Sama-sama! 😊 Senang bisa membantu. Ada yang lain yang ingin ditanyakan?",
                    "Terima kasih kembali! 🙏 Jangan ragu untuk bertanya lagi ya!",
                    "Siap! Semoga informasinya bermanfaat. Ada pertanyaan lain?"
                ],
                'quick_replies' => ['Lihat Produk Lain', 'Cara Pemesanan', 'Hubungi Admin']
            ],
            'recommendation' => [
                'messages' => $this->getProductRecommendations(),
                'quick_replies' => ['Lihat Detail', 'Produk Lainnya', 'Filter Harga']
            ],
            'order' => [
                'messages' => [
                    "Untuk melakukan pemesanan:\n\n1️⃣ Pilih produk yang diinginkan\n2️⃣ Pilih varian (warna/ukuran)\n3️⃣ Masukkan ke keranjang\n4️⃣ Checkout dan isi alamat pengiriman\n5️⃣ Pilih metode pembayaran\n6️⃣ Konfirmasi pesanan\n\nAtau hubungi admin untuk bantuan langsung! 📞"
                ],
                'quick_replies' => ['Lihat Katalog', 'Hubungi Admin', 'Cek Keranjang']
            ],
            'help' => [
                'messages' => [
                    "Saya bisa membantu Anda dengan:\n\n📦 Informasi produk (harga, stok, warna, ukuran)\n🛒 Cara pemesanan\n🚚 Info pengiriman\n💳 Metode pembayaran\n🎨 Custom design\n📋 Rekomendasi produk\n\nSilakan ketik pertanyaan Anda!"
                ],
                'quick_replies' => ['Info Produk', 'Cara Pesan', 'Rekomendasi', 'Hubungi Admin']
            ],
            'product_inquiry' => $this->getProductInquiryResponse($intent['subtype'], $conversation),
            'general' => [
                'messages' => [
                    "Terima kasih atas pertanyaannya! Untuk jawaban yang lebih detail, admin kami akan segera membantu. 😊"
                ],
                'quick_replies' => ['Hubungi Admin', 'Lihat Katalog', 'FAQ'],
                'should_escalate' => true
            ]
        ];

        $responseData = $responses[$intent['type']] ?? $responses['general'];
        
        // Select random message if multiple
        $message = is_array($responseData['messages']) 
            ? (is_array($responseData['messages'][0] ?? null) 
                ? $responseData['messages'][0] 
                : $responseData['messages'][array_rand($responseData['messages'])])
            : $responseData['messages'];

        return [
            'message' => $message,
            'metadata' => [
                'type' => 'auto_response',
                'confidence' => $intent['confidence'],
                'detected_intent' => $intent['type'],
                'subtype' => $intent['subtype'] ?? null,
                'quick_replies' => $responseData['quick_replies'] ?? [],
                'should_escalate' => $responseData['should_escalate'] ?? false
            ]
        ];
    }

    /**
     * Get product inquiry response based on subtype
     */
    private function getProductInquiryResponse($subtype, $conversation = null)
    {
        $responses = [
            'harga' => [
                'messages' => ["Untuk informasi harga produk, silakan kunjungi halaman katalog kami atau tanyakan produk spesifik yang Anda inginkan! 💰\n\nHarga mulai dari Rp 50.000 - Rp 300.000 tergantung jenis dan ukuran."],
                'quick_replies' => ['Lihat Katalog', 'Produk Termurah', 'Hubungi Admin']
            ],
            'stock' => [
                'messages' => ["Stok produk kami diupdate secara real-time di halaman produk. 📦\n\nUntuk memastikan ketersediaan, silakan cek langsung di halaman produk atau tanyakan produk spesifiknya!"],
                'quick_replies' => ['Cek Katalog', 'Produk Ready Stock', 'Hubungi Admin']
            ],
            'color' => [
                'messages' => ["Kami menyediakan berbagai pilihan warna untuk setiap produk! 🎨\n\nPilihan warna tersedia di halaman detail produk. Warna populer: Hitam, Putih, Navy, Maroon, Abu-abu."],
                'quick_replies' => ['Lihat Katalog', 'Custom Warna', 'Hubungi Admin']
            ],
            'size' => [
                'messages' => ["Ukuran yang tersedia umumnya: S, M, L, XL, XXL 📏\n\nUntuk panduan ukuran detail, silakan cek di halaman produk atau tanyakan ke admin!"],
                'quick_replies' => ['Panduan Ukuran', 'Lihat Katalog', 'Hubungi Admin']
            ],
            'material' => [
                'messages' => ["Produk kami menggunakan bahan berkualitas tinggi! 🧵\n\n• Kaos: Cotton Combed 30s\n• Polo: Lacoste Cotton\n• Jersey: Dry-fit Premium\n• Topi: Raphel/Twill"],
                'quick_replies' => ['Detail Bahan', 'Lihat Katalog', 'Hubungi Admin']
            ],
            'shipping' => [
                'messages' => ["Kami mendukung berbagai ekspedisi pengiriman! 🚚\n\n• JNE, J&T, SiCepat, Anteraja\n• Estimasi: 2-5 hari kerja\n• COD tersedia untuk area tertentu\n\nOngkir dihitung saat checkout."],
                'quick_replies' => ['Cek Ongkir', 'Area COD', 'Hubungi Admin']
            ],
            'payment' => [
                'messages' => ["Metode pembayaran yang tersedia: 💳\n\n• Transfer Bank (BCA, BRI, Mandiri)\n• E-Wallet (Dana, OVO, GoPay)\n• QRIS\n• COD (area tertentu)"],
                'quick_replies' => ['Cara Bayar', 'Konfirmasi Pembayaran', 'Hubungi Admin']
            ],
            'custom' => [
                'messages' => ["Kami menerima pesanan custom design! 🎨\n\n✅ Sablon DTF/DTG\n✅ Bordir komputer\n✅ Logo perusahaan\n✅ Desain sesuai request\n\nMinimum order: 12 pcs\nHubungi admin untuk konsultasi desain!"],
                'quick_replies' => ['Konsultasi Design', 'Lihat Contoh', 'Hubungi Admin']
            ]
        ];

        return $responses[$subtype] ?? [
            'messages' => ["Untuk informasi lebih detail tentang produk, silakan kunjungi katalog kami atau hubungi admin! 📋"],
            'quick_replies' => ['Lihat Katalog', 'Hubungi Admin']
        ];
    }

    /**
     * Get product recommendations
     */
    private function getProductRecommendations()
    {
        try {
            $products = Product::where('is_active', true)
                ->where('stock', '>', 0)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            if ($products->isEmpty()) {
                return ["Maaf, saat ini tidak ada produk yang tersedia. Silakan coba lagi nanti atau hubungi admin."];
            }

            $message = "🔥 Rekomendasi Produk Terbaik:\n\n";
            foreach ($products as $index => $product) {
                $num = $index + 1;
                $price = number_format($product->price, 0, ',', '.');
                $message .= "{$num}. {$product->name} 💰 Rp {$price} 📦 Stok: {$product->stock}\n";
            }
            $message .= "\nKlik nama produk untuk melihat detail!";

            return [$message];
        } catch (\Exception $e) {
            Log::error('Error getting product recommendations: ' . $e->getMessage());
            return ["Maaf, terjadi kesalahan saat mengambil rekomendasi produk. Silakan coba lagi."];
        }
    }

    private function getDefaultResponse($userMessage)
    {
        return $this->analyzeAndRespond($userMessage);
    }

    private function shouldEscalateToAdmin($userMessage)
    {
        $escalateKeywords = [
            'complaint', 'problem', 'issue', 'error', 'wrong', 'broken',
            'keluhan', 'masalah', 'gangguan', 'salah', 'rusak', 'komplain',
            'refund', 'pengembalian', 'garansi', 'warranty', 'sengketa'
        ];

        $message = strtolower($userMessage->message);
        
        foreach ($escalateKeywords as $keyword) {
            if (str_contains($message, $keyword)) {
                Log::info('Escalating to admin due to keyword: ' . $keyword, [
                    'conversation_id' => $userMessage->conversation_id,
                    'message' => $userMessage->message
                ]);
                return true;
            }
        }

        return false;
    }

    private function escalateToAdmin($conversationId, $userMessage)
    {
        $conversation = ChatConversation::find($conversationId);
        $conversation->update(['status' => 'escalated']);

        $adminMessage = ChatMessage::create([
            'conversation_id' => $conversationId,
            'sender_type' => 'bot',
            'message' => 'Pertanyaan Anda telah diarahkan ke admin. Admin akan merespons segera.',
            'metadata' => [
                'escalated' => true,
                'escalation_reason' => 'complex_question',
                'original_message' => $userMessage->message
            ]
        ]);

        // TODO: Notify admin via notification system
        // Broadcast atau notification ke admin panel
        Log::info('Conversation escalated to admin:', [
            'conversation_id' => $conversationId,
            'user_id' => $conversation->user_id,
            'message' => $userMessage->message
        ]);

        return $adminMessage;
    }

    public function getTemplateQuestions($product = null)
    {
        $templates = [
            'Apa harga produk ini?',
            'Apakah produk ini ready stock?',
            'Tersedia warna apa saja?',
            'Berapa lama waktu pengirimannya?',
            'Apa bahan yang digunakan?',
            'Bisa request custom design?',
            'Ada diskon untuk pembelian grosir?',
            'Bagaimana cara perawatan produk?',
            'Apa ukuran yang tersedia?'
        ];

        if ($product) {
            array_unshift($templates, "Tanya tentang {$product->nama}");
        }

        return $templates;
    }

    /**
     * Get fresh stock information including available colors and sizes
     * This method filters colors and sizes to only show those with stock > 0
     * 
     * @param int $productId
     * @return array
     */
    public function getProductStockInfo($productId)
    {
        try {
            $product = Product::with('variants')->findOrFail($productId);
            
            $availableColors = [];
            $availableSizes = [];
            $totalVariantStock = 0;
            $availableVariantCount = 0;
            
            // DEBUG: Log product data
            Log::info('🔍 getProductStockInfo: Querying product', [
                'product_id' => $productId,
                'product_name' => $product->name,
                'has_variants' => $product->variants ? count($product->variants) : 0,
                'base_stock' => $product->stock,
                'base_colors' => $product->colors,
                'base_sizes' => $product->sizes
            ]);
            
            // Process variants to get available colors and sizes
            if ($product->variants && count($product->variants) > 0) {
                foreach ($product->variants as $variant) {
                    if (!isset($variant->stock)) continue; // Skip if no stock value
                    
                    $variantStock = intval($variant->stock);
                    $totalVariantStock += $variantStock;
                    
                    Log::debug('Variant stock check', [
                        'variant_id' => $variant->id,
                        'color' => $variant->color,
                        'size' => $variant->size,
                        'stock' => $variantStock
                    ]);
                    
                    if ($variantStock > 0) {
                        $availableVariantCount++;
                        
                        // Add color if not already in list
                        if ($variant->color) {
                            $cleanColor = trim($variant->color);
                            if (!in_array($cleanColor, $availableColors)) {
                                $availableColors[] = $cleanColor;
                                Log::debug('Color added', ['color' => $cleanColor]);
                            }
                        }
                        
                        // Add size if not already in list
                        if ($variant->size) {
                            $cleanSize = trim($variant->size);
                            if (!in_array($cleanSize, $availableSizes)) {
                                $availableSizes[] = $cleanSize;
                                Log::debug('Size added', ['size' => $cleanSize]);
                            }
                        }
                    }
                }
                
                Log::info('✓ Variants processed successfully', [
                    'total_variant_stock' => $totalVariantStock,
                    'available_variants' => $availableVariantCount,
                    'available_colors_count' => count($availableColors),
                    'available_sizes_count' => count($availableSizes),
                    'available_colors' => $availableColors,
                    'available_sizes' => $availableSizes
                ]);
            } else {
                Log::info('⚠ No variants found, will use fallback colors/sizes');
            }
            
            // If no variants with stock, fall back to product colors/sizes
            if (empty($availableColors) && $product->colors) {
                $availableColors = is_array($product->colors) ? 
                    array_map('trim', $product->colors) : 
                    [trim($product->colors)];
                Log::info('Using fallback colors from Product', ['colors' => $availableColors]);
            }
            
            if (empty($availableSizes) && $product->sizes) {
                $availableSizes = is_array($product->sizes) ? 
                    array_map('trim', $product->sizes) : 
                    [trim($product->sizes)];
                Log::info('Using fallback sizes from Product', ['sizes' => $availableSizes]);
            }
            
            return [
                'success' => true,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'stock' => $product->stock,  // Base product stock
                'total_variant_stock' => $totalVariantStock,  // Total from all variants
                'colors' => $availableColors,  // Only colors with stock > 0
                'sizes' => $availableSizes,  // Only sizes with stock > 0
                'color_count' => count($availableColors),
                'size_count' => count($availableSizes),
                'total_variants' => $product->variants ? count($product->variants) : 0,
                'available_variants' => $availableVariantCount,
                'is_in_stock' => $product->stock > 0 || $totalVariantStock > 0
            ];
            
        } catch (\Exception $e) {
            Log::error('❌ Error getting product stock info: ' . $e->getMessage(), [
                'product_id' => $productId,
                'exception' => $e
            ]);
            
            return [
                'success' => false,
                'error' => 'Produk tidak ditemukan atau terjadi error',
                'product_id' => $productId,
                'exception_message' => $e->getMessage()
            ];
        }
    }

    /**
     * Prepare product data for N8N webhook with fresh stock information
     * 
     * @param array $productData - Basic product data from request
     * @return array - Enhanced product data with fresh stock info
     */
    public function enrichProductDataWithFreshStock(array $productData): array
    {
        // Jika ada product_id, query fresh stock data
        if (isset($productData['id'])) {
            $stockInfo = $this->getProductStockInfo($productData['id']);
            
            if ($stockInfo['success']) {
                // Update product data dengan fresh stock info
                $productData['stock'] = $stockInfo['stock'];
                $productData['total_variant_stock'] = $stockInfo['total_variant_stock'];
                $productData['colors'] = $stockInfo['colors'];
                $productData['sizes'] = $stockInfo['sizes'];
                $productData['is_in_stock'] = $stockInfo['is_in_stock'];
                $productData['metadata'] = [
                    'stock_queried_at' => now()->toIso8601String(),
                    'available_colors_count' => $stockInfo['color_count'],
                    'available_sizes_count' => $stockInfo['size_count'],
                    'available_variants' => $stockInfo['available_variants']
                ];
            }
        }
        
        return $productData;
    }

    /**
     * TEST METHOD: Direct n8n test tanpa database operations
     */
    public function testN8nConnection($testMessage = 'test connection')
    {
        try {
            $payload = [
                'message' => $testMessage,
                'conversation_id' => 999,
                'user_id' => 1,
                'product' => [
                    'name' => 'Test Product',
                    'price' => 50000
                ]
            ];

            $response = Http::timeout(10)->post($this->n8nWebhookUrl, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'n8n_url' => $this->n8nWebhookUrl
                ];
            } else {
                return [
                    'success' => false,
                    'status' => $response->status(),
                    'error' => $response->body(),
                    'n8n_url' => $this->n8nWebhookUrl
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'n8n_url' => $this->n8nWebhookUrl
            ];
        }
    }

    /**
     * METHOD: Untuk testing dari controller tanpa authentication
     */
    public function processTestMessage($message, $productId = null, $userId = 1)
    {
        $productData = null;
        
        if ($productId) {
            $product = Product::find($productId);
            if ($product) {
                $productData = [
                    'name' => $product->nama,
                    'price' => $product->harga
                ];
            }
        }

        $payload = [
            'message' => $message,
            'conversation_id' => $productId ?? rand(1000, 9999),
            'user_id' => $userId,
            'product' => $productData
        ];

        try {
            $response = Http::timeout(30)->post($this->n8nWebhookUrl, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'bot_response' => $response->json(),
                    'user_message' => $message
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'n8n service unavailable',
                    'status_code' => $response->status()
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send message when admin takes over conversation
     * This disables bot responses and uses admin manual responses instead
     */
    public function handleAdminTakeover($conversationId, $adminId)
    {
        try {
            $conversation = ChatConversation::find($conversationId);
            
            if (!$conversation) {
                return [
                    'success' => false,
                    'message' => 'Conversation not found'
                ];
            }

            $conversation->update([
                'taken_over_by_admin' => true,
                'admin_id' => $adminId,
                'is_admin_active' => true,
                'taken_over_at' => now()
            ]);

            // Create system notification
            ChatMessage::create([
                'conversation_id' => $conversationId,
                'sender_type' => 'system',
                'message' => '👤 Admin sedang membantu Anda. Chatbot otomatis dinonaktifkan.',
                'metadata' => [
                    'system_notification' => true,
                    'type' => 'admin_takeover',
                    'admin_id' => $adminId
                ]
            ]);

            Log::info('Admin takeover handled', [
                'conversation_id' => $conversationId,
                'admin_id' => $adminId
            ]);

            return [
                'success' => true,
                'message' => 'Admin telah mengambil alih konversasi'
            ];

        } catch (\Exception $e) {
            Log::error('Error handling admin takeover: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengambil alih konversasi'
            ];
        }
    }

    /**
     * Send admin response to customer
     */
    public function sendAdminResponse($conversationId, $adminId, $message)
    {
        try {
            $conversation = ChatConversation::find($conversationId);
            
            if (!$conversation) {
                return [
                    'success' => false,
                    'message' => 'Conversation not found'
                ];
            }

            // Save admin message
            $adminMessage = ChatMessage::create([
                'conversation_id' => $conversationId,
                'sender_type' => 'admin',
                'message' => $message,
                'is_admin_reply' => true,
                'is_read_by_user' => false
            ]);

            // Update conversation
            $conversation->update([
                'taken_over_by_admin' => true,
                'admin_id' => $adminId,
                'is_admin_active' => true,
                'needs_admin_response' => false,
                'needs_response_since' => null
            ]);

            Log::info('Admin response sent', [
                'conversation_id' => $conversationId,
                'message_id' => $adminMessage->id,
                'admin_id' => $adminId
            ]);

            return [
                'success' => true,
                'message' => $adminMessage,
                'conversation' => $conversation
            ];

        } catch (\Exception $e) {
            Log::error('Error sending admin response: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengirim pesan'
            ];
        }
    }

    /**
     * Notify customer that they need immediate admin response
     * This shows a notification to admin that customer needs help
     */
    public function notifyCustomerNeedsResponse($conversationId, $reason = null)
    {
        try {
            $conversation = ChatConversation::find($conversationId);
            
            if (!$conversation) {
                return [
                    'success' => false,
                    'message' => 'Conversation not found'
                ];
            }

            $conversation->update([
                'is_escalated' => true,
                'escalated_at' => now(),
                'needs_admin_response' => true,
                'needs_response_since' => now(),
                'escalation_reason' => $reason ?? 'Customer requested immediate admin response'
            ]);

            // Create system notification
            ChatMessage::create([
                'conversation_id' => $conversationId,
                'sender_type' => 'system',
                'message' => '🔔 Admin sedang diberitahu untuk membantu Anda',
                'metadata' => [
                    'system_notification' => true,
                    'type' => 'escalation_notification',
                    'reason' => $reason
                ]
            ]);

            Log::info('Customer needs response notification sent', [
                'conversation_id' => $conversationId,
                'reason' => $reason
            ]);

            return [
                'success' => true,
                'message' => 'Admin telah diberitahu'
            ];

        } catch (\Exception $e) {
            Log::error('Error notifying needs response: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengirim notifikasi'
            ];
        }
    }

    /**
     * Toggle chatbot enabled/disabled status for a conversation
     */
    public function toggleChatbotStatus($conversationId, $enabled = true)
    {
        try {
            $conversation = ChatConversation::find($conversationId);
            
            if (!$conversation) {
                return [
                    'success' => false,
                    'message' => 'Conversation not found'
                ];
            }

            $conversation->update([
                'is_admin_active' => !$enabled  // is_admin_active means manual, not bot
            ]);

            $status = $enabled ? 'enabled' : 'disabled';
            
            ChatMessage::create([
                'conversation_id' => $conversationId,
                'sender_type' => 'system',
                'message' => "Chatbot otomatis telah di-{$status}",
                'metadata' => [
                    'system_notification' => true,
                    'type' => 'chatbot_toggle',
                    'status' => $status
                ]
            ]);

            Log::info('Chatbot status toggled', [
                'conversation_id' => $conversationId,
                'enabled' => $enabled
            ]);

            return [
                'success' => true,
                'message' => "Chatbot telah di-{$status}",
                'status' => $status
            ];

        } catch (\Exception $e) {
            Log::error('Error toggling chatbot status: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengubah status chatbot'
            ];
        }
    }

    /**
     * Release conversation back to bot
     */
    public function releaseConversationToBot($conversationId)
    {
        try {
            $conversation = ChatConversation::find($conversationId);
            
            if (!$conversation) {
                return [
                    'success' => false,
                    'message' => 'Conversation not found'
                ];
            }

            $conversation->update([
                'taken_over_by_admin' => false,
                'is_admin_active' => false,
                'admin_id' => null,
                'taken_over_at' => null
            ]);

            ChatMessage::create([
                'conversation_id' => $conversationId,
                'sender_type' => 'system',
                'message' => 'Chatbot otomatis telah mengambil alih kembali',
                'metadata' => [
                    'system_notification' => true,
                    'type' => 'bot_resumed'
                ]
            ]);

            Log::info('Conversation released back to bot', [
                'conversation_id' => $conversationId
            ]);

            return [
                'success' => true,
                'message' => 'Conversation released to bot'
            ];

        } catch (\Exception $e) {
            Log::error('Error releasing conversation to bot: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal melepaskan ke bot'
            ];
        }
    }
}