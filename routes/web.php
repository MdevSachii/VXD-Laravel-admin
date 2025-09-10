<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WPAuthController;
use App\Http\Controllers\WPPostController;

Route::get('/auth/wp', [WPAuthController::class, 'redirect'])->name('wp.login');
Route::get('/auth/wp/callback', [WPAuthController::class, 'callback'])->name('wp.callback');

Route::get('/', function () {
    return view('welcome');
})->middleware('wp.auth');

Route::middleware(['web','wp.auth'])
    ->prefix('api')->group(function () {
        Route::get('/posts', [WPPostController::class, 'index']);
        Route::get('/posts/{id}', [WPPostController::class, 'show']);
        Route::post('/posts', [WPPostController::class, 'store']);
        Route::put('/posts/{id}', [WPPostController::class, 'update']);
        Route::delete('/posts/{id}', [WPPostController::class, 'destroy']);
        Route::post('/posts/{id}/priority', [WPPostController::class, 'setPriority']);
    });