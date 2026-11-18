<?php

use App\Http\Controllers\PpdbController;
use App\Http\Controllers\PengumumanController;
use Illuminate\Support\Facades\Route;

// Pendaftaran PPDB tetap terbuka tanpa login.
Route::post('ppdb', [
    PpdbController::class,
    'store',
]);

// Pengumuman publik: controller harus menyaring pengumuman terbit.
Route::get('pengumuman/publik', [
    PengumumanController::class,
    'publik',
]);

Route::get('pengumuman/slug/{slug}', [
    PengumumanController::class,
    'showBySlug',
]);