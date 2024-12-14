<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAuthSession
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is trying to access admin routes
        if ($request->is('home*') || $request->is('admin*')) {
            if (!Auth::guard('admin')->check()) {
                return redirect()->route('login')->with('error', 'Please login to access this page.');
            }
        }

        // Check if user is trying to access student routes
        if ($request->is('student*') || $request->is('vote*')) {
            if (!Auth::guard('student')->check()) {
                return redirect()->route('login')->with('error', 'Please login to access this page.');
            }
        }

        return $next($request);
    }
}
