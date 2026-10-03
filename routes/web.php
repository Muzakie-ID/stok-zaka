<?php

use Illuminate\Support\Facades\Route;

use App\Livewire\Home;
use App\Livewire\Stok;
use App\Livewire\Penjualan;
use App\Livewire\ServiceIndex;
use App\Livewire\Laporan;
use App\Livewire\Keuangan;
use App\Livewire\KelolaUser;
use App\Livewire\SaldoKaryawan;
use App\Livewire\SaldoSaya;
use App\Livewire\LabelBarcode;

Route::middleware('auth')->group(function () {
    Route::get('/', Home::class)->name('dashboard');
    Route::get('/stok', Stok::class);
    Route::get('/penjualan', Penjualan::class);
    Route::get('/saldo-saya', SaldoSaya::class)->name('saldo-saya');

    // Khusus admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/service', ServiceIndex::class);
        Route::get('/keuangan', Keuangan::class);
        Route::get('/laporan', Laporan::class);
        Route::get('/kelola-user', KelolaUser::class)->name('kelola-user');
        Route::get('/saldo-karyawan', SaldoKaryawan::class)->name('saldo-karyawan');
        Route::get('/label', LabelBarcode::class)->name('label');
    });
});