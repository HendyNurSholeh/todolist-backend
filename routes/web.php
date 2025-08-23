<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'Laravel JWT API',
        'version' => '1.0.0',
        'status' => 'running'
    ]);
});

// Fallback login route untuk menghindari error "Route [login] not defined"
Route::get('/login', function () {
    return response()->json([
        'message' => 'Unauthenticated.'
    ], 401);
})->name('login');