<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
    public function render($request, Throwable $e)
    {
        // Handle 403 Forbidden errors
        if ($e instanceof HttpException && $e->getStatusCode() === 403) {
            return response()->view('errors.403', [
                'message' => 'You do not have permission to access this page.'
            ], 403);
        }

        // Handle 404 Not Found errors
        if ($e instanceof HttpException && $e->getStatusCode() === 404) {
            return response()->view('errors.404', [
                'message' => 'The page you are looking for could not be found.'
            ], 404);
        }

        // Handle authentication errors
        if ($e instanceof AuthenticationException) {
            return redirect()->route('login')
                ->with('error', 'Please log in to access this page.');
        }

        // Handle all other errors with a generic 500 error page
        if (!config('app.debug')) {
            return response()->view('errors.500', [
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }

        return parent::render($request, $e);
    }
} 