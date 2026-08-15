<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\CustomDesignOrder;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;

class DashboardController
{
    /**
     * Display the admin dashboard with dynamic data
     */
    public function index()
    {
        // Calculate statistics (including both regular and custom design orders)
        $regularCompleted = Order::where('status', 'completed')->count();
        $customCompleted = CustomDesignOrder::where('status', 'completed')->count();
        
        $regularRevenue = Order::where('status', 'completed')->sum('total') ?? 0;
        $customRevenue = CustomDesignOrder::where('status', 'completed')->sum('total_price') ?? 0;
        
        $regularPending = Order::where('status', 'pending')->count();
        $customPending = CustomDesignOrder::where('status', 'pending')->count();
        
        $stats = [
            'total_sold' => $regularCompleted + $customCompleted,
            'revenue' => $regularRevenue + $customRevenue,
            'customers' => User::count(),
            'pending_orders' => $regularPending + $customPending,
        ];

        // Trend calculation (compared to previous month) - including custom orders
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();
        
        $currentMonthRegular = Order::where('status', 'completed')
            ->whereBetween('completed_at', [$currentMonth, now()])
            ->count();
        $currentMonthCustom = CustomDesignOrder::where('status', 'completed')
            ->whereBetween('completed_at', [$currentMonth, now()])
            ->count();
        $currentMonthSold = $currentMonthRegular + $currentMonthCustom;
        
        $previousMonthRegular = Order::where('status', 'completed')
            ->whereBetween('completed_at', [$previousMonth, $previousMonth->copy()->endOfMonth()])
            ->count();
        $previousMonthCustom = CustomDesignOrder::where('status', 'completed')
            ->whereBetween('completed_at', [$previousMonth, $previousMonth->copy()->endOfMonth()])
            ->count();
        $previousMonthSold = $previousMonthRegular + $previousMonthCustom;

        $soldTrend = $previousMonthSold > 0 
            ? round((($currentMonthSold - $previousMonthSold) / $previousMonthSold) * 100, 1)
            : 0;

        // Top products (by order count)
        $topProducts = Product::select('products.id', 'products.name')
            ->leftJoin('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->leftJoin('orders', function($join) {
                $join->on('product_variants.id', '=', DB::raw('JSON_EXTRACT(orders.items, "$[*].variant_id")'))
                    ->orWhereRaw('orders.items LIKE CONCAT("%\"", product_variants.id, "%")');
            })
            ->groupBy('products.id', 'products.name')
            ->selectRaw('COUNT(orders.id) as order_count, COUNT(DISTINCT product_variants.id) as variant_count')
            ->orderByDesc('order_count')
            ->limit(5)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'order_count' => $product->order_count ?? 0,
                    'variant_count' => $product->variant_count ?? 0
                ];
            });

        // Recent orders with customer details (combining regular and custom orders)
        $regularOrders = Order::with('user')
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function($order) {
                $order->order_type = 'regular';
                return $order;
            });
            
        $customOrders = CustomDesignOrder::with('user')
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function($order) {
                $order->order_type = 'custom';
                $order->total = $order->total_price; // Normalize field name
                return $order;
            });
        
        $recentOrders = $regularOrders->concat($customOrders)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();

        // Order status breakdown (combining both order types)
        $regularStatusBreakdown = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->keyBy('status');
            
        $customStatusBreakdown = CustomDesignOrder::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->keyBy('status');
        
        // Merge status breakdowns
        $orderStatusBreakdown = collect();
        $allStatuses = $regularStatusBreakdown->keys()->merge($customStatusBreakdown->keys())->unique();
        foreach ($allStatuses as $status) {
            $regularCount = $regularStatusBreakdown->get($status)->count ?? 0;
            $customCount = $customStatusBreakdown->get($status)->count ?? 0;
            $orderStatusBreakdown[$status] = (object)['status' => $status, 'count' => $regularCount + $customCount];
        }

        // Sales data for chart (last 6 months) - including custom orders
        $sixMonthsAgo = now()->subMonths(6);
        
        $regularSales = Order::where('status', 'completed')
            ->whereBetween('created_at', [$sixMonthsAgo, now()])
            ->get()
            ->map(function($order) {
                return [
                    'created_at' => $order->created_at,
                    'total' => $order->total
                ];
            });
            
        $customSales = CustomDesignOrder::where('status', 'completed')
            ->whereBetween('created_at', [$sixMonthsAgo, now()])
            ->get()
            ->map(function($order) {
                return [
                    'created_at' => $order->created_at,
                    'total' => $order->total_price
                ];
            });
        
        $allSales = $regularSales->concat($customSales);
        
        $salesByMonth = $allSales->groupBy(function($item) {
                return $item['created_at']->format('Y-m');
            })
            ->map(function($group) {
                return [
                    'month' => $group->first()['created_at']->format('M'),
                    'count' => $group->count(),
                    'revenue' => $group->sum('total')
                ];
            })
            ->values();

        return view('admin.dashboard', [
            'stats' => $stats,
            'soldTrend' => $soldTrend,
            'topProducts' => $topProducts,
            'recentOrders' => $recentOrders,
            'orderStatusBreakdown' => $orderStatusBreakdown,
            'salesByMonth' => $salesByMonth,
        ]);
    }

    /**
     * Get dashboard stats via API (for real-time updates)
     */
    public function getStats(Request $request)
    {
        $regularCompleted = Order::where('status', 'completed')->count();
        $customCompleted = CustomDesignOrder::where('status', 'completed')->count();
        
        $regularRevenue = Order::where('status', 'completed')->sum('total') ?? 0;
        $customRevenue = CustomDesignOrder::where('status', 'completed')->sum('total_price') ?? 0;
        
        $regularPending = Order::where('status', 'pending')->count();
        $customPending = CustomDesignOrder::where('status', 'pending')->count();
        
        $stats = [
            'total_sold' => $regularCompleted + $customCompleted,
            'revenue' => $regularRevenue + $customRevenue,
            'customers' => User::count(),
            'pending_orders' => $regularPending + $customPending,
        ];

        return response()->json($stats);
    }

    /**
     * Get sales chart data via API
     */
    public function getSalesData(Request $request)
    {
        $months = $request->query('months', 6);
        $startDate = now()->subMonths($months);

        // Get regular orders
        $regularSales = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, now()])
            ->get()
            ->map(function($order) {
                return [
                    'created_at' => $order->created_at,
                    'total' => $order->total
                ];
            });
            
        // Get custom design orders
        $customSales = CustomDesignOrder::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, now()])
            ->get()
            ->map(function($order) {
                return [
                    'created_at' => $order->created_at,
                    'total' => $order->total_price
                ];
            });
        
        $allSales = $regularSales->concat($customSales);
        
        $salesData = $allSales->groupBy(function($item) {
                return $item['created_at']->format('M');
            })
            ->map(function($group, $label) {
                return [
                    'label' => $label,
                    'sales' => $group->count(),
                    'revenue' => (int) $group->sum('total')
                ];
            })
            ->values()
            ->sortBy('label');

        return response()->json([
            'labels' => $salesData->pluck('label')->toArray(),
            'datasets' => [
                [
                    'label' => 'Penjualan',
                    'data' => $salesData->pluck('sales')->toArray(),
                    'borderColor' => '#0a1d37',
                    'backgroundColor' => 'rgba(10, 29, 55, 0.05)',
                ]
            ]
        ]);
    }

    /**
     * Get recent orders via API (including custom design orders)
     */
    public function getRecentOrders(Request $request)
    {
        $limit = $request->query('limit', 10);

        // Get regular orders
        $regularOrders = Order::with('user')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->order_number,
                    'product' => $order->items[0]['name'] ?? 'N/A',
                    'customer' => $order->user->name ?? 'N/A',
                    'date' => $order->created_at->format('M d, Y'),
                    'status' => $order->status,
                    'amount' => 'Rp ' . number_format((float) $order->total, 0, ',', '.'),
                    'type' => 'regular',
                    'created_at' => $order->created_at,
                ];
            });
            
        // Get custom design orders
        $customOrders = CustomDesignOrder::with('user')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => 'CDO-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'product' => $order->product_name ?? 'Custom Design',
                    'customer' => $order->user->name ?? 'N/A',
                    'date' => $order->created_at->format('M d, Y'),
                    'status' => $order->status,
                    'amount' => 'Rp ' . number_format((float) $order->total_price, 0, ',', '.'),
                    'type' => 'custom',
                    'created_at' => $order->created_at,
                ];
            });
        
        // Merge and sort by date, take the limit
        $orders = $regularOrders->concat($customOrders)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values()
            ->map(function($order) {
                unset($order['created_at']); // Remove helper field
                return $order;
            });

        return response()->json($orders);
    }

    /**
     * Get top products via API (by order count)
     */
    public function getTopProducts(Request $request)
    {
        $limit = $request->query('limit', 5);

        $products = Product::select('products.id', 'products.name')
            ->leftJoin('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->leftJoin('orders', function($join) {
                $join->on('product_variants.id', '=', DB::raw('JSON_EXTRACT(orders.items, "$[*].variant_id")'))
                    ->orWhereRaw('orders.items LIKE CONCAT("%\"", product_variants.id, "%")');
            })
            ->groupBy('products.id', 'products.name')
            ->selectRaw('COUNT(orders.id) as order_count, COUNT(DISTINCT product_variants.id) as variant_count')
            ->orderByDesc('order_count')
            ->limit($limit)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'order_count' => $product->order_count ?? 0,
                    'variant_count' => $product->variant_count ?? 0
                ];
            });

        return response()->json($products);
    }
}
