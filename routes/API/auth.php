<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Authentication Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'auth'], function () {
    // Admin login route
    Route::post('/admin/login', [AuthController::class, 'adminLogin']);

    // Staff/Management login route
    Route::post('/staff/login', [AuthController::class, 'staffLogin']);

    // Guru/Wali Kelas login route
    Route::post('/guru/login', [AuthController::class, 'guruLogin']);

    // Siswa login route
    Route::post('/siswa/login', [AuthController::class, 'siswaLogin']);

    // Pengurus Kelas login route
    Route::post('/pengurus-kelas/login', [AuthController::class, 'pengurusKelasLogin']);

    // Protected logout route
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});
