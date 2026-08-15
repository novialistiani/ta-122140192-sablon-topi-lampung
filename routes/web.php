<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProductManagementController;
use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\Admin\ColorManagementController;
use App\Http\Controllers\Admin\SubcategoryManagementController;
use App\Http\Controllers\Admin\CustomDesignPriceController;
use App\Http\Controllers\Admin\AdminChatController;
use App\Http\Controllers\Admin\ChatbotSettingsController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ResendWebhookController;
use Illuminate\Support\Facades\Auth;


use Illuminate\Support\Facades\Http;
//use Illuminate\Support\Facades\Route;

// Resend Webhook Endpoint (must be before any auth middleware)
Route::post('/webhooks/resend', [ResendWebhookController::class, 'handle'])
    ->name('webhooks.resend')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/test-n8n-integration', function () {
    try {
        // Gunakan URL langsung dulu untuk testing
        $n8nUrl = 'http://localhost:5678/webhook/chatbot';
        
        $response = Http::timeout(30)->post($n8nUrl, [
            'message' => 'harga produk ini berapa?',
            'conversation_id' => 1,
            'product' => [
                'name' => 'Topi Sablon Bintang', 
                'price' => 65000
            ],
            'user_id' => 1
        ]);

        if ($response->successful()) {
            return response()->json([
                'status' => 'success',
                'data' => $response->json(),
                'n8n_response_time' => $response->transferStats->getTransferTime() . 's',
                'n8n_url_used' => $n8nUrl
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'n8n request failed',
                'status_code' => $response->status(),
                'error' => $response->body(),
                'n8n_url_used' => $n8nUrl
            ], 500);
        }

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Exception occurred',
            'error' => $e->getMessage(),
            'n8n_url' => $n8nUrl ?? 'not set'
        ], 500);
    }
});
// Test routes untuk n8n integration
Route::get('/test-n8n-simple', function () {
    $n8nUrl = 'http://localhost:5678/webhook/chatbot';
    
    try {
        $response = Http::timeout(10)->post($n8nUrl, [
            'message' => 'test connection from Laravel',
            'conversation_id' => 999,
            'user_id' => 1
        ]);
        
        return response()->json([
            'status' => $response->status(),
            'success' => $response->successful(),
            'response' => $response->json(),
            'url_used' => $n8nUrl
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'url_used' => $n8nUrl
        ], 500);
    }
});

Route::get('/debug-n8n-config', function () {
    return response()->json([
        'n8n_webhook_url' => config('services.n8n.webhook_url'),
        'env_n8n_url' => env('N8N_WEBHOOK_URL'),
        'all_services_config' => config('services.n8n'),
        'is_n8n_url_string' => is_string(config('services.n8n.webhook_url')),
        'n8n_url_length' => config('services.n8n.webhook_url') ? strlen(config('services.n8n.webhook_url')) : 0
    ]);
});

Route::get('/verify-config', function () {
    // Test different ways to get the config
    $tests = [
        'env_direct' => env('N8N_WEBHOOK_URL'),
        'config_services' => config('services.n8n.webhook_url'),
        'config_direct' => config('services.n8n'),
        'is_null' => is_null(config('services.n8n.webhook_url')),
        'is_string' => is_string(config('services.n8n.webhook_url')),
    ];
    
    // Test actual HTTP call if config is available
    if (config('services.n8n.webhook_url')) {
        try {
            $response = Http::timeout(5)->post(config('services.n8n.webhook_url'), ['message' => 'test']);
            $tests['http_test'] = $response->status();
        } catch (\Exception $e) {
            $tests['http_test'] = $e->getMessage();
        }
    }
    
    return response()->json($tests);
});

