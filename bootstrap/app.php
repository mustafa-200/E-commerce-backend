<?php

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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'resolve.user' => \App\Http\Middleware\ResolveAuthenticatedUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (\Illuminate\Database\UniqueConstraintViolationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'القيمة المدخلة مستخدمة من قبل، برجاء استخدام قيمة مختلفة.',
                ], 409);
            }
        });

        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            if ($request->is('api/*') && $e->getCode() === '23000') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'لا يمكن إتمام هذا الإجراء بسبب ارتباط البيانات ببيانات أخرى.',
                ], 409);
            }
        });

    })->create();