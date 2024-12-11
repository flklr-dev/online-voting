<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (Auth::guard('admin')->check() && in_array('admin', $roles)) {
            return $next($request);
        }
        
        if (Auth::guard('student')->check() && in_array('student', $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
} 