<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Include authentication routes
require __DIR__ . '/API/auth.php';

// Protected route to get current user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
