<?php

use App\Http\Controllers\PublicServiceController;
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
Route::get('/layanan', [PublicServiceController::class, 'trackingPage'])
    ->name('layanan');
Route::post('/layanan/check', [PublicServiceController::class, 'checkStatus'])
    ->name('public.tracking.check');

// Application Routes - KTP, KK, Akta, dll
Route::get('/layanan/apply/{type}', [App\Http\Controllers\Public\LayananController::class, 'showForm'])
    ->name('apply.form');

// Direct aliases — semua redirect ke form baru (node-based) di /layanan/{slug}
// Ini menyatukan dua jalur (/ktp dan /layanan/ktp) menjadi satu jalur saja.
Route::get('/ktp', fn() => redirect('/layanan/ktp', 301))->name('apply.ktp');
Route::get('/kk', fn() => redirect('/layanan/kk', 301))->name('apply.kk');
Route::get('/akta', fn() => redirect('/layanan/akta', 301))->name('apply.akta');
Route::get('/sktm', fn() => redirect('/layanan/sktm', 301))->name('apply.sktm');
Route::get('/domisili', fn() => redirect('/layanan/domisili', 301))->name('apply.domisili');
Route::get('/nikah', fn() => redirect('/layanan/nikah', 301))->name('apply.nikah');
Route::get('/bpjs', fn() => redirect('/layanan/bpjs', 301))->name('apply.bpjs');

Route::post('/layanan/apply', [App\Http\Controllers\Public\LayananController::class, 'store'])
    ->name('apply.store');

// NEW: Dynamic Decision Tree Route (slug-based, backward compatible)
Route::get('/layanan/{slug}', [App\Http\Controllers\Public\LayananController::class, 'showLayanan'])
    ->name('apply.layanan');
Route::post('/layanan/node/submit', [App\Http\Controllers\Public\LayananController::class, 'storeNodeBased'])
    ->name('apply.node.store');
