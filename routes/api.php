<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LaporanController;
use App\Http\Controllers\API\KontakDaruratController;
use App\Http\Controllers\API\SkenarioPanggilanPalsuController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Endpoint yang bisa diakses publik (tanpa login)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    
    // Endpoint untuk mengubah status laporan oleh admin
    // Method: PATCH, URL: /api/admin/laporan/{id}/status
    Route::patch('/laporan/{laporan}/status', [App\Http\Controllers\API\LaporanController::class, 'updateStatus']);
    
    // Endpoint untuk CRUD User oleh admin
    // URL: /api/admin/users
    //Route::apiResource('/users', App\Http\Controllers\API\UserController::class);

    // Anda bisa tambahkan endpoint admin lainnya di sini
});

// Endpoint yang HARUS login terlebih dahulu
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Fitur Laporan (Create & Read)
    // Route::get('/laporan', [LaporanController::class, 'index']);
    // Route::post('/laporan', [LaporanController::class, 'store']);

    Route::apiResource('/laporan', LaporanController::class);
    Route::apiResource('/kontak-darurat', KontakDaruratController::class);
    // Route::get('/kontak-darurat', [KontakDaruratController::class, 'index']);
    // Route::post('/kontak-darurat', [KontakDaruratController::class, 'store']);
    // Route::delete('/kontak-darurat/{kontakDarurat}', [KontakDaruratController::class, 'destroy']);

    // Rute untuk Skenario Panggilan Palsu
    Route::get('/skenario-panggilan', [SkenarioPanggilanPalsuController::class, 'index']);
    
    // Anda bisa tambahkan route lain di sini
    // Contoh:
    // Route::apiResource('/kontak-darurat', KontakDaruratController::class);
    // Route::get('/skenario-panggilan', [SkenarioPanggilanPalsuController::class, 'index']);
});