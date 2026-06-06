<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\RepairController;
use App\Http\Controllers\CarController;
use Illuminate\Support\Facades\Route;

// Оборачиваем ВСЕ роуты в группу 'web', чтобы включить сессии и куки
Route::middleware(['web'])->group(function () {

    // Публичный роут для входа
    Route::post('/login', [AuthController::class, 'login']);

    // Защищенные роуты внутри Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

       Route::get('/clients', [ClientController::class, 'index']);
       Route::post('/clients', [ClientController::class, 'store']);
       Route::get('/clients/{id}', [ClientController::class, 'show']);
       Route::put('/clients/{id}', [ClientController::class, 'update']);

       Route::get('/repairs', [RepairController::class, 'index']);
       Route::post('/repairs', [RepairController::class, 'store']);
       Route::patch('/repairs/{id}/status', [RepairController::class, 'updateStatus']);


       Route::post('/cars', [CarController::class, 'store']);
    });

});
