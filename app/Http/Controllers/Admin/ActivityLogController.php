<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\User;
use App\Exports\ActivityLogsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ActivityLogController extends Controller
{
    /**
     * Display activity logs
     */
    public function index(Request $request)
    {
        $query = ActivityLog::orderBy('created_at', 'desc');

        // Filter by user type and user id
        if ($request->has('user_type') && $request->has('user_id')) {
            $userType = $request->user_type === 'admin' ? 'App\\Models\\Admin' : 'App\\Models\\User';
            $userId = $request->user_id;
            
            $query->where('user_type', $userType)
                  ->where('user_id', $userId);
        }

        // Filter by action type
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->paginate(20);

        return view('admin.activity-logs', compact('logs'));
    }

    /**
     * Admin History page (UX-focused view with filters)
     */
    public function history(Request $request)
    {
        $query = ActivityLog::orderBy('created_at', 'desc');

        // Optional quick filters to match UI tabs
        $entity = $request->get('entity'); // order|product|user
        $sub = $request->get('sub'); // proses|cancel|selesai | ditambahkan|update|hapus | admin|superadmin|customer
        
        if ($entity) {
            if ($entity === 'order') {
                // Filter aktivitas terkait Order
                $query->where('subject_type', 'App\\Models\\CustomDesignOrder');
                
                if ($sub) {
                    $statusMap = [
                        'proses' => ['created', 'pending', 'processing', 'approved'],
                        'cancel' => ['cancelled', 'rejected'],
                        'selesai' => ['completed', 'delivered', 'finished'],
                    ];
                    if (isset($statusMap[$sub])) {
                        $query->where(function ($q) use ($statusMap, $sub) {
                            foreach ($statusMap[$sub] as $status) {
                                $q->orWhere('action', 'like', "%{$status}%")
                                  ->orWhere('description', 'like', "%{$status}%");
                            }
                        });
                    }
                }
            } elseif ($entity === 'product') {
                // Filter aktivitas terkait Product
                $query->where('subject_type', 'App\\Models\\Product');
                
                if ($sub) {
                    $actionMap = [
                        'ditambahkan' => 'created',
                        'update' => 'updated',
                        'hapus' => 'deleted',
                    ];
                    if (isset($actionMap[$sub])) {
                        $query->where('action', $actionMap[$sub]);
                    }
                }
            } elseif ($entity === 'user') {
                // Filter berdasarkan SIAPA yang melakukan aktivitas
                if ($sub === 'admin') {
                    // Aktivitas yang dilakukan oleh Admin (bukan superadmin)
                    $adminIds = Admin::where('role', 'admin')->pluck('id')->toArray();
                    $query->where('user_type', 'App\\Models\\Admin')
                          ->whereIn('user_id', $adminIds);
                } elseif ($sub === 'superadmin') {
                    // Aktivitas yang dilakukan oleh Superadmin
                    $superadminIds = Admin::where('role', 'super_admin')->pluck('id')->toArray();
                    $query->where('user_type', 'App\\Models\\Admin')
                          ->whereIn('user_id', $superadminIds);
                } elseif ($sub === 'customer') {
                    // Aktivitas yang dilakukan oleh Customer
                    $query->where('user_type', 'App\\Models\\User');
                } else {
                    // Semua user (tidak filter subject_type, hanya tampilkan semua)
                    // Tidak perlu filter tambahan
                }
            }
        }

        // Search in object name/description (simple: description LIKE)
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('subject_id', 'like', "%{$search}%");
            });
        }

        $perPage = (int) ($request->get('per_page', 25));
        if ($perPage < 5 || $perPage > 100) { $perPage = 25; }

        $logs = $query->paginate($perPage)->withQueryString();

        return view('admin.history', compact('logs'));
    }

    /**
     * Show detail of a specific activity log
     */
    public function historyDetail($id)
    {
        $log = ActivityLog::findOrFail($id);
        
        // Get user who performed the action
        $performer = null;
        if ($log->user_type === 'App\\Models\\Admin') {
            $performer = Admin::find($log->user_id);
        } else {
            $performer = User::find($log->user_id);
        }
        
        // Get subject (the object that was affected)
        $subject = null;
        if ($log->subject_type && $log->subject_id) {
            $subjectClass = $log->subject_type;
            if (class_exists($subjectClass)) {
                $subject = $subjectClass::find($log->subject_id);
            }
        }
        
        // Get related logs (same subject or same user in last 24h)
        $relatedLogs = ActivityLog::where('id', '!=', $log->id)
            ->where(function ($q) use ($log) {
                $q->where(function ($q2) use ($log) {
                    $q2->where('subject_type', $log->subject_type)
                       ->where('subject_id', $log->subject_id);
                })->orWhere(function ($q2) use ($log) {
                    $q2->where('user_type', $log->user_type)
                       ->where('user_id', $log->user_id)
                       ->where('created_at', '>=', $log->created_at->subDay());
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('admin.history-detail', compact('log', 'performer', 'subject', 'relatedLogs'));
    }

    /**
     * Export activity logs to Excel
     */
    public function export(Request $request)
    {
        $userType = $request->get('type');
        $filename = 'activity-logs-' . date('Y-m-d-His') . '.xlsx';
        
        return Excel::download(new ActivityLogsExport($userType), $filename);
    }
}