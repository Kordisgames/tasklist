<?php

use App\Http\Controllers\Auth\AuthApiController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Только гостевые роуты, формата: http://localhost/api/v1/...
function guestRoute()
{
    Route::post('/register', [AuthApiController::class, 'register']);
    Route::post('/login', [AuthApiController::class, 'login']);

    Route::middleware('auth:sanctum')->post('/logout', [AuthApiController::class, 'logout']);
}

function authRoute()
{
    Route::get('/tasks', [TaskController::class, 'index']); // Получение списка задач
    Route::post('/tasks', [TaskController::class, 'store']); // Создание задачи
    Route::put('/tasks/{task}', [TaskController::class, 'update']); // Обновление задачи
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']); // Удаление задачи
}

Route::prefix('v1')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        authRoute();
    });

    // http://localhost/api/v1/auth/... (register|login|logout)
    Route::prefix('auth')->group(function () {
        guestRoute();
    });
});
