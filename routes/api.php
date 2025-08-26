<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Category\CategoryController;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');

Route::middleware('auth:sanctum')->group(function () {
    Route::resource('users', UserController::class);

    Route::middleware('admin')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['index', 'show']);
    });
});

Route::resource('categories', CategoryController::class)->only(['index', 'show']);