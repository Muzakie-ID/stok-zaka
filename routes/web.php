<?php

use Illuminate\Support\Facades\Route;

use App\Livewire\Home;
use App\Livewire\Stok;
use App\Livewire\Penjualan;
use App\Livewire\ServiceIndex;
use App\Livewire\Laporan;
use App\Livewire\Keuangan;
use App\Livewire\KelolaUser;

Route::middleware('auth')->group(function () {
    Route::get('/', Home::class)->name('dashboard');
    Route::get('/stok', Stok::class);
    Route::get('/penjualan', Penjualan::class);

    // Khusus admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/service', ServiceIndex::class);
        Route::get('/keuangan', Keuangan::class);
        Route::get('/laporan', Laporan::class);
        Route::get('/kelola-user', KelolaUser::class)->name('kelola-user');
    });
});