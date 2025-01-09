<?php

use Illuminate\Auth\Middleware\Authenticate;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Http\Middleware\EnsureTokenIsValid;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
// Middleware groups can be defined here
// $middleware->group('api', [
//     // Add middleware specific to API routes
//     EnsureTokenIsValid::class,
// ]);

// // Define aliases for route-specific middleware
// $middleware->alias([
//     'auth' => Authenticate::class,
//     'ensure.token.valid' => EnsureTokenIsValid::class,
// ]);

$middleware->alias([
    // 'auth' => Authenticate::class,
    'auth.merchant' => \Illuminate\Auth\Middleware\Authenticate::class . ':merchant',
    'auth.superadmin' => \Illuminate\Auth\Middleware\Authenticate::class . ':superadmin',
    'ensure.token.valid' => EnsureTokenIsValid::class,
]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
// Custom exception handling for API
$exceptions->render(function (NotFoundHttpException $e, Request $request) {
    if ($request->is('api/*')) {
        return response()->json([
            'status' => 'false',
            'message' => 'Route Not found',
        ], 404);
    }
});

$exceptions->render(function (AuthenticationException $e, Request $request) {
    if ($request->is('api/*')) {
        return response()->json([
            'status' => 'false',
            'message' => 'Unauthorized: Token not provided or invalid',
        ], 401);
    }
});



    })->create();
