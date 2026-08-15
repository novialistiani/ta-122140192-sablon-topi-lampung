<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminAuthController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm()
    {
        // Jika admin sudah login, redirect ke dashboard
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * Handle admin login request.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:admins,email',
            'password' => 'required|min:6',
        ], [
            'email.exists' => 'Email tidak ditemukan.',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Log login activity
            \App\Models\Admin::logActivity(
                'login',
                'Admin logged in successfully'
            );
            
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'password' => 'Password salah.',
        ])->onlyInput('email');
    }

    /**
     * Handle admin logout request.
     */
    public function logout(Request $request)
    {
        // Log logout activity before logging out
        \App\Models\Admin::logActivity(
            'logout',
            'Admin logged out'
        );
        
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    /**
     * Show the admin dashboard.
     */
    public function dashboard()
    {
        return view('admin.dashboard');
    }
}