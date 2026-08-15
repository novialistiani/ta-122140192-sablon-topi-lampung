<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderApprovalMail;
use App\Mail\OrderApprovedMail;
use App\Mail\OrderRejectionMail;
use App\Models\CustomDesignOrder;
use App\Models\Order;
use App\Models\VirtualAccount;
use App\Exports\OrderExport;
use App\Traits\StockManagementTrait;
use App\Events\OrderApprovedEvent;
use App\Events\OrderRejectedEvent;
use App\Events\OrderCompletedEvent;
use App\Events\PaymentReceivedEvent;
use App\Events\CustomDesignApprovedEvent;
use App\Events\CustomDesignRejectedEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log, Mail};
use Maatwebsite\Excel\Facades\Excel;

class OrderManagementController extends Controller
{
    use StockManagementTrait;
    /**
     * Display order list page
     */
    public function index(Request $request)
    {
        $orderType = $request->get('type', 'all'); // 'all', 'regular', or 'custom'

        if ($orderType === 'all') {
            // Get custom orders separately
            $customOrdersQuery = CustomDesignOrder::with('user')
                ->where('status', '!=', 'completed');

            // Get regular orders separately  
            $regularOrdersQuery = Order::with('user')
                ->where('status', '!=', 'completed');

            // Apply filters to both queries
            if ($request->filled('status')) {
                $customOrdersQuery->where('status', $request->status);
                $regularOrdersQuery->where('status', $request->status);
            }

            // Payment status filter
            if ($request->filled('payment_status')) {
                if ($request->payment_status === 'paid') {
                    $customOrdersQuery->where('payment_status', 'paid');
                    $regularOrdersQuery->where('payment_status', 'paid');
                } elseif ($request->payment_status === 'va_active') {
                    $customOrdersQuery->where('payment_status', 'va_active');
                    $regularOrdersQuery->where('payment_status', 'va_active');
                }
            }

            if ($request->filled('start_date')) {
                $regularOrdersQuery->whereDate('created_at', '>=', $request->start_date);
                $customOrdersQuery->whereDate('created_at', '>=', $request->start_date);
            }
            
            if ($request->filled('end_date')) {
                $regularOrdersQuery->whereDate('created_at', '<=', $request->end_date);
                $customOrdersQuery->whereDate('created_at', '<=', $request->end_date);
            }

            // Get collections and add order_type attribute
            $customOrders = $customOrdersQuery->get()->map(function($order) {
                $order->order_type = 'custom';
                $order->total = $order->total_price; // Ensure total is set
                return $order;
            });
            
            $regularOrders = $regularOrdersQuery->get()->map(function($order) {
                $order->order_type = 'regular';
                // total already exists, no need to map
                return $order;
            });
            
            // Merge both collections
            $allOrders = $customOrders->concat($regularOrders);

            // Apply search after getting collections
            if ($request->filled('search')) {
                $search = strtolower($request->search);
                $allOrders = $allOrders->filter(function($order) use ($search) {
                    return str_contains(strtolower((string)$order->id), $search) ||
                           str_contains(strtolower($order->user->name ?? ''), $search);
                });
            }

            // Sort by created_at
            $allOrders = $allOrders->sortByDesc('created_at')->values();

            // Manual pagination
            $perPage = 25;
            $currentPage = $request->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;

            $orders = new \Illuminate\Pagination\LengthAwarePaginator(
                $allOrders->slice($offset, $perPage),
                $allOrders->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            $viewData = ['orders' => $orders, 'orderType' => 'all'];
            
        } elseif ($orderType === 'custom') {
            $query = CustomDesignOrder::with(['user', 'product', 'uploads'])
                ->orderBy('created_at', 'desc');

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                      ->orWhere('id', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Payment status filter
            if ($request->filled('payment_status')) {
                if ($request->payment_status === 'paid') {
                    $query->where('payment_status', 'paid');
                } elseif ($request->payment_status === 'va_active') {
                    $query->whereHas('user.virtualAccounts', function($q) {
                        $q->where('status', 'pending')
                          ->where('expired_at', '>', now());
                    });
                }
            }

            // Filter by date range (start_date to end_date)
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            $orders = $query->paginate(25);
            $viewData = ['orders' => $orders, 'orderType' => 'custom'];
        } else {
            $query = Order::with(['user'])
                ->orderBy('created_at', 'desc');

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Payment status filter
            if ($request->filled('payment_status')) {
                if ($request->payment_status === 'paid') {
                    $query->where('payment_status', 'paid');
                } elseif ($request->payment_status === 'va_active') {
                    $query->whereHas('user.virtualAccounts', function($q) {
                        $q->where('status', 'pending')
                          ->where('expired_at', '>', now());
                    });
                }
            }

            // Filter by date range (start_date to end_date)
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            $orders = $query->paginate(25);
            $viewData = ['orders' => $orders, 'orderType' => 'regular'];
        }

        return view('admin.management-order', $viewData);
    }

    /**
     * Approve an order
     */
    public function approve(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $orderType = $request->get('type', 'regular');
            
            if ($orderType === 'custom') {
                $order = CustomDesignOrder::findOrFail($id);
            } else {
                $order = Order::findOrFail($id);
            }

            // Deduct stock when order is approved
            $stockDeducted = $this->deductStockForOrder($order, $orderType);
            
            if (!$stockDeducted['success']) {
                throw new \Exception($stockDeducted['message']);
            }

            $paymentDeadline = now()->addHours(24);
            
            $updateData = [
                'status' => 'approved',
                'approved_at' => now(),
                'payment_deadline' => $paymentDeadline,
            ];

            // Using forceFill untuk memastikan kolom terupdate
            $order->forceFill($updateData)->save();

            // Commit transaction BEFORE sending email
            // This ensures order is approved even if email fails
            DB::commit();

            // Dispatch event for notification based on order type
            if ($orderType === 'custom') {
                CustomDesignApprovedEvent::dispatch($order);
            } else {
                OrderApprovedEvent::dispatch($order);
            }

            // Send email notification to customer (outside transaction)
            try {
                if ($order->user) {
                    Mail::to($order->user->email)->send(new OrderApprovedMail(
                        $order, 
                        $orderType, 
                        $order->user->name,
                        $paymentDeadline
                    ));
                    
                    Log::info('Order approval email sent', [
                        'order_id' => $order->id,
                        'order_type' => $orderType,
                        'customer_name' => $order->user->name,
                        'customer_email' => $order->user->email,
                        'payment_deadline' => $paymentDeadline
                    ]);
                    
                    return redirect()->back()->with('success', 'Pesanan berhasil disetujui. Customer memiliki 24 jam untuk melakukan pembayaran. Email notifikasi telah dikirim.');
                } else {
                    Log::warning('User data not found for order #' . $order->id . ', email not sent');
                    return redirect()->back()->with('success', 'Pesanan berhasil disetujui. Customer memiliki 24 jam untuk melakukan pembayaran.');
                }
            } catch (\Exception $emailException) {
                // Email failed but order is already approved
                Log::error('Failed to send approval email (order already approved)', [
                    'order_id' => $order->id,
                    'error' => $emailException->getMessage()
                ]);
                
                return redirect()->back()->with('success', 'Pesanan berhasil disetujui. Customer memiliki 24 jam untuk melakukan pembayaran. (Catatan: Email notifikasi gagal dikirim)');
            }

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to approve order', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Gagal menyetujui pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Reject an order with reason
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $orderType = $request->get('type', 'regular');

        if ($orderType === 'custom') {
            $order = CustomDesignOrder::findOrFail($id);
        } else {
            $order = Order::findOrFail($id);
        }

        // Check if THIS specific order has active VA
        $activeVA = \App\Models\VirtualAccount::where('user_id', $order->user_id)
            ->where('order_id', $id)
            ->where('order_type', $orderType)
            ->where('status', 'pending')
            ->where('expired_at', '>', now())
            ->first();
        
        if ($activeVA) {
            return redirect()->back()->with('error', 'Tidak dapat menolak pesanan karena pesanan ini memiliki Virtual Account aktif. Harap tunggu hingga VA expired atau minta customer membatalkan VA terlebih dahulu.');
        }

        // If order was previously approved, restore stock
        if ($order->status === 'approved') {
            $this->restoreStockForOrder($order, $orderType);
        }

        $order->update([
            'status' => 'rejected',
            'admin_notes' => $request->reason,
            'rejected_at' => now(),
        ]);

        // Dispatch event for notification based on order type
        if ($orderType === 'custom') {
            CustomDesignRejectedEvent::dispatch($order, $request->reason ?? '');
        } else {
            OrderRejectedEvent::dispatch($order, $request->reason ?? '');
        }

        // Send rejection email notification
        $emailSent = false;
        try {
            Mail::to($order->user->email)->send(new OrderRejectionMail($order, $request->reason, $orderType));
            
            \Log::info('Order rejection email sent', [
                'order_id' => $order->id,
                'order_type' => $orderType,
                'customer_email' => $order->user->email,
                'reason' => $request->reason
            ]);
            
            $emailSent = true;
        } catch (\Exception $e) {
            \Log::error('Failed to send order rejection email: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }

        if ($emailSent) {
            return redirect()->back()->with('success', 'Pesanan berhasil ditolak dan email notifikasi telah dikirim.');
        } else {
            return redirect()->back()->with('success', 'Pesanan berhasil ditolak. (Catatan: Email notifikasi gagal dikirim)');
        }
    }

    /**
     * Update order status (manual processing/completed by admin)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,processing,completed,cancelled',
        ]);

        $orderType = $request->get('type', 'regular');

        if ($orderType === 'custom') {
            $order = CustomDesignOrder::findOrFail($id);
        } else {
            $order = Order::findOrFail($id);
        }

        // Check if user has active VA when trying to reject/cancel
        if (in_array($request->status, ['rejected', 'cancelled'])) {
            $activeVA = \App\Models\VirtualAccount::where('user_id', $order->user_id)
                ->where('order_id', $id)
                ->where('order_type', $orderType)
                ->where('status', 'pending')
                ->where('expired_at', '>', now())
                ->first();
            
            if ($activeVA) {
                $errorMessage = 'Tidak dapat mengubah status ke ' . $request->status . ' karena pesanan ini memiliki Virtual Account aktif. Harap tunggu hingga VA expired atau customer membatalkan VA terlebih dahulu.';
                
                // If AJAX request, return JSON
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 400);
                }
                
                return redirect()
                    ->route('admin.order.detail', ['id' => $id, 'type' => $orderType])
                    ->with('error', $errorMessage);
            }
            
            // Restore stock if order was previously approved
            if ($order->status === 'approved') {
                $stockRestored = $this->restoreStockForOrder($order, $orderType);
                Log::info('Stock restored due to status change to ' . $request->status, [
                    'order_id' => $order->id,
                    'order_type' => $orderType,
                    'previous_status' => $order->status,
                    'new_status' => $request->status,
                    'stock_restored' => $stockRestored
                ]);
            }
        }

        // Only allow manual update to 'processing' or 'completed' if order is paid
        if (in_array($request->status, ['processing', 'completed'])) {
            if (!isset($order->payment_status) || $order->payment_status !== 'paid') {
                return redirect()
                    ->route('admin.order.detail', ['id' => $id, 'type' => $orderType])
                    ->with('error', 'Order harus sudah dibayar sebelum diproses');
            }
        }

        $updateData = ['status' => $request->status];
        
        // Set timestamps
        if ($request->status === 'processing') {
            $updateData['processing_at'] = now();
        } elseif ($request->status === 'completed') {
            $updateData['completed_at'] = now();
        }

        // If updating status to 'approved', ensure stock deduction runs and use a transaction
        if ($request->status === 'approved') {
            DB::beginTransaction();
            try {
                // Deduct stock for this order (will return ['success' => bool, 'message' => string])
                $deduction = $this->deductStockForOrder($order, $orderType);

                if (!$deduction['success']) {
                    throw new \Exception($deduction['message']);
                }

                // Set approved timestamps + payment deadline
                $updateData['approved_at'] = now();
                $updateData['payment_deadline'] = now()->addHours(24);

                // Save and commit
                $order->forceFill($updateData)->save();
                DB::commit();

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Status pesanan berhasil diubah ke Approved',
                        'status' => 'approved',
                        'timestamp' => now()->format('l, d/m/Y H:i')
                    ]);
                }

                return redirect()
                    ->route('admin.order.detail', ['id' => $id, 'type' => $orderType])
                    ->with('success', 'Status pesanan berhasil diubah ke Approved. Stok telah dikurangi.');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to update status to approved', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal mengubah status: ' . $e->getMessage()
                    ], 400);
                }

                return redirect()
                    ->route('admin.order.detail', ['id' => $id, 'type' => $orderType])
                    ->with('error', 'Gagal mengubah status pesanan: ' . $e->getMessage());
            }
        }

        // Default: non-approved status change
        $oldStatus = $order->status;
        $order->update($updateData);

        // Send status update notification to customer
        if ($oldStatus !== $request->status && $order->user_id) {
            try {
                app(\App\Services\NotificationService::class)->notifyOrderStatusUpdate($order, $order->user_id, $oldStatus, $request->status);
            } catch (\Exception $e) {
                Log::warning('Failed to send status update notification', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // If AJAX request, return JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status pesanan berhasil diubah ke ' . ucfirst($request->status),
                'status' => $request->status,
                'timestamp' => now()->format('l, d/m/Y H:i')
            ]);
        }

        return redirect()
            ->route('admin.order.detail', ['id' => $id, 'type' => $orderType])
            ->with('success', 'Status pesanan berhasil diubah ke ' . ucfirst($request->status));
    }

    /**
     * Get order detail for modal (legacy)
     */
    public function getDetail(Request $request, $id)
    {
        $orderType = $request->get('type', 'regular');

        if ($orderType === 'custom') {
            $order = CustomDesignOrder::with(['user', 'product', 'uploads'])->findOrFail($id);
            $uploads = $order->uploads ?? [];
        } else {
            $order = Order::with(['user'])->findOrFail($id);
            $uploads = [];

            // Add status_label to the order object
            $order->status_label = $order->getStatusLabel();
        }

        return response()->json([
            'order' => $order,
            'orderType' => $orderType,
            'uploads' => $uploads,
        ]);
    }

    /**
     * Show order detail page
     */
    public function showDetail(Request $request, $id)
    {
        $orderType = $request->get('type', 'regular');

        if ($orderType === 'custom') {
            $order = CustomDesignOrder::with(['user', 'product', 'variant', 'uploads'])->findOrFail($id);
            $uploads = $order->uploads ?? [];
            
            // Debug: Log untuk cek data
            \Log::info('Order Detail Debug:', [
                'order_id' => $order->id,
                'product_id' => $order->product_id,
                'variant_id' => $order->variant_id,
                'has_product' => !is_null($order->product),
                'has_variant' => !is_null($order->variant),
                'product_image' => $order->product ? $order->product->image : null,
                'variant_image' => $order->variant ? $order->variant->image : null,
            ]);
        } else {
            $order = Order::with(['user'])->findOrFail($id);
            $uploads = [];
        }

        // Get payment info
        $virtualAccount = \App\Models\VirtualAccount::where('user_id', $order->user_id)
            ->latest()
            ->first();
            
        $paymentTransaction = \App\Models\PaymentTransaction::where('user_id', $order->user_id)
            ->where('order_id', $id)
            ->where('order_type', $orderType)
            ->latest()
            ->first();

        return view('admin.order-detail', compact('order', 'orderType', 'uploads', 'virtualAccount', 'paymentTransaction'));
    }

    /**
     * Display order history page (completed orders only)
     */
    public function history(Request $request)
    {
        $orderType = $request->get('type', 'all');
        
        // Get completed orders only
        if ($orderType === 'custom') {
            $query = CustomDesignOrder::with('user')
                ->where('status', 'completed');
        } elseif ($orderType === 'regular') {
            $query = Order::with('user')
                ->where('status', 'completed');
        } else {
            // All completed orders
            $customOrders = CustomDesignOrder::with('user')
                ->where('status', 'completed');
            $regularOrders = Order::with('user')
                ->where('status', 'completed');
            
            // Get both and merge
            $customData = $customOrders->get()->map(function($order) {
                $order->order_type = 'custom';
                return $order;
            });
            $regularData = $regularOrders->get()->map(function($order) {
                $order->order_type = 'regular';
                return $order;
            });
            
            $orders = $customData->merge($regularData)
                ->sortByDesc('completed_at')
                ->values();
            
            // Manual pagination
            $perPage = 10;
            $currentPage = $request->get('page', 1);
            $total = $orders->count();
            $orders = $orders->slice(($currentPage - 1) * $perPage, $perPage)->values();
            
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $orders,
                $total,
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            
            return view('admin.order-history', [
                'orders' => $paginator,
                'currentOrderType' => $orderType
            ]);
        }
        
        // Paginate for single type
        $orders = $query->orderBy('completed_at', 'desc')->paginate(10);
        
        return view('admin.order-history', [
            'orders' => $orders,
            'currentOrderType' => $orderType
        ]);
    }
    
    /**
     * Mark order payment as received (for manual WA payment verification)
     */
    public function markPaymentReceived(Request $request, $id)
    {
        $orderType = $request->get('type', 'regular');
        
        if ($orderType === 'custom') {
            $order = CustomDesignOrder::findOrFail($id);
        } else {
            $order = Order::findOrFail($id);
        }

        // Check if order is already paid
        if ($order->payment_status === 'paid') {
            return redirect()
                ->route('admin.order.detail', ['id' => $id, 'type' => $orderType])
                ->with('error', 'Pesanan sudah ditandai sebagai dibayar.');
        }

        try {
            DB::beginTransaction();

            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);

            // Dispatch event for payment received
            PaymentReceivedEvent::dispatch($order, $orderType);

            DB::commit();

            // Send payment confirmation notification
            try {
                app(\App\Services\NotificationService::class)->notifyPaymentReceived($order, $orderType);
            } catch (\Exception $e) {
                Log::warning('Failed to send payment confirmation notification', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('Payment marked as received', [
                'order_id' => $order->id,
                'order_type' => $orderType,
                'marked_by' => auth()->user()->name ?? 'System',
                'timestamp' => now()
            ]);

            return redirect()
                ->route('admin.order.detail', ['id' => $id, 'type' => $orderType])
                ->with('success', 'Pembayaran telah dikonfirmasi. Pesanan dapat diproses.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark payment as received', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.order.detail', ['id' => $id, 'type' => $orderType])
                ->with('error', 'Gagal mengkonfirmasi pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Export orders to Excel
     */
    public function export(Request $request)
    {
        $orderType = $request->get('type', 'regular');

        if ($orderType === 'custom') {
            return Excel::download(
                new \App\Exports\CustomDesignOrderExport(),
                'custom_orders_' . date('Y-m-d_His') . '.xlsx'
            );
        } else {
            return Excel::download(
                new \App\Exports\OrderExport(),
                'orders_' . date('Y-m-d_His') . '.xlsx'
            );
        }
    }

    // Stock management methods moved to StockManagementTrait
}
