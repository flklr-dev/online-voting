<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log detailed error information for debugging
            Log::error('Error occurred:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Handle 404 (Not Found) errors
        if ($exception instanceof NotFoundHttpException) {
            if (Auth::guard('admin')->check()) {
                return redirect()->route('home')->with('error', 'Page not found.');
            }
            
            if (Auth::guard('student')->check()) {
                return redirect()->route('student-home')->with('error', 'Page not found.');
            }
        }

        return parent::render($request, $exception);
    }
} 