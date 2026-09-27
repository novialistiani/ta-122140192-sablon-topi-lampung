<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\CustomDesignOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceController extends Controller
{
    /**
     * Display finance dashboard
     * Data ditarik dari tabel orders & custom_design_orders (pembayaran QRIS),
     * bukan dari PaymentTransaction/VirtualAccount (sistem lama yang sudah tidak dipakai).
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        [$totalRevenue, $paidCount, $pendingVerificationCount] = $this->getSummary($start, $end);

        // Periode sebelumnya untuk perbandingan persentase
        $periodLength = $start->diffInDays($end);
        $prevStart = $start->copy()->subDays($periodLength + 1);
        $prevEnd = $start->copy()->subSecond();

        [$prevRevenue, $prevPaidCount, $prevPendingCount] = $this->getSummary($prevStart, $prevEnd);

        $revenueChange = $prevRevenue > 0 ? (($totalRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;
        $paidChange = $prevPaidCount > 0 ? (($paidCount - $prevPaidCount) / $prevPaidCount) * 100 : 0;
        $pendingChange = $prevPendingCount > 0 ? (($pendingVerificationCount - $prevPendingCount) / $prevPendingCount) * 100 : 0;

        $chartData = $this->getChartData($start, $end);
        $transactions = $this->getTransactions($start, $end, $request->get('page', 1));

        return view('admin.finance.index', compact(
            'totalRevenue',
            'paidCount',
            'pendingVerificationCount',
            'revenueChange',
            'paidChange',
            'pendingChange',
            'chartData',
            'transactions',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Hitung total pemasukan, jumlah transaksi dibayar, dan jumlah menunggu verifikasi
     * dalam suatu rentang tanggal (gabungan pesanan reguler & custom design).
     */
    private function getSummary($start, $end)
    {
        $regularRevenue = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->sum('total');

        $customRevenue = CustomDesignOrder::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->sum('total_price');

        $totalRevenue = $regularRevenue + $customRevenue;

        $paidCount = Order::where('payment_status', 'paid')
                ->whereBetween('paid_at', [$start, $end])->count()
            + CustomDesignOrder::where('payment_status', 'paid')
                ->whereBetween('paid_at', [$start, $end])->count();

        $pendingVerificationCount = Order::where('payment_status', 'pending_verification')
                ->whereBetween('created_at', [$start, $end])->count()
            + CustomDesignOrder::where('payment_status', 'pending_verification')
                ->whereBetween('created_at', [$start, $end])->count();

        return [$totalRevenue, $paidCount, $pendingVerificationCount];
    }

    /**
     * Data grafik pemasukan harian (gabungan pesanan reguler & custom design)
     */
    private function getChartData($start, $end)
    {
        $regularChart = Order::select(
                DB::raw('DATE(paid_at) as date'),
                DB::raw('SUM(total) as total')
            )
            ->where('payment_status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->groupBy('date')
            ->get();

        $customChart = CustomDesignOrder::select(
                DB::raw('DATE(paid_at) as date'),
                DB::raw('SUM(total_price) as total')
            )
            ->where('payment_status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->groupBy('date')
            ->get();

        $chartMap = [];
        foreach ($regularChart as $row) {
            $chartMap[$row->date] = ($chartMap[$row->date] ?? 0) + $row->total;
        }
        foreach ($customChart as $row) {
            $chartMap[$row->date] = ($chartMap[$row->date] ?? 0) + $row->total;
        }
        ksort($chartMap);

        return collect($chartMap)->map(function ($total, $date) {
            return (object) ['date' => $date, 'total' => $total];
        })->values();
    }

    /**
     * Daftar transaksi gabungan (reguler & custom design) yang sudah dibayar
     * atau sedang menunggu verifikasi, dengan pagination manual.
     */
    private function getTransactions($start, $end, $currentPage)
    {
        $regularTransactions = Order::with('user')
            ->whereIn('payment_status', ['paid', 'pending_verification'])
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->map(function ($order) {
                $order->order_type = 'regular';
                $order->display_id = 'ORD-' . $order->id;
                $order->display_item = $order->items[0]['name'] ?? 'Pesanan Reguler';
                $order->display_total = $order->total;
                return $order;
            });

        $customTransactions = CustomDesignOrder::with('user')
            ->whereIn('payment_status', ['paid', 'pending_verification'])
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->map(function ($order) {
                $order->order_type = 'custom';
                $order->display_id = 'CUS-' . $order->id;
                $order->display_item = $order->product_name;
                $order->display_total = $order->total_price;
                return $order;
            });

        $all = $regularTransactions->concat($customTransactions)
            ->sortByDesc('created_at')
            ->values();

        $perPage = 25;
        $offset = ($currentPage - 1) * $perPage;

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $all->slice($offset, $perPage)->values(),
            $all->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Export transaksi ke CSV
     */
    public function export(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $regularTransactions = Order::with('user')
            ->whereIn('payment_status', ['paid', 'pending_verification'])
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->map(function ($order) {
                $order->display_id = 'ORD-' . $order->id;
                $order->display_item = $order->items[0]['name'] ?? 'Pesanan Reguler';
                $order->display_total = $order->total;
                return $order;
            });

        $customTransactions = CustomDesignOrder::with('user')
            ->whereIn('payment_status', ['paid', 'pending_verification'])
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->map(function ($order) {
                $order->display_id = 'CUS-' . $order->id;
                $order->display_item = $order->product_name;
                $order->display_total = $order->total_price;
                return $order;
            });

        $transactions = $regularTransactions->concat($customTransactions)
            ->sortByDesc('created_at')
            ->values();

        $filename = 'transactions_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Tanggal', 'ID Pesanan', 'Customer', 'Barang',
                'Metode Pembayaran', 'Total', 'Status Pembayaran'
            ]);

            foreach ($transactions as $trx) {
                fputcsv($file, [
                    $trx->created_at->format('d/m/Y H:i'),
                    $trx->display_id,
                    $trx->user->name ?? 'N/A',
                    $trx->display_item,
                    'QRIS',
                    number_format($trx->display_total, 0, ',', '.'),
                    $trx->payment_status === 'paid' ? 'Sudah Dibayar' : 'Menunggu Verifikasi'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}