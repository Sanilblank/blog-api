<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Category\CategoryController;
use App\Http\Controllers\Api\Comment\CommentController;
use App\Http\Controllers\Api\Post\PostController;
use App\Http\Controllers\Api\Tag\TagController;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');

Route::middleware('auth:sanctum')->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('posts', PostController::class)->except(['index', 'show']);
    Route::as('posts.')->prefix('posts/{post}')->controller(CommentController::class)->group(function () {
        Route::resource('comments', CommentController::class)->except(['index', 'show']);
    });

    Route::middleware('admin')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['index', 'show']);
        Route::resource('tags', TagController::class)->except(['index', 'show']);
    });
});

Route::resource('categories', CategoryController::class)->only(['index', 'show']);
Route::resource('tags', TagController::class)->only(['index', 'show']);
Route::resource('posts', PostController::class)->only(['index', 'show']);

Route::as('posts.')->prefix('posts/{post}')->controller(CommentController::class)->group(function () {
    Route::resource('comments', CommentController::class)->only(['index', 'show']);
});

