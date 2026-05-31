<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;

// Оборачиваем ВСЕ роуты в группу 'web', чтобы включить сессии и куки
Route::middleware(['web'])->group(function () {

    // Публичный роут для входа
    Route::post('/login', [AuthController::class, 'login']);

    // Защищенные роуты внутри Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

});
