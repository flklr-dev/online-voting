<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        $userRole = session('user_role');

        if ($userRole !== $role) {
            abort(403);
        }

        return $next($request);
    }
} 