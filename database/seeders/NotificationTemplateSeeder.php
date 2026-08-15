<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // Order Related Notifications
            [
                'type' => 'order_created',
                'name' => 'Order Created',
                'description' => 'Sent when a new order is created by customer',
                'channel' => 'email',
                'subject' => 'Order Berhasil Dibuat - #{order_number}',
                'template' => 'emails.notifications.order_created',
                'title_template' => 'Order Berhasil Dibuat',
                'message_template' => 'Order #{order_number} telah berhasil dibuat dengan total {total_amount}',
                'available_variables' => json_encode(['order_id', 'order_number', 'customer_name', 'total_amount', 'items_count']),
                'action_url_template' => '/orders/{order_id}',
                'action_text' => 'Lihat Detail Order',
                'is_active' => true,
            ],
            [
                'type' => 'order_approved',
                'name' => 'Order Approved',
                'description' => 'Sent when order is approved by admin',
                'channel' => 'email',
                'subject' => 'Order Anda Telah Disetujui - #{order_number}',
                'template' => 'emails.notifications.order_approved',
                'title_template' => 'Order Disetujui',
                'message_template' => 'Order #{order_number} Anda telah disetujui dan sedang diproses',
                'available_variables' => json_encode(['order_id', 'order_number', 'customer_name', 'estimated_completion']),
                'action_url_template' => '/orders/{order_id}',
                'action_text' => 'Track Order',
                'is_active' => true,
            ],
            [
                'type' => 'order_rejected',
                'name' => 'Order Rejected',
                'description' => 'Sent when order is rejected by admin',
                'channel' => 'email',
                'subject' => 'Order Anda Ditolak - #{order_number}',
                'template' => 'emails.notifications.order_rejected',
                'title_template' => 'Order Ditolak',
                'message_template' => 'Maaf, order #{order_number} Anda ditolak. Alasan: {rejection_reason}',
                'available_variables' => json_encode(['order_id', 'order_number', 'customer_name', 'rejection_reason']),
                'action_url_template' => '/orders/{order_id}',
                'action_text' => 'Lihat Detail',
                'is_active' => true,
            ],
            [
                'type' => 'order_completed',
                'name' => 'Order Completed',
                'description' => 'Sent when order is marked as completed',
                'channel' => 'email',
                'subject' => 'Order Selesai - #{order_number}',
                'template' => 'emails.notifications.order_completed',
                'title_template' => 'Order Selesai',
                'message_template' => 'Order #{order_number} telah selesai dan siap diambil/dikirim',
                'available_variables' => json_encode(['order_id', 'order_number', 'customer_name', 'pickup_info']),
                'action_url_template' => '/orders/{order_id}',
                'action_text' => 'Lihat Order',
                'is_active' => true,
            ],
            
            // Payment Related Notifications
            [
                'type' => 'payment_received',
                'name' => 'Payment Received',
                'description' => 'Sent when payment is confirmed',
                'channel' => 'email',
                'subject' => 'Pembayaran Diterima - #{order_number}',
                'template' => 'emails.notifications.payment_received',
                'title_template' => 'Pembayaran Diterima',
                'message_template' => 'Pembayaran untuk order #{order_number} sebesar {amount} telah diterima',
                'available_variables' => json_encode(['order_id', 'order_number', 'customer_name', 'amount', 'payment_method']),
                'action_url_template' => '/orders/{order_id}',
                'action_text' => 'Lihat Invoice',
                'is_active' => true,
            ],
            [
                'type' => 'payment_pending',
                'name' => 'Payment Pending',
                'description' => 'Reminder for pending payment',
                'channel' => 'email',
                'subject' => 'Menunggu Pembayaran - #{order_number}',
                'template' => 'emails.notifications.payment_pending',
                'title_template' => 'Menunggu Pembayaran',
                'message_template' => 'Order #{order_number} menunggu pembayaran sebesar {amount}',
                'available_variables' => json_encode(['order_id', 'order_number', 'customer_name', 'amount', 'due_date']),
                'action_url_template' => '/orders/{order_id}/payment',
                'action_text' => 'Bayar Sekarang',
                'is_active' => true,
            ],
            
            // Custom Design Notifications
            [
                'type' => 'custom_design_uploaded',
                'name' => 'Custom Design Uploaded',
                'description' => 'Sent when customer uploads custom design',
                'channel' => 'email',
                'subject' => 'Desain Custom Diterima - #{order_number}',
                'template' => 'emails.notifications.custom_design_uploaded',
                'title_template' => 'Desain Custom Diterima',
                'message_template' => 'Desain custom untuk order #{order_number} telah diterima dan akan direview',
                'available_variables' => json_encode(['order_id', 'order_number', 'customer_name', 'design_count']),
                'action_url_template' => '/admin/custom-designs/{order_id}',
                'action_text' => 'Review Desain',
                'is_active' => true,
            ],
            [
                'type' => 'custom_design_approved',
                'name' => 'Custom Design Approved',
                'description' => 'Sent when custom design is approved',
                'channel' => 'email',
                'subject' => 'Desain Anda Disetujui - #{order_number}',
                'template' => 'emails.notifications.custom_design_approved',
                'title_template' => 'Desain Disetujui',
                'message_template' => 'Desain custom untuk order #{order_number} telah disetujui dan akan diproduksi',
                'available_variables' => json_encode(['order_id', 'order_number', 'customer_name', 'approval_notes']),
                'action_url_template' => '/orders/{order_id}',
                'action_text' => 'Lihat Desain',
                'is_active' => true,
            ],
            
            // Admin Notifications
            [
                'type' => 'new_order_admin',
                'name' => 'New Order (Admin)',
                'description' => 'Notify admin when new order is placed',
                'channel' => 'email',
                'subject' => '[ADMIN] Order Baru - #{order_number}',
                'template' => 'emails.notifications.new_order_admin',
                'title_template' => 'Order Baru Masuk',
                'message_template' => 'Order baru #{order_number} dari {customer_name} dengan total {total_amount}',
                'available_variables' => json_encode(['order_id', 'order_number', 'customer_name', 'total_amount', 'items_count']),
                'action_url_template' => '/admin/orders/{order_id}',
                'action_text' => 'Proses Order',
                'is_active' => true,
            ],
            [
                'type' => 'low_stock_alert',
                'name' => 'Low Stock Alert',
                'description' => 'Alert admin when product stock is low',
                'channel' => 'email',
                'subject' => '[ALERT] Stok Rendah - {product_name}',
                'template' => 'emails.notifications.low_stock_alert',
                'title_template' => 'Stok Rendah',
                'message_template' => 'Produk {product_name} memiliki stok rendah: {current_stock} unit',
                'available_variables' => json_encode(['product_id', 'product_name', 'current_stock', 'minimum_stock']),
                'action_url_template' => '/admin/products/{product_id}',
                'action_text' => 'Update Stok',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            // Use updateOrCreate to avoid duplicate errors
            DB::table('notification_templates')->updateOrInsert(
                ['type' => $template['type'], 'channel' => $template['channel']],
                array_merge($template, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
        
        $this->command->info('Notification templates seeded successfully!');
    }
}
