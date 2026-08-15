<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\VirtualAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceController extends Controller
{
    /**
     * Display finance dashboard
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        
        // Convert to Carbon instances
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        // Total Pemasukan (paid transactions)
        $totalRevenue = PaymentTransaction::where('status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount');
        
        // Transaksi VA count
        $vaTransactions = PaymentTransaction::where('payment_method', 'va')
            ->whereBetween('created_at', [$start, $end])
            ->count();
        
        // Transaksi E-Wallet count (future)
        $ewalletTransactions = PaymentTransaction::where('payment_method', 'ewallet')
            ->whereBetween('created_at', [$start, $end])
            ->count();
        
        // Calculate percentage changes (compare to previous period)
        $periodLength = $start->diffInDays($end);
        $prevStart = $start->copy()->subDays($periodLength);
        $prevEnd = $start->copy()->subDay();
        
        $prevRevenue = PaymentTransaction::where('status', 'paid')
            ->whereBetween('paid_at', [$prevStart, $prevEnd])
            ->sum('amount');
        
        $prevVACount = PaymentTransaction::where('payment_method', 'va')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();
        
        $prevEwalletCount = PaymentTransaction::where('payment_method', 'ewallet')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();
        
        // Calculate percentages
        $revenueChange = $prevRevenue > 0 ? (($totalRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;
        $vaChange = $prevVACount > 0 ? (($vaTransactions - $prevVACount) / $prevVACount) * 100 : 0;
        $ewalletChange = $prevEwalletCount > 0 ? (($ewalletTransactions - $prevEwalletCount) / $prevEwalletCount) * 100 : 0;
        
        // Chart data - daily revenue
        $chartData = PaymentTransaction::select(
                DB::raw('DATE(paid_at) as date'),
                DB::raw('SUM(amount) as total')
            )
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Transactions list with pagination
        $transactions = PaymentTransaction::with(['user', 'virtualAccount'])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->paginate(25);
        
        return view('admin.finance.index', compact(
            'totalRevenue',
            'vaTransactions',
            'ewalletTransactions',
            'revenueChange',
            'vaChange',
            'ewalletChange',
            'chartData',
            'transactions',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Export transactions to Excel
     */
    public function export(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        $transactions = PaymentTransaction::with(['user', 'virtualAccount'])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $filename = 'transactions_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Tanggal',
                'ID Transaksi',
                'Customer',
                'Barang',
                'Metode Pembayaran',
                'Nomor VA',
                'Status VA',
                'Total',
                'Status Pembayaran'
            ]);
            
            foreach ($transactions as $trx) {
                $vaNumber = $trx->virtualAccount ? $trx->virtualAccount->va_number : '-';
                $vaStatus = $this->getVAStatus($trx);
                $paymentChannel = strtoupper($trx->payment_channel ?? 'N/A');
                
                fputcsv($file, [
                    $trx->created_at->format('d/m/Y H:i'),
                    $trx->transaction_id,
                    $trx->user->name ?? 'N/A',
                    $this->getItemName($trx),
                    $paymentChannel,
                    $vaNumber,
                    $vaStatus,
                    number_format($trx->amount, 0, ',', '.'),
                    $this->getStatusText($trx->status)
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Get VA status text
     */
    private function getVAStatus($transaction)
    {
        if (!$transaction->virtualAccount) {
            return '-';
        }
        
        $va = $transaction->virtualAccount;
        
        if ($va->status === 'paid') {
            return 'Sudah Dibayar';
        }
        
        if ($va->isExpired()) {
            return 'Expired';
        }
        
        return 'VA Aktif';
    }
    
    /**
     * Get status text in Indonesian
     */
    private function getStatusText($status)
    {
        $statuses = [
            'pending' => 'Pending',
            'paid' => 'Sudah Dibayar',
            'failed' => 'Gagal',
            'expired' => 'Kadaluarsa'
        ];
        
        return $statuses[$status] ?? $status;
    }
    
    /**
     * Get item name from transaction
     */
    private function getItemName($transaction)
    {
        if ($transaction->order_type === 'custom') {
            $order = \App\Models\CustomDesignOrder::find($transaction->order_id);
            return $order ? $order->product_name : 'Custom Design';
        } elseif ($transaction->order_type === 'regular') {
            $order = \App\Models\Order::find($transaction->order_id);
            if ($order && isset($order->items[0])) {
                return $order->items[0]['name'] ?? 'Regular Order';
            }
        }
        
        return 'N/A';
    }
}
