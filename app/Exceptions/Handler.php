<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    // ... existing code ...

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthorizationException) {
            return response()->view('errors.403', [], 403);
        }

        if ($exception instanceof HttpException && $exception->getStatusCode() === 403) {
            return response()->view('errors.403', [], 403);
        }

        return parent::render($request, $exception);
    }
} 