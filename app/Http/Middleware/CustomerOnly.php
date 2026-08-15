<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerOnly
{
    /**
     * Handle an incoming request.
     * Ensure only customers (not admins) can access customer-specific routes.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated as admin (using 'admin' guard)
        if (auth()->guard('admin')->check()) {
            // If admin is trying to access customer routes, redirect to admin dashboard
            return redirect()->route('admin.dashboard')
                ->with('error', 'Admin tidak dapat melakukan aksi pelanggan. Silakan gunakan akun pelanggan.');
        }

        // Check if user is authenticated as customer (using default 'web' guard)
        if (!auth()->check()) {
            // Not authenticated, redirect to login
            return redirect()->route('login')
                ->with('error', 'Silakan login sebagai pelanggan untuk melanjutkan.');
        }

        return $next($request);
    }
}
