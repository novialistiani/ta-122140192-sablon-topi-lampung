<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if admin is logged in
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')
                ->with('error', 'Please login to access this page.');
        }

        $admin = Auth::guard('admin')->user();

        // If no specific roles required, just check if authenticated
        if (empty($roles)) {
            return $next($request);
        }

        // Check if admin has any of the required roles
        if ($admin->hasAnyRole($roles)) {
            return $next($request);
        }

        // Admin doesn't have required role
        abort(403, 'You do not have permission to access this resource.');
    }
}