// Route original test
Route::get('/test-n8n-integration', function () {
    try {
        // Gunakan URL langsung dulu untuk testing
        $n8nUrl = 'http://localhost:5678/webhook/chatbot';
        
        $response = Http::timeout(30)->post($n8nUrl, [
            'message' => 'harga produk ini berapa?',
            'conversation_id' => 1,
            'product' => [
                'name' => 'Topi Sablon Bintang', 
                'price' => 65000
            ],
            'user_id' => 1
        ]);

        if ($response->successful()) {
            return response()->json([
                'status' => 'success',
                'data' => $response->json(),
                'n8n_response_time' => $response->transferStats->getTransferTime() . 's',
                'n8n_url_used' => $n8nUrl
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'n8n request failed',
                'status_code' => $response->status(),
                'error' => $response->body(),
                'n8n_url_used' => $n8nUrl
            ], 500);
        }

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Exception occurred',
            'error' => $e->getMessage(),
            'n8n_url' => $n8nUrl ?? 'not set'
        ], 500);
    }
});

Route::post('/test-product-chat', function (Request $request) {
    $request->validate([
        'product_id' => 'required|integer',
        'product_name' => 'required|string',
        'product_price' => 'required|numeric',
        'message' => 'required|string'
    ]);

    try {
        $response = Http::post(config('services.n8n.webhook_url'), [
            'message' => $request->message,
            'conversation_id' => $request->product_id,
            'product' => [
                'name' => $request->product_name,
                'price' => $request->product_price
            ],
            'user_id' => Auth::id() ?? 1
        ]);

        return response()->json([
            'status' => $response->successful() ? 'success' : 'error',
            'data' => $response->json(),
            'product' => [
                'id' => $request->product_id,
                'name' => $request->product_name
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// Test routes untuk n8n integration
Route::get('/test-chat-interface', function () {
    return view('chat.test-interface');
});

// Customer chatbot polling routes (untuk menerima pesan admin)
Route::middleware(['auth'])->group(function () {
    Route::get('/chat/new-messages', [ChatController::class, 'getNewMessages'])->name('chat.new-messages');
    Route::get('/chat/status', [ChatController::class, 'getConversationStatus'])->name('chat.status');
});

// ===== API Routes untuk Stock & Product Info (untuk future use) =====
Route::prefix('api')->group(function () {
    // Get fresh stock information untuk product spesifik
    // Usage: GET /api/product/{id}/stock
    Route::get('/product/{id}/stock', function ($id) {
        try {
            $chatBotService = app(\App\Services\ChatBotService::class);
            $stockInfo = $chatBotService->getProductStockInfo($id);
            
            if ($stockInfo['success']) {
                return response()->json($stockInfo, 200);
            } else {
                return response()->json($stockInfo, 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    });

    // Notification API routes
    Route::middleware('auth')->group(function () {
        Route::get('/notifications', [App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
        Route::post('/notifications/{id}/read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
        Route::post('/notifications/read-selected', [App\Http\Controllers\Api\NotificationController::class, 'markSelectedAsRead']);
    });
    
    // Chatbot API routes
    Route::get('/chatbot/history', [App\Http\Controllers\Api\ChatbotApiController::class, 'getHistory'])->name('api.chatbot.history');
    Route::post('/chatbot/send', [App\Http\Controllers\Api\ChatbotApiController::class, 'sendMessage'])->name('api.chatbot.send');
    Route::get('/chatbot/unread-count', [App\Http\Controllers\Api\ChatbotApiController::class, 'getUnreadCount'])->name('api.chatbot.unread-count');
    Route::post('/chatbot/mark-read', [App\Http\Controllers\Api\ChatbotApiController::class, 'markAsRead'])->name('api.chatbot.mark-read');
});






Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/all-products', [ProductController::class, 'allProducts'])->name('all-products');

// Debug route untuk cek product
Route::get('/debug/product/{id}', function ($id) {
    $product = \App\Models\Product::find($id);
    if (!$product) {
        return response()->json(['error' => 'Product not found', 'id' => $id], 404);
    }
    return response()->json([
        'id' => $product->id,
        'name' => $product->name,
        'is_active' => $product->is_active,
        'price' => $product->price,
        'stock' => $product->stock,
        'variants_count' => $product->variants()->count(),
    ]);
});

// Debug route untuk check semua products
Route::get('/debug/all-products', function () {
    $products = \App\Models\Product::select('id', 'name', 'is_active', 'price', 'stock')->orderBy('id')->get();
    return response()->json([
        'total' => $products->count(),
        'active_count' => $products->where('is_active', true)->count(),
        'products' => $products
    ]);
});

// Product detail route - changed from /public/detail to /product-detail to avoid folder conflict
Route::get('/product-detail', [ProductController::class, 'detail'])->name('product.detail');

// Keep both routes to ensure backward compatibility
Route::get('/public/detail', [ProductController::class, 'detail'])->name('product.detail.old');

// About Us page
Route::get('/tentang-kami', function () {
    return view('pages.about');
})->name('about');

// Catalog routes
Route::get('/catalog/{category}', [CatalogController::class, 'index'])->name('catalog');

// Public API for custom design prices
Route::get('/api/custom-design-prices', [App\Http\Controllers\Admin\CustomDesignPriceController::class, 'getPrices'])->name('api.custom-design-prices');

// API for product-specific custom design prices (for customer page)
Route::get('/api/product-custom-design-prices/{productId}', [App\Http\Controllers\Admin\CustomDesignPriceController::class, 'getProductPrices'])->name('api.product-custom-design-prices');


// Chat Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('chat')->group(function () {
        Route::get('/product/{productId}', [ChatController::class, 'startChat'])->name('chat.start');
        Route::get('/history', [ChatController::class, 'getChatHistory'])->name('chat.history');
        Route::get('/conversation/{conversationId}', [ChatController::class, 'getConversation'])->name('chat.conversation');
        Route::post('/send-message', [ChatController::class, 'sendMessage'])->name('chat.send');
    });
});

// Customer authenticated routes
Route::middleware(['auth'])->group(function () {
    // Profile routes (available for both customer and admin viewing)
    Route::get('/profile', [CustomerProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [CustomerProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/confirm-email/{token}', [CustomerProfileController::class, 'confirmEmailChange'])->name('profile.confirm-email-change');
    Route::post('/profile/avatar', [CustomerProfileController::class, 'updateAvatar'])->name('profile.update-avatar');
    Route::delete('/profile/avatar', [CustomerProfileController::class, 'deleteAvatar'])->name('profile.delete-avatar');
    Route::post('/profile/password', [CustomerProfileController::class, 'updatePassword'])->name('profile.update-password');
    
    // Address management routes
    Route::post('/profile/address', [CustomerProfileController::class, 'storeAddress'])->name('profile.address.store');
    Route::put('/profile/address/{id}', [CustomerProfileController::class, 'updateAddress'])->name('profile.address.update');
    Route::delete('/profile/address/{id}', [CustomerProfileController::class, 'deleteAddress'])->name('profile.address.delete');
    Route::post('/profile/address/{id}/set-primary', [CustomerProfileController::class, 'setPrimaryAddress'])->name('profile.address.set-primary');
    
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])
        ->name('dashboard');

    // Notification routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::post('/{id}/archive', [App\Http\Controllers\NotificationController::class, 'archive'])->name('archive');
    });

    // Customer-only routes (cart, checkout, orders, custom design)
    Route::middleware(['customer.only'])->group(function () {
        Route::get('/keranjang', [CustomerController::class, 'keranjang'])
            ->name('keranjang');
        Route::post('/keranjang', [CustomerController::class, 'addToCart'])
            ->name('cart.add');
        Route::patch('/keranjang/{key}', [CustomerController::class, 'updateCartItem'])
            ->name('cart.update');
        Route::delete('/keranjang/{key}', [CustomerController::class, 'removeCartItem'])
            ->name('cart.remove');
        Route::delete('/keranjang-bulk', [CustomerController::class, 'removeSelected'])
            ->name('cart.bulk-remove');
        
        // Buy Now - Direct order creation
        Route::post('/buy-now', [CustomerController::class, 'buyNow'])
            ->name('buy-now');

        Route::post('/checkout', [CustomerController::class, 'checkout'])
            ->name('checkout');

        Route::get('/alamat', [CustomerController::class, 'alamat'])
            ->name('alamat');
        Route::post('/alamat/select', [CustomerController::class, 'selectAddress'])
            ->name('alamat.select');

        Route::get('/pemesanan', [CustomerController::class, 'pemesanan'])
            ->name('pemesanan');
        Route::post('/pemesanan/select', [CustomerController::class, 'selectShipping'])
            ->name('pemesanan.select');

        Route::get('/pembayaran', [CustomerController::class, 'pembayaran'])
            ->name('pembayaran');
        Route::get('/pembayaran/direct', [CustomerController::class, 'pembayaranDirect'])
            ->name('pembayaran.direct');
        Route::post('/pembayaran/generate-va', [CustomerController::class, 'generateVA'])
            ->name('pembayaran.generate-va');
        Route::post('/pembayaran/process-order', [CustomerController::class, 'processOrder'])
            ->name('pembayaran.process');

        Route::get('/order-list', [CustomerController::class, 'orderList'])
            ->name('order-list');
        Route::get('/payment-status', [CustomerController::class, 'paymentStatus'])
            ->name('payment-status');
        
        Route::get('/order-detail/{type}/{id}', [CustomerController::class, 'orderDetail'])
            ->name('order-detail');
        
        Route::post('/order/{type}/{id}/cancel', [CustomerController::class, 'cancelOrder'])
            ->name('order.cancel');

        // Deprecated Midtrans payment routes removed - System now uses Virtual Account
        // See CLEANUP_REPORT.md for details

        Route::get('/custom-design', [CustomerController::class, 'customDesign'])
            ->name('custom-design');

        Route::post('/custom-design', [CustomerController::class, 'storeCustomDesign'])
            ->name('custom-design.store');

        Route::get('/chatpage', [CustomerController::class, 'chatpage'])
            ->name('chatpage');
        Route::get('/notifikasi', [CustomerController::class, 'notifikasi'])
            ->name('notifikasi');
        
        // API endpoints untuk realtime dashboard updates
        Route::get('/api/dashboard/stats', [CustomerController::class, 'getDashboardStats'])
            ->name('api.customer.dashboard-stats');
        Route::get('/api/dashboard/recent-orders', [CustomerController::class, 'getRecentOrdersData'])
            ->name('api.customer.dashboard-orders');
    });

    // Download route - accessible by both customer and admin (outside customer.only middleware)
    Route::get('/custom-design/download/{uploadId}', [CustomerController::class, 'downloadCustomDesignFile'])
        ->name('custom-design.download');

    // Chatbot route - redirect to chatpage
    Route::get('/chatbot', [CustomerController::class, 'chatpage'])
        ->name('chatbot');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});


Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])
    ->middleware('guest')
    ->name('google.login');

Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])
    ->name('google.callback');

// Admin Routes
Route::prefix('admin')->group(function () {
    // Routes untuk admin yang belum login
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
    });

    // Routes untuk admin yang sudah login
    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
        
        // Dashboard API endpoints untuk real-time updates
        Route::get('/api/dashboard/stats', [App\Http\Controllers\Admin\DashboardController::class, 'getStats'])->name('api.dashboard.stats');
        Route::get('/api/dashboard/sales-data', [App\Http\Controllers\Admin\DashboardController::class, 'getSalesData'])->name('api.dashboard.sales');
        Route::get('/api/dashboard/recent-orders', [App\Http\Controllers\Admin\DashboardController::class, 'getRecentOrders'])->name('api.dashboard.orders');
        Route::get('/api/dashboard/top-products', [App\Http\Controllers\Admin\DashboardController::class, 'getTopProducts'])->name('api.dashboard.products');
        
        // Analytics & Reports
        Route::get('/analytic', [AnalyticsController::class, 'index'])->name('admin.analytics');
        Route::get('/api/analytics/sales-overview', [AnalyticsController::class, 'getSalesOverview'])->name('admin.api.analytics.sales-overview');
        Route::get('/api/analytics/sales-trend', [AnalyticsController::class, 'getSalesTrendData'])->name('admin.api.analytics.sales-trend');
        Route::get('/api/analytics/order-status', [AnalyticsController::class, 'getOrderStatusDistribution'])->name('admin.api.analytics.order-status');
        Route::get('/api/analytics/customer', [AnalyticsController::class, 'getCustomerAnalytics'])->name('admin.api.analytics.customer');
        Route::get('/api/analytics/conversion-funnel', [AnalyticsController::class, 'getConversionFunnel'])->name('admin.api.analytics.conversion-funnel');
        Route::get('/api/analytics/check-changes', [AnalyticsController::class, 'checkAnalyticsChanges'])->name('admin.api.analytics.check-changes');
        
        Route::get('/order-list', [OrderManagementController::class, 'index'])->name('admin.order-list');
        Route::get('/order-history', [OrderManagementController::class, 'history'])->name('admin.order-history');
        Route::get('/order-list/{id}/detail', [OrderManagementController::class, 'showDetail'])->name('admin.order.detail');
        Route::get('/order-list/export', [OrderManagementController::class, 'export'])->name('admin.order-list.export');
        Route::post('/order-list/{id}/approve', [OrderManagementController::class, 'approve'])->name('admin.order.approve');
        Route::post('/order-list/{id}/reject', [OrderManagementController::class, 'reject'])->name('admin.order.reject');
        Route::patch('/order-list/{id}/status', [OrderManagementController::class, 'updateStatus'])->name('admin.order.update-status');
        Route::post('/order-list/{id}/mark-payment-received', [OrderManagementController::class, 'markPaymentReceived'])->name('admin.order.mark-payment-received');
        Route::get('/management-users', [UserManagementController::class, 'index'])->name('admin.management-users');
        Route::get('/management-users/customer/{id}', [UserManagementController::class, 'showCustomerDetail'])->name('admin.customer-detail');
        Route::get('/management-users/customer/{id}/export-pdf', [UserManagementController::class, 'exportCustomerPDF'])->name('admin.customer-export-pdf');
        Route::get('/management-users/customer/{id}/export-excel', [UserManagementController::class, 'exportCustomerExcel'])->name('admin.customer-export-excel');
        Route::get('/management-users/export-admins', [UserManagementController::class, 'exportAdmins'])->name('admin.management-users.export-admins');
        Route::get('/management-users/export-customers', [UserManagementController::class, 'exportCustomers'])->name('admin.management-users.export-customers');
        
        // Finance & Wallet
        Route::get('/finance', [App\Http\Controllers\Admin\FinanceController::class, 'index'])->name('admin.finance.index');
        Route::get('/finance/export', [App\Http\Controllers\Admin\FinanceController::class, 'export'])->name('admin.finance.export');
        
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs');
        Route::get('/activity-logs/export', [ActivityLogController::class, 'export'])->name('admin.activity-logs.export');
        Route::get('/history', [ActivityLogController::class, 'history'])->name('admin.history');
        Route::get('/history/{id}', [ActivityLogController::class, 'historyDetail'])->name('admin.history.detail');
        
        // Admin Notification routes
        Route::prefix('notifications')->name('admin.notifications.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AdminNotificationController::class, 'index'])->name('index');
            Route::post('/{id}/read', [App\Http\Controllers\Admin\AdminNotificationController::class, 'markAsRead'])->name('read');
            Route::post('/read-all', [App\Http\Controllers\Admin\AdminNotificationController::class, 'markAllAsRead'])->name('read-all');
        });
        
        // Product Management
        Route::get('/management-product', [ProductManagementController::class, 'index'])->name('admin.management-product');
        Route::get('/all-products', [ProductManagementController::class, 'allProducts'])->name('admin.all-products');
        Route::get('/all-products/detail/{id}', [ProductManagementController::class, 'productDetail'])->name('admin.all-products.detail');

        // Chatbot Admin Management
        Route::prefix('chatbot')->name('chatbot.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AdminChatController::class, 'index'])->name('index');
            Route::get('/conversation/{id}', [App\Http\Controllers\Admin\AdminChatController::class, 'getConversation'])->name('conversation.show');
            Route::post('/conversation/{id}/take-over', [App\Http\Controllers\Admin\AdminChatController::class, 'takeOverConversation'])->name('conversation.takeover');
            Route::post('/conversation/{id}/send-message', [App\Http\Controllers\Admin\AdminChatController::class, 'sendAdminMessage'])->name('conversation.send');
            Route::post('/conversation/{id}/needs-response', [App\Http\Controllers\Admin\AdminChatController::class, 'markNeedsAdminResponse'])->name('conversation.needs-response');
            Route::post('/conversation/{id}/close', [App\Http\Controllers\Admin\AdminChatController::class, 'closeConversation'])->name('conversation.close');
            Route::post('/conversation/{id}/release', [App\Http\Controllers\Admin\AdminChatController::class, 'releaseConversation'])->name('conversation.release');
            Route::post('/conversation/{id}/mark-read', [App\Http\Controllers\Admin\AdminChatController::class, 'markConversationAsRead'])->name('conversation.mark-read');
            Route::delete('/conversation/{id}/delete', [App\Http\Controllers\Admin\AdminChatController::class, 'deleteConversation'])->name('conversation.delete');
            Route::post('/conversation/{id}/clear-history', [App\Http\Controllers\Admin\AdminChatController::class, 'clearChatHistory'])->name('conversation.clear-history');
            
            // Chatbot Settings
            Route::get('/settings', [App\Http\Controllers\Admin\ChatbotSettingsController::class, 'index'])->name('settings');
            Route::post('/settings/toggle-global', [App\Http\Controllers\Admin\ChatbotSettingsController::class, 'toggleGlobal'])->name('settings.toggle-global');
            Route::post('/settings/toggle-product/{productId}', [App\Http\Controllers\Admin\ChatbotSettingsController::class, 'toggleProduct'])->name('settings.toggle-product');
            Route::post('/settings/reset', [App\Http\Controllers\Admin\ChatbotSettingsController::class, 'reset'])->name('settings.reset');
            
            // API endpoints
            Route::get('/api/unread-count', [App\Http\Controllers\Admin\AdminChatController::class, 'getUnreadCount'])->name('api.unread-count');
            Route::get('/api/needs-attention', [App\Http\Controllers\Admin\AdminChatController::class, 'getConversationsNeedingAttention'])->name('api.needs-attention');
            Route::get('/api/settings', [App\Http\Controllers\Admin\ChatbotSettingsController::class, 'getSettings'])->name('api.settings');
            Route::get('/api/products', [App\Http\Controllers\Admin\ChatbotSettingsController::class, 'getProductsList'])->name('api.products');
            Route::get('/api/product/{productId}/status', [App\Http\Controllers\Admin\ChatbotSettingsController::class, 'getProductStatus'])->name('api.product-status');
        });
        
        // Custom Design Prices Management
        Route::get('/custom-design-prices', [CustomDesignPriceController::class, 'index'])->name('admin.custom-design-prices');
        Route::post('/custom-design-prices/init', [CustomDesignPriceController::class, 'initializeDefaults'])->name('admin.custom-design-prices.init');
        
        Route::get('/profile', [ProfileController::class, 'index'])->name('admin.profile');
        Route::post('/profile', [ProfileController::class, 'update'])->name('admin.profile.update');
        Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('admin.profile.update-avatar');
        Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('admin.profile.delete-avatar');
        
        // API routes for user management
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/admins', [UserManagementController::class, 'getAdmins'])->name('admins.index');
            Route::post('/admins', [UserManagementController::class, 'storeAdmin'])->name('admins.store');
            Route::get('/admins/{id}', [UserManagementController::class, 'getAdmin'])->name('admins.show');
            Route::put('/admins/{id}', [UserManagementController::class, 'updateAdmin'])->name('admins.update');
            Route::delete('/admins/{id}', [UserManagementController::class, 'destroyAdmin'])->name('admins.destroy');
            
            Route::get('/customers', [UserManagementController::class, 'getCustomers'])->name('customers.index');
            Route::post('/customers', [UserManagementController::class, 'storeCustomer'])->name('customers.store');
            Route::get('/customers/{id}', [UserManagementController::class, 'getCustomer'])->name('customers.show');
            Route::put('/customers/{id}', [UserManagementController::class, 'updateCustomer'])->name('customers.update');
            Route::delete('/customers/{id}', [UserManagementController::class, 'destroyCustomer'])->name('customers.destroy');
            
            // Product Management API
            Route::get('/products', [ProductManagementController::class, 'getProducts'])->name('products.index');
            Route::get('/products/export', [ProductManagementController::class, 'export'])->name('products.export');
            Route::get('/products/{id}/orders', [ProductManagementController::class, 'getProductOrders'])->name('products.orders');
            Route::post('/products', [ProductManagementController::class, 'store'])->name('products.store');
            Route::get('/products/{id}', [ProductManagementController::class, 'show'])->name('products.show');
            Route::put('/products/{id}', [ProductManagementController::class, 'update'])->name('products.update');
            Route::delete('/products/{id}', [ProductManagementController::class, 'destroy'])->name('products.destroy');
            Route::post('/products/bulk-archive', [ProductManagementController::class, 'bulkArchive'])->name('products.bulk-archive');
            Route::post('/products/{id}/toggle-status', [ProductManagementController::class, 'toggleStatus'])->name('products.toggle-status');
            
            // Color Management API
            Route::get('/colors', [ColorManagementController::class, 'index'])->name('colors.index');
            Route::post('/colors', [ColorManagementController::class, 'store'])->name('colors.store');
            Route::delete('/colors/{color}', [ColorManagementController::class, 'destroy'])->name('colors.destroy');
            Route::delete('/colors', [ColorManagementController::class, 'clear'])->name('colors.clear');
            
            // Subcategory Management API
            Route::get('/subcategories', [SubcategoryManagementController::class, 'index'])->name('subcategories.index');
            Route::post('/subcategories', [SubcategoryManagementController::class, 'store'])->name('subcategories.store');
            Route::delete('/subcategories/{slug}', [SubcategoryManagementController::class, 'destroy'])->name('subcategories.destroy');
            
            // Custom Design Prices API
            Route::put('/custom-design-prices/{id}/price', [CustomDesignPriceController::class, 'updatePrice'])->name('custom-design-prices.update-price');
            Route::post('/custom-design-prices/{id}/toggle', [CustomDesignPriceController::class, 'toggleStatus'])->name('custom-design-prices.toggle');
            
            // Testing & Debugging API
            Route::get('/test/db-integration', [\App\Http\Controllers\Admin\TestController::class, 'testDbIntegration'])->name('test.db-integration');
            Route::get('/test/db-status', [\App\Http\Controllers\Admin\TestController::class, 'dbStatus'])->name('test.db-status');
        });
        
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    });
});

// Image serving endpoints (public)
Route::prefix('api/images')->group(function () {
    Route::get('/serve', [\App\Http\Controllers\ImageController::class, 'serve'])->name('images.serve');
    Route::get('/url', [\App\Http\Controllers\ImageController::class, 'getUrl'])->name('images.url');
    Route::post('/validate', [\App\Http\Controllers\ImageController::class, 'validate'])->name('images.validate');
});

require __DIR__.'/auth.php';
