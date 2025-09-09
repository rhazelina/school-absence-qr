<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\GuruController;
use App\Http\Controllers\Web\SiswaController;
use App\Http\Controllers\Web\AbsensiController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    // User Management Routes
    Route::resource('users', UserController::class);

    // Guru Management Routes
    Route::resource('guru', GuruController::class);

    // Siswa Management Routes
    Route::resource('siswa', SiswaController::class);

    // Absensi Routes
    Route::get('absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::get('absensi/{absensi}', [AbsensiController::class, 'show'])->name('absensi.show');
    Route::get('absensi/export', [AbsensiController::class, 'export'])->name('absensi.export');
});
