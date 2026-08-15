import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Core
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/bootstrap.js',
                
                // Livewire
                'resources/css/livewire/chatbot-customer.css',

                // Components
                'resources/css/components/navbar.css',
                'resources/css/components/footer.css',
                'resources/css/components/product-card.css',
                'resources/js/components/navbar.js',
                'resources/css/components/notification-dropdown.css',
                'resources/js/components/notification-dropdown.js',
                
                
                // Admin
                'resources/css/admin/login.css',
                'resources/css/admin/dashboard.css',
                'resources/css/admin/history.css',
                'resources/css/admin/profile.css',
                'resources/css/admin/customer-detail.css',
                'resources/css/admin/management-order.css',
                'resources/css/admin/user-management.css',
                'resources/css/admin/product-management.css',
                'resources/css/admin/modern-add-product.css',
                'resources/css/admin/all-products.css',
                'resources/css/admin/chatbot.css',
                'resources/css/admin/order-list.css',
                'resources/js/admin/login.js',
                'resources/js/admin/layout.js',
                'resources/js/admin/all-products.js',
                'resources/js/admin/dashboard-charts.js',
                'resources/js/admin/user-management.js',
                'resources/js/admin/product-management.js',
                'resources/js/admin/modern-add-product.js',
                'resources/css/admin/analytics.css',
                'resources/js/admin/analytics.js',
                'resources/css/admin/chatbot-management.css',
                'resources/js/admin/chatbot-management.js',
                'resources/css/admin/admin-notifications.css',
                
                // Auth
                'resources/css/auth/forgot-password.css',
                'resources/css/auth/auth-layout.css',
                
                // Guest/Homepage
                'resources/js/guest/home.js',
                'resources/css/guest/home.css',
                'resources/css/guest/auth-layout.css',
                'resources/css/guest/login.css',
                'resources/css/guest/product-detail.css',
                'resources/css/guest/catalog.css',
                'resources/css/guest/catalog-inline.css',
                'resources/css/guest/custom-design.css',
                'resources/css/guest/alamat.css',
                'resources/css/guest/chatpage.css',
                    // 'resources/js/guest/about.js', // removed: file not found
                    // 'resources/js/guest/alamat.js', // removed: file not found
                    // 'resources/js/guest/other-info.js', // removed: file not found
                    // 'resources/js/guest/guest.css', // removed: file not found
                'resources/js/guest/auth-layout.js',
                'resources/js/guest/login.js',
                'resources/js/guest/product-slider.js',
                'resources/js/guest/product-detail.js',
                'resources/js/guest/catalog.js',
                'resources/js/guest/product-card-carousel.js',
                'resources/js/guest/custom-design.js',
                'resources/css/guest/about.css',
                
                
                // Customer
                'resources/css/customer/shared.css',
                'resources/css/customer/profile-form.css',
                'resources/css/customer/all-product.css',
                'resources/css/customer/Pembayaran.css',
                'resources/css/customer/Notifikasi.css',
                // 'resources/js/customer/chatbot.js', // removed: file not found
                'resources/js/customer/profile-dropdown.js',
                'resources/js/customer/cart.js',
                // 'resources/js/guest/guest.css', // removed: file not found
            ],
            refresh: true,
        }),
    ],
    build: {
    outDir: 'public/build',
    emptyOutDir: true,
},

});
