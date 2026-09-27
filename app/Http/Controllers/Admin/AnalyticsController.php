<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\CustomDesignOrder;
use App\Models\User;
use App\Models\Product;
use App\Models\PaymentTransaction;
use App\Exports\AnalyticsReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AnalyticsController extends Controller
{
    /**
     * Display analytics report page
     */
    public function index()
    {
        try {
            // Get date range from request
            $period = request()->get('period', 'month');
            $startDate = $this->getStartDate($period);
            $endDate = now();
            
            return view('admin.analytics', [
                'period' => $period,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]);
        } catch (\Exception $e) {
            \Log::error('Analytics Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load analytics.');
        }
    }
    
    /**
     * Get sales & revenue overview data
     */
    public function getSalesOverview()
    {
        try {
            // Support both period-based and date range filtering
            $period = request()->get('period', 'month');
            $startDate = request()->get('start_date');
            $endDate = request()->get('end_date');
            
            if ($startDate && $endDate) {
                // Date range filtering
                $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
                $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();
            } else {
                // Fallback to period-based filtering
                $startDate = $this->getStartDate($period);
                $endDate = now();
            }
            
            // Get all orders in date range
            $orders = Order::where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
            
            $customOrders = CustomDesignOrder::where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
            
            // Get all orders (regardless of status) for conversion rate
            $allOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
            $allCustomOrders = CustomDesignOrder::whereBetween('created_at', [$startDate, $endDate])->count();
            $totalAllOrders = $allOrders + $allCustomOrders;
            
            // Combine revenues - Order has 'total', CustomDesignOrder has 'total_price'
            $totalRevenue = $orders->sum('total') + $customOrders->sum('total_price');
            $completedOrders = $orders->count() + $customOrders->count();
            $averageOrderValue = $completedOrders > 0 ? $totalRevenue / $completedOrders : 0;
            $conversionRate = $totalAllOrders > 0 ? ($completedOrders / $totalAllOrders) * 100 : 0;
            
            // Get previous period data for comparison
            $prevStartDate = $this->getPreviousPeriodStart($period, $startDate);
            $prevEndDate = $startDate->copy()->subSecond();
            
            $prevOrders = Order::where('status', 'completed')
                ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
                ->get();
            
            $prevCustomOrders = CustomDesignOrder::where('status', 'completed')
                ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
                ->get();
            
            $prevRevenue = $prevOrders->sum('total') + $prevCustomOrders->sum('total_price');
            
            // Get previous period all orders for conversion rate
            $prevAllOrders = Order::whereBetween('created_at', [$prevStartDate, $prevEndDate])->count();
            $prevAllCustomOrders = CustomDesignOrder::whereBetween('created_at', [$prevStartDate, $prevEndDate])->count();
            $prevTotalAllOrders = $prevAllOrders + $prevAllCustomOrders;
            $prevCompletedOrders = $prevOrders->count() + $prevCustomOrders->count();
            $prevConversionRate = $prevTotalAllOrders > 0 ? ($prevCompletedOrders / $prevTotalAllOrders) * 100 : 0;
            
            // Calculate growth percentage
            $revenueGrowth = $prevRevenue > 0 ? (($totalRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;
            $conversionGrowth = $prevConversionRate > 0 ? (($conversionRate - $prevConversionRate) / $prevConversionRate) * 100 : 0;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'totalRevenue' => round($totalRevenue, 2),
                    'completedOrders' => $completedOrders,
                    'totalOrders' => $totalAllOrders,
                    'averageOrderValue' => round($averageOrderValue, 2),
                    'conversionRate' => round($conversionRate, 2),
                    'revenueGrowth' => round($revenueGrowth, 2),
                    'conversionGrowth' => round($conversionGrowth, 2),
                    'comparison' => [
                        'label' => $period === 'day' ? 'Today vs Yesterday' : ($period === 'week' ? 'This Week vs Last Week' : 'This Month vs Last Month'),
                        'current' => round($totalRevenue, 2),
                        'previous' => round($prevRevenue, 2),
                        'percentageChange' => round($revenueGrowth, 2),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Sales Overview Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get sales trend data (for charts)
     */
    public function getSalesTrendData()
    {
        try {
            $period = request()->get('period', 'month');
            $groupBy = request()->get('groupBy', 'day'); // day, week, month
            
            // Support date range filtering
            $startDate = request()->get('start_date');
            $endDate = request()->get('end_date');
            
            if ($startDate && $endDate) {
                // Date range filtering
                $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            } else {
                // Period-based filtering
                $startDate = $this->getStartDate($period);
                $endDate = now();
            }
            
            $query = Order::where('status', 'completed')
                ->select(
                    DB::raw("DATE({$this->getDateFormat($groupBy)}) as date"),
                    DB::raw('COUNT(*) as orders'),
                    DB::raw('SUM(total) as revenue')
                )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy(DB::raw("DATE({$this->getDateFormat($groupBy)})"))
                ->orderBy('date');
            
            $orders = $query->get();
            
            // Also get custom design orders - use 'total_price' instead of 'total'
            $customQuery = CustomDesignOrder::where('status', 'completed')
                ->select(
                    DB::raw("DATE({$this->getDateFormat($groupBy)}) as date"),
                    DB::raw('COUNT(*) as orders'),
                    DB::raw('SUM(total_price) as revenue')
                )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy(DB::raw("DATE({$this->getDateFormat($groupBy)})"))
                ->orderBy('date');
            
            $customOrders = $customQuery->get();
            
            // Merge data
            $chartData = [];
            foreach ($orders as $order) {
                $chartData[$order->date] = [
                    'date' => $order->date,
                    'orders' => $order->orders ?? 0,
                    'revenue' => $order->revenue ?? 0,
                ];
            }
            
            foreach ($customOrders as $order) {
                if (isset($chartData[$order->date])) {
                    $chartData[$order->date]['orders'] += $order->orders ?? 0;
                    $chartData[$order->date]['revenue'] += $order->revenue ?? 0;
                } else {
                    $chartData[$order->date] = [
                        'date' => $order->date,
                        'orders' => $order->orders ?? 0,
                        'revenue' => $order->revenue ?? 0,
                    ];
                }
            }
            
            // Sort by date
            ksort($chartData);
            
            return response()->json([
                'success' => true,
                'data' => array_values($chartData),
            ]);
        } catch (\Exception $e) {
            \Log::error('Sales Trend Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get order status distribution
     */
    public function getOrderStatusDistribution()
    {
        try {
            $period = request()->get('period', 'month');
            
            // Support date range filtering
            $startDate = request()->get('start_date');
            $endDate = request()->get('end_date');
            
            if ($startDate && $endDate) {
                // Date range filtering
                $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            } else {
                // Period-based filtering
                $startDate = $this->getStartDate($period);
                $endDate = now();
            }
            
            $statuses = ['completed', 'pending', 'processing', 'cancelled', 'rejected', 'approved'];
            $distribution = [];
            
            foreach ($statuses as $status) {
                $count = Order::where('status', $status)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();
                
                $count += CustomDesignOrder::where('status', $status)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();
                
                $distribution[] = [
                    'status' => $status,
                    'label' => $this->getStatusLabel($status),
                    'count' => $count,
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $distribution,
            ]);
        } catch (\Exception $e) {
            \Log::error('Order Status Distribution Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get customer analytics
     */
    public function getCustomerAnalytics()
    {
        try {
            $period = request()->get('period', 'month');
            
            // Support date range filtering
            $startDate = request()->get('start_date');
            $endDate = request()->get('end_date');
            
            if ($startDate && $endDate) {
                // Date range filtering
                $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            } else {
                // Period-based filtering
                $startDate = $this->getStartDate($period);
                $endDate = now();
            }
            
            // New customers in this period
            $newCustomers = User::whereBetween('created_at', [$startDate, $endDate])
                ->count();
            
            // Total customers who existed at the start of period (active customer base)
            $totalCustomersInPeriod = User::where('created_at', '<=', $endDate)->count();
            
            // Customers who made purchases IN THIS PERIOD
            $customersWithOrdersInPeriod = Order::whereBetween('created_at', [$startDate, $endDate])
                ->distinct('user_id')
                ->count('user_id');
            
            $customersWithCustomOrdersInPeriod = CustomDesignOrder::whereBetween('created_at', [$startDate, $endDate])
                ->distinct('user_id')
                ->count('user_id');
            
            $activeCustomersInPeriod = max($customersWithOrdersInPeriod, $customersWithCustomOrdersInPeriod);
            
            // Total active customers all-time (for comparison)
            $totalActiveCustomersAllTime = max(
                Order::distinct('user_id')->count('user_id'),
                CustomDesignOrder::distinct('user_id')->count('user_id')
            );
            
            // Purchasing rate = customers who bought in this period / total customers who existed
            $purchasingRate = $totalCustomersInPeriod > 0 ? round(($activeCustomersInPeriod / $totalCustomersInPeriod) * 100, 2) : 0;
            
            // RFM Analysis (Recency, Frequency, Monetary)
            $rfmData = [];
            
            $customers = User::select('id', 'name', 'email', 'created_at')
                ->get();
            
            foreach ($customers as $customer) {
                $orders = Order::where('user_id', $customer->id)->get();
                $customOrders = CustomDesignOrder::where('user_id', $customer->id)->get();
                
                $allOrders = $orders->concat($customOrders);
                
                if ($allOrders->count() === 0) continue;
                
                $lastOrder = $allOrders->sortByDesc('created_at')->first();
                $recency = now()->diffInDays($lastOrder->created_at, true);
                $frequency = $allOrders->count();
                // Sum both Order.total and CustomDesignOrder.total_price
                $monetary = $orders->sum('total') + $customOrders->sum('total_price');
                
                $rfmData[] = [
                    'customer' => $customer->name,
                    'email' => $customer->email,
                    'recency' => $recency,
                    'frequency' => $frequency,
                    'monetary' => $monetary,
                    'segment' => $this->getRFMSegment($recency, $frequency, $monetary),
                ];
            }
            
            // Sort by monetary value (top spenders)
            usort($rfmData, function($a, $b) {
                return $b['monetary'] <=> $a['monetary'];
            });
            
            $topRFMData = array_slice($rfmData, 0, 10);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'newCustomers' => $newCustomers,
                    'activeCustomers' => $activeCustomersInPeriod,
                    'totalCustomers' => $totalCustomersInPeriod,
                    'purchasingRate' => $purchasingRate,
                    'rfmTop' => $topRFMData,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Customer Analytics Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get conversion funnel data
     *
     * Funnel disederhanakan menjadi 3 tahap yang datanya benar-benar dapat
     * diukur dari basis data (Checkout Dibuat -> Pembayaran Selesai ->
     * Pesanan Selesai). Tahap "Visitors", "Product Views", dan "Add to Cart"
     * pada versi sebelumnya dihapus karena tidak dapat diukur secara valid:
     * sistem tidak memiliki page-view tracking, dan keranjang belanja
     * disimpan menggunakan Session (bukan tabel basis data permanen),
     * sehingga tidak meninggalkan jejak historis yang bisa di-query.
     */
    public function getConversionFunnel()
    {
        try {
            $period = request()->get('period', 'month');

            // Support date range filtering
            $startDate = request()->get('start_date');
            $endDate = request()->get('end_date');

            if ($startDate && $endDate) {
                $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
            } else {
                $startDate = $this->getStartDate($period);
                $endDate = now();
            }

            $funnel = $this->buildConversionFunnelData($startDate, $endDate);

            return response()->json([
                'success' => true,
                'data' => [
                    'funnel' => $funnel,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Conversion Funnel Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Hitung data funnel 3 tahap untuk rentang tanggal tertentu.
     * Dipakai bersama oleh getConversionFunnel() (JSON) dan exportExcel().
     */
    private function buildConversionFunnelData($startDate, $endDate)
    {
        // Tahap 1: Checkout dibuat (semua order pada periode ini, apapun statusnya)
        $checkoutCount = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $checkoutCount += CustomDesignOrder::whereBetween('created_at', [$startDate, $endDate])->count();

        // Tahap 2: Pembayaran selesai
        // Order reguler: payment_status = 'paid'
        // Custom design order: tidak memiliki payment_status, gunakan status 'approved' sebagai proksi pembayaran diterima
        $completedPayments = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $completedPayments += CustomDesignOrder::where('status', 'approved')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Tahap 3: Pesanan selesai
        $completedOrders = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $completedOrders += CustomDesignOrder::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Rate dihitung relatif terhadap tahap pertama (checkout),
        // sehingga funnel dijamin menurun atau tetap di setiap tahap berikutnya
        $paymentRate = $checkoutCount > 0 ? round(($completedPayments / $checkoutCount) * 100, 2) : 0;
        $completionRate = $checkoutCount > 0 ? round(($completedOrders / $checkoutCount) * 100, 2) : 0;

        return [
            [
                'stage' => '1. Checkout Dibuat',
                'count' => $checkoutCount,
                'rate' => 100,
            ],
            [
                'stage' => '2. Pembayaran Selesai',
                'count' => $completedPayments,
                'rate' => $paymentRate,
            ],
            [
                'stage' => '3. Pesanan Selesai',
                'count' => $completedOrders,
                'rate' => $completionRate,
            ],
        ];
    }

    /**
     * Export laporan analytics (ringkasan penjualan, conversion funnel,
     * dan top customers RFM) ke file Excel.
     */
    public function exportExcel()
    {
        $period = request()->get('period', 'month');
        $startDateParam = request()->get('start_date');
        $endDateParam = request()->get('end_date');

        if ($startDateParam && $endDateParam) {
            $startDate = Carbon::parse($startDateParam)->startOfDay();
            $endDate = Carbon::parse($endDateParam)->endOfDay();
            $periodLabel = $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y');
        } else {
            $startDate = $this->getStartDate($period);
            $endDate = now();
            $periodLabel = $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y');
        }

        // --- Ringkasan Penjualan ---
        $orders = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        $customOrders = CustomDesignOrder::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $allOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $allCustomOrders = CustomDesignOrder::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalAllOrders = $allOrders + $allCustomOrders;

        $totalRevenue = $orders->sum('total') + $customOrders->sum('total_price');
        $completedOrders = $orders->count() + $customOrders->count();
        $averageOrderValue = $completedOrders > 0 ? $totalRevenue / $completedOrders : 0;
        $conversionRate = $totalAllOrders > 0 ? ($completedOrders / $totalAllOrders) * 100 : 0;

        $prevStartDate = $this->getPreviousPeriodStart($period, $startDate);
        $prevEndDate = $startDate->copy()->subSecond();
        $prevRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->sum('total')
            + CustomDesignOrder::where('status', 'completed')
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->sum('total_price');
        $revenueGrowth = $prevRevenue > 0 ? (($totalRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;

        $salesOverview = [
            'totalRevenue' => round($totalRevenue, 2),
            'completedOrders' => $completedOrders,
            'totalOrders' => $totalAllOrders,
            'conversionRate' => round($conversionRate, 2),
            'averageOrderValue' => round($averageOrderValue, 2),
            'revenueGrowth' => round($revenueGrowth, 2),
        ];

        // --- Conversion Funnel (pakai helper yang sama dengan endpoint JSON) ---
        $funnelData = $this->buildConversionFunnelData($startDate, $endDate);

        // --- Top Customers (RFM) ---
        $rfmData = [];
        $customers = User::select('id', 'name', 'email')->get();
        foreach ($customers as $customer) {
            $custOrders = Order::where('user_id', $customer->id)->get();
            $custCustomOrders = CustomDesignOrder::where('user_id', $customer->id)->get();
            $allCustOrders = $custOrders->concat($custCustomOrders);

            if ($allCustOrders->count() === 0) continue;

            $lastOrder = $allCustOrders->sortByDesc('created_at')->first();
            $recency = now()->diffInDays($lastOrder->created_at, true);
            $frequency = $allCustOrders->count();
            $monetary = $custOrders->sum('total') + $custCustomOrders->sum('total_price');

            $rfmData[] = [
                'customer' => $customer->name,
                'email' => $customer->email,
                'recency' => $recency,
                'frequency' => $frequency,
                'monetary' => $monetary,
                'segment' => $this->getRFMSegment($recency, $frequency, $monetary),
            ];
        }
        usort($rfmData, fn($a, $b) => $b['monetary'] <=> $a['monetary']);
        $topRFMData = array_slice($rfmData, 0, 10);

        $fileName = 'laporan-analytics-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new AnalyticsReportExport($periodLabel, $salesOverview, $funnelData, $topRFMData),
            $fileName
        );
    }
    
    // ============ Helper Methods ============
    
    private function getStartDate($period)
    {
        switch ($period) {
            case 'day':
                return now()->startOfDay();
            case 'week':
                return now()->startOfWeek();
            case 'month':
                return now()->startOfMonth();
            case 'year':
                return now()->startOfYear();
            default:
                return now()->startOfMonth();
        }
    }
    
    private function getPreviousPeriodStart($period, $startDate)
    {
        switch ($period) {
            case 'day':
                return $startDate->copy()->subDay();
            case 'week':
                return $startDate->copy()->subWeek();
            case 'month':
                return $startDate->copy()->subMonth();
            case 'year':
                return $startDate->copy()->subYear();
            default:
                return $startDate->copy()->subMonth();
        }
    }
    
    private function getDateFormat($groupBy)
    {
        switch ($groupBy) {
            case 'week':
                return 'DATE(DATE_SUB(created_at, INTERVAL WEEKDAY(created_at) DAY))';
            case 'month':
                return 'DATE_FORMAT(created_at, "%Y-%m-01")';
            default:
                return 'created_at';
        }
    }
    
    private function getStatusLabel($status)
    {
        $labels = [
            'completed' => 'Selesai',
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'cancelled' => 'Dibatalkan',
            'rejected' => 'Ditolak',
            'approved' => 'Disetujui',
            'failed' => 'Gagal',
            'refunded' => 'Pengembalian',
        ];
        
        return $labels[$status] ?? ucfirst($status);
    }
    
    
    private function getRFMSegment($recency, $frequency, $monetary)
    {
        // Simplified RFM segmentation
        if ($frequency >= 3 && $monetary >= 500000) {
            return 'Champion';
        } elseif ($frequency >= 2 && $monetary >= 300000) {
            return 'Loyal';
        } elseif ($recency <= 7) {
            return 'Recent';
        } elseif ($frequency === 1 && $monetary < 100000) {
            return 'At Risk';
        }
        
        return 'Regular';
    }

    /**
     * Check if analytics data has changed since last check
     * Used for real-time updates
     */
    public function checkAnalyticsChanges()
    {
        try {
            $clientHash = request()->get('last_hash', '');
            
            // Get latest order timestamp
            $latestOrder = Order::latest('updated_at')->first();
            $latestCustomOrder = CustomDesignOrder::latest('updated_at')->first();
            
            $latestTimestamp = max(
                $latestOrder?->updated_at?->timestamp ?? 0,
                $latestCustomOrder?->updated_at?->timestamp ?? 0
            );
            
            // Create a simple hash based on latest changes
            $currentHash = md5(json_encode([
                'latest_order_timestamp' => $latestTimestamp,
                'order_count' => Order::count(),
                'custom_order_count' => CustomDesignOrder::count(),
            ]));
            
            // Compare with client's last hash
            $hasChanges = $clientHash !== $currentHash;
            
            return response()->json([
                'hasChanges' => $hasChanges,
                'currentHash' => $currentHash,
                'latestTimestamp' => $latestTimestamp,
            ]);
        } catch (\Exception $e) {
            \Log::error('Check Analytics Changes Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}