<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'otp.verified' => \App\Http\Middleware\OtpVerifiedMiddleware::class,
            'sanitizeInput' => \App\Http\Middleware\SanitizeInput::class,
            'fileTypeCheck' => \App\Http\Middleware\FileTypeCheck::class,
        ]);
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle validation exceptions for API
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Handle unauthenticated requests for API routes
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated - Bearer token required',
                    'error' => 'No token provided or token expired',
                ], 401);
            }
        });

        // Handle authorization exceptions for API routes (403)
        $exceptions->render(function (HttpException $e, $request) {
            if (($request->expectsJson() || $request->is('api/*')) && $e->getStatusCode() === 403) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden - You do not have permission',
                    'error' => 'Insufficient permissions',
                ], 403);
            }
        });
    })
    ->create();
