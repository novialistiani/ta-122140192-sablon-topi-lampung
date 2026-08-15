<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Exports\UsersExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class UserManagementController extends Controller
{
    /**
     * Display user management page
     */
    public function index()
    {
        $currentAdmin = auth('admin')->user();
        $admins = Admin::orderBy('created_at', 'desc')->get();
        $users = User::orderBy('created_at', 'desc')->get();
        
        return view('admin.user-management', compact('admins', 'users', 'currentAdmin'));
    }

    /**
     * Show customer detail page
     */
    public function showCustomerDetail($id)
    {
        $customer = User::findOrFail($id);
        $currentAdmin = auth('admin')->user();
        
        return view('admin.customer-detail', compact('customer', 'currentAdmin'));
    }

    /**
     * Get admins list (API endpoint)
     */
    public function getAdmins()
    {
        $admins = Admin::select('id', 'name', 'email', 'role', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($admin) {
                return [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'role' => $admin->role_name,
                    'status' => $admin->status_name,
                    'created_at' => $admin->created_at->format('Y-m-d'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $admins,
            'total' => $admins->count()
        ]);
    }

    /**
     * Get customers list (API endpoint)
     */
    public function getCustomers()
    {
        $users = User::select('id', 'name', 'email', 'phone', 'address', 'city', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '-',
                    'address' => $user->address ?? '-',
                    'city' => $user->city ?? '-',
                    'status' => 'Active',
                    'created_at' => $user->created_at->format('Y-m-d'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $users,
            'total' => $users->count()
        ]);
    }

    /**
     * Get customer detail (API endpoint)
     */
    public function getCustomer($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'city' => $user->city,
                'province' => $user->province,
                'postal_code' => $user->postal_code,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Store new admin
     */
    public function storeAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:super_admin,admin',
            'status' => 'required|in:active,inactive',
        ]);

        $admin = Admin::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => $validated['status'],
        ]);

        // Log activity
        Admin::logActivity('create', 'Created new admin', null, $admin);

        return response()->json([
            'success' => true,
            'message' => 'Admin created successfully',
            'data' => $admin
        ], 201);
    }

    /**
     * Get single admin (API endpoint)
     */
    public function getAdmin($id)
    {
        $admin = Admin::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $admin
        ]);
    }

    /**
     * Update admin
     */
    public function updateAdmin(Request $request, $id)
    {
        $currentAdmin = auth('admin')->user();
        $admin = Admin::findOrFail($id);

        // Only super admin can edit other admins
        if (!$currentAdmin->isSuperAdmin() && $currentAdmin->id !== $admin->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Super Admin can edit other admin accounts.'
            ], 403);
        }

        $rules = [
            'name' => 'required|string|max:255',
        ];

        // Super admin editing others: can change email, role, and status (no password)
        if ($currentAdmin->isSuperAdmin() && $currentAdmin->id !== $admin->id) {
            $rules['email'] = ['required', 'email', Rule::unique('admins')->ignore($id)];
            $rules['role'] = ['required', 'in:super_admin,admin'];
            $rules['status'] = ['required', 'in:active,inactive'];
        }
        // Admin editing self: can change password, but not email, role, or status
        elseif ($currentAdmin->id === $admin->id) {
            $rules['password'] = 'nullable|min:6|confirmed';
        }

        $validated = $request->validate($rules);

        $admin->name = $validated['name'];
        
        // Only super admin can change email, role, and status
        if ($currentAdmin->isSuperAdmin() && $currentAdmin->id !== $admin->id) {
            if (isset($validated['email']) && $admin->email !== $validated['email']) {
                // Note: Email change is immediate. Future enhancement: Add confirmation workflow
                $admin->email = $validated['email'];
            }
            
            // Update role and status
            if (isset($validated['role'])) {
                $admin->role = $validated['role'];
            }
            
            if (isset($validated['status'])) {
                $admin->status = $validated['status'];
            }
        }
        
        // Admin can change own password
        if ($currentAdmin->id === $admin->id && !empty($validated['password'])) {
            $admin->password = Hash::make($validated['password']);
        }
        
        $admin->save();

        // Log activity
        Admin::logActivity('update', 'Updated admin information', null, $admin);

        return response()->json([
            'success' => true,
            'message' => 'Admin updated successfully',
            'data' => $admin
        ]);
    }

    /**
     * Delete admin
     */
    public function destroyAdmin($id)
    {
        $admin = Admin::findOrFail($id);

        // Prevent deleting the first admin (super admin)
        if ($admin->id === 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete super admin'
            ], 403);
        }

        // Log activity before deleting
        Admin::logActivity('delete', "Deleted admin: {$admin->name}", null, $admin);
        
        $admin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admin deleted successfully'
        ]);
    }

    /**
     * Store new customer - DISABLED
     * Customers can only register themselves, not created by admin
     */
    public function storeCustomer(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Customers cannot be created by admin. They must register themselves.'
        ], 403);
    }

    /**
     * Update customer - DISABLED
     * Customers manage their own profile, not edited by admin
     */
    public function updateCustomer(Request $request, $id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Customers cannot be edited by admin. They manage their own profile.'
        ], 403);
    }

    /**
     * Delete customer
     */
    public function destroyCustomer($id)
    {
        $user = User::findOrFail($id);
        
        // Log activity before deleting
        Admin::logActivity('delete', "Deleted customer: {$user->name}", null, $user);
        
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully'
        ]);
    }

    /**
     * Export admins to Excel
     */
    public function exportAdmins()
    {
        $filename = 'admins-' . date('Y-m-d-His') . '.xlsx';
        return Excel::download(new UsersExport('admin'), $filename);
    }

    /**
     * Export customers to Excel
     */
    public function exportCustomers()
    {
        $filename = 'customers-' . date('Y-m-d-His') . '.xlsx';
        return Excel::download(new UsersExport('customer'), $filename);
    }

    /**
     * Export single customer detail to PDF
     */
    public function exportCustomerPDF($id)
    {
        $customer = User::findOrFail($id);
        $currentAdmin = auth('admin')->user();
        
        // Generate PDF using view
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.customer-detail-pdf', compact('customer', 'currentAdmin'));
        
        $filename = 'customer-' . str_replace(' ', '-', strtolower($customer->name)) . '-' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export single customer detail to Excel
     */
    public function exportCustomerExcel($id)
    {
        $customer = User::findOrFail($id);
        
        $filename = 'customer-' . str_replace(' ', '-', strtolower($customer->name)) . '-' . date('Y-m-d') . '.xlsx';
        
        return Excel::download(new \App\Exports\CustomerDetailExport($customer), $filename);
    }
}
