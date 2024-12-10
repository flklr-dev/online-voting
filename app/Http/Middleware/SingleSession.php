<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SingleSession
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $sessionId = Session::getId();
            
            // Generate a unique key for the user's session
            $userSessionKey = "user_session_{$user->getAuthIdentifier()}";
            
            // Check if user has another active session
            $existingSession = Cache::get($userSessionKey);
            
            if ($existingSession && $existingSession !== $sessionId) {
                Auth::logout();
                Session::flush();
                return redirect()->route('login')
                    ->with('error', 'Your account is already logged in on another device.');
            }
            
            // Store current session ID with TTL matching session lifetime
            Cache::put($userSessionKey, $sessionId, now()->addMinutes(config('session.lifetime')));
        }

        return $next($request);
    }
} 