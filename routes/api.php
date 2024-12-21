<?php
use App\Http\Controllers\EncryptionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Middleware\AuthJWT;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
Route::get('/encrypt', [EncryptionController::class, 'encryptData']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::apiResource('tasks', TaskController::class)->middleware([
    AuthJWT::class,
    RoleMiddleware::class
]);
