<?php

use App\Http\Controllers\Public\LayananController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Service Routes (Layanan Publik)
|--------------------------------------------------------------------------
|
| Route untuk halaman layanan publik - Menggunakan controller untuk
| performance yang lebih baik dengan caching
|
*/

// Main Layanan Portal
Route::get('/layanan', [LayananController::class, 'index'])
    ->name('layanan')
    ->middleware('cache.headers:public;max_age=300');

// Application Routes - KTP, KK, Akta, dll
Route::get('/ktp', [LayananController::class, 'ktp'])
    ->name('apply.ktp')
    ->middleware('cache.headers:public;max_age=300');

Route::get('/kk', [LayananController::class, 'kk'])
    ->name('apply.kk')
    ->middleware('cache.headers:public;max_age=300');

Route::get('/akta', [LayananController::class, 'akta'])
    ->name('apply.akta')
    ->middleware('cache.headers:public;max_age=300');

Route::get('/sktm', [LayananController::class, 'sktm'])
    ->name('apply.sktm')
    ->middleware('cache.headers:public;max_age=300');

Route::get('/domisili', [LayananController::class, 'domisili'])
    ->name('apply.domisili')
    ->middleware('cache.headers:public;max_age=300');

Route::get('/nikah', [LayananController::class, 'nikah'])
    ->name('apply.nikah')
    ->middleware('cache.headers:public;max_age=300');

Route::get('/bpjs', [LayananController::class, 'bpjs'])
    ->name('apply.bpjs')
    ->middleware('cache.headers:public;max_age=300');
