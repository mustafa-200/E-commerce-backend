<?php

use App\Http\Controllers\Auth\{LoginController, RegisterController, LogoutController};
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {


    Route::post('/register', RegisterController::class);
    Route::post('/login', LoginController::class);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', LogoutController::class);
    });

    require __DIR__ . '/admin.php';
    require __DIR__ . '/customer.php';
});
