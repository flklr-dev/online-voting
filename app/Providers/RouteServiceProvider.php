<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';
    public const STUDENT_HOME = '/student-home';

    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    protected function redirectTo()
    {
        if (Auth::guard('admin')->check()) {
            return self::HOME;
        } elseif (Auth::guard('student')->check()) {
            return self::STUDENT_HOME;
        }
        
        return '/';
    }
} 