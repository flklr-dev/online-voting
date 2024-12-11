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
        if (Auth::guard('admin')->check() || Auth::guard('student')->check()) {
            $currentTime = Carbon::now();
            
            // Always check for inactivity timeout
            if (Session::has('last_activity')) {
                $lastActivity = Carbon::parse(Session::get('last_activity'));
                
                if ($currentTime->diffInMinutes($lastActivity) >= config('session.inactivity_timeout')) {
                    $this->logout();
                    return redirect()->route('login')
                        ->with('error', 'Session expired due to inactivity. Please log in again.');
                }
            }

            // Always check for absolute session timeout
            if (Session::has('session_start')) {
                $sessionStart = Carbon::parse(Session::get('session_start'));
                
                if ($currentTime->diffInMinutes($sessionStart) >= config('session.lifetime')) {
                    $this->logout();
                    return redirect()->route('login')
                        ->with('error', 'Session expired. Please log in again.');
                }
            }

            // Update last activity for ALL requests (removed ajax check)
            Session::put('last_activity', $currentTime);
        }

        return $next($request);
    }

    private function logout()
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        } else {
            Auth::guard('student')->logout();
        }
        Session::flush();
    }
} 