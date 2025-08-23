<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('api.register');
    Route::post('login', [AuthController::class, 'login'])->name('api.login');
    
    // Protected routes
    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
        Route::post('refresh', [AuthController::class, 'refresh'])->name('api.refresh');
        Route::get('me', [AuthController::class, 'me'])->name('api.me');
    });
});

// Todo routes (Protected)
Route::middleware('auth')->prefix('todos')->group(function () {
    Route::get('/', [TodoController::class, 'index'])->name('api.todos.index');
    Route::post('/', [TodoController::class, 'store'])->name('api.todos.store');
    Route::get('/stats', [TodoController::class, 'stats'])->name('api.todos.stats');
    Route::get('/{id}', [TodoController::class, 'show'])->name('api.todos.show');
    Route::put('/{id}', [TodoController::class, 'update'])->name('api.todos.update');
    Route::delete('/{id}', [TodoController::class, 'destroy'])->name('api.todos.destroy');
    Route::patch('/{id}/complete', [TodoController::class, 'markCompleted'])->name('api.todos.complete');
    Route::patch('/{id}/pending', [TodoController::class, 'markPending'])->name('api.todos.pending');
});
