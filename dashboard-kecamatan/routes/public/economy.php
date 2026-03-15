<?php

use App\Http\Controllers\EconomyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Economy Routes (UMKM, Jasa, Loker)
|--------------------------------------------------------------------------
|
| Route untuk halaman ekonomi - UMKM Rakyat, Lowongan Kerja, dan Jasa
|
*/

// Economy Hub (Main)
Route::get('/ekonomi', [EconomyController::class, 'index'])
    ->name('economy.index');

// Pendaftaran - HARUS di sebelum route /ekonomi/{id}
Route::get('/ekonomi/daftar', [EconomyController::class, 'create'])
    ->name('economy.create');
Route::post('/ekonomi/daftar', [EconomyController::class, 'store'])
    ->name('economy.store');

// Detail
Route::get('/ekonomi/{id}', [EconomyController::class, 'show'])
    ->name('economy.show');

// Redirects for backward compatibility
Route::get('/umkm', function () {
    return redirect()->route('economy.index', ['tab' => 'produk']);
})->name('public.umkm.index');

Route::get('/kerja', function () {
    return redirect()->route('economy.index', ['tab' => 'jasa']);
});
