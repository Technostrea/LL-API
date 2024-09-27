<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'role.any' => \App\Http\Middleware\EnsureUserHasAnyRole::class,
            'permission' => \App\Http\Middleware\EnsureUserHasPermission::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Unauthorized: You do not have the required role(s).',
            ], 403);
        });

    })->create();
