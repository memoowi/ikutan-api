<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, $request) {
        if ($request->is('api/*')) {
            // Handle Validation Errors
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Validation Failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            // Handle Authentication Errors
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Unauthenticated',
                ], 401);
            }

            // Handle Authorization Errors
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Generic fallback for other exceptions
            return response()->json([
                'status' => 'Error',
                'message' => $e->getMessage() ?: 'Server Error',
            ], $e->getCode() ?: 500);
        }
    });
    })->create();
