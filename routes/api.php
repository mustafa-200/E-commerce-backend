<?php

use App\Http\Controllers\Auth\{LoginController, RegisterController, LogoutController};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/register', RegisterController::class);
        Route::post('/login', LoginController::class);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', LogoutController::class);
            Route::get('/me', function (Request $request) {
                return $request->user();
            });
        });
    });

    require __DIR__ . '/admin.php';
    require __DIR__ . '/customer.php';
});
