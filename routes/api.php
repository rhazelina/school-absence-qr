<?php

use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\JadwalPembelajaranController;
use App\Http\Controllers\API\KelasController;
use App\Http\Controllers\API\MataPelajaranController;
use App\Http\Controllers\API\QrCodeController;
use App\Http\Controllers\API\SiswaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Include authentication routes
require __DIR__ . '/API/auth.php';

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('students', SiswaController::class)
        ->parameters(['students' => 'siswa']);

    Route::apiResource('lessons', MataPelajaranController::class)
        ->parameters(['lessons' => 'mataPelajaran']);

    Route::apiResource('classes', KelasController::class)
        ->only(['index', 'show'])
        ->parameters(['classes' => 'kelas']);

    Route::apiResource('schedules', JadwalPembelajaranController::class)
        ->only(['index', 'show'])
        ->parameters(['schedules' => 'jadwalPembelajaran']);

    Route::get('qr-codes/active', [QrCodeController::class, 'active']);

    Route::apiResource('qr-codes', QrCodeController::class)
        ->only(['index', 'store', 'show', 'destroy']);

    Route::post('attendance/scan', [AttendanceController::class, 'scan']);
    Route::get('attendance/records', [AttendanceController::class, 'records']);
    Route::get('attendance/summary', [AttendanceController::class, 'summary']);
});
