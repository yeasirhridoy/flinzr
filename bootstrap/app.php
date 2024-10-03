<?php

use App\Http\Middleware\VerifyToken;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(VerifyToken::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        if (request()->expectsJson()) {
            $exceptions->render(function (AuthenticationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error' => null,
                ], 401);
            });
        }
    })->create();
