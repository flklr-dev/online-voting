<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class SessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $currentTime = Carbon::now();

            // Check last activity time
            if (Session::has('last_activity')) {
                $lastActivity = Carbon::parse(Session::get('last_activity'));
                
                // Check for inactivity timeout (5 minutes)
                if ($currentTime->diffInMinutes($lastActivity) >= config('session.inactivity_timeout')) {
                    Auth::logout();
                    Session::flush();
                    return redirect()->route('login')
                        ->with('error', 'Session expired due to inactivity. Please log in again.');
                }
            }

            // Check for absolute timeout (30 minutes)
            if (Session::has('session_start')) {
                $sessionStart = Carbon::parse(Session::get('session_start'));
                
                if ($currentTime->diffInMinutes($sessionStart) >= config('session.lifetime')) {
                    Auth::logout();
                    Session::flush();
                    return redirect()->route('login')
                        ->with('error', 'Session expired. Please log in again.');
                }
            }

            // Update last activity timestamp
            Session::put('last_activity', $currentTime);
        }

        return $next($request);
    }
} 