<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

/**
 * Migrasi awal MongoDB.
 *
 * MongoDB tidak punya kolom/schema tetap — dokumen bersifat fleksibel.
 * Yang dibuat di sini: collection + index yang dibutuhkan aplikasi.
 * Validasi bentuk data dilakukan di layer aplikasi (rules() Livewire & casts() model).
 */
return new class extends Migration
{
    protected $connection = 'mongodb';

    public function up(): void
    {
        // --- users ---------------------------------------------------------
        Schema::connection('mongodb')->create('users', function (Blueprint $collection): void {
            $collection->unique('email');
            $collection->index('role');
            $collection->index('name');
        });

        // --- password_reset_tokens ------------------------------------------
        Schema::connection('mongodb')->create('password_reset_tokens', function (Blueprint $collection): void {
            $collection->unique('email');
            $collection->index('created_at');
        });

        // --- sessions (driver session mongodb) -------------------------------
        Schema::connection('mongodb')->create('sessions', function (Blueprint $collection): void {
            $collection->unique('id');
            $collection->index('user_id');
            $collection->index('last_activity');
        });

        // --- cache + locks (driver cache mongodb) -----------------------------
        Schema::connection('mongodb')->create('cache', function (Blueprint $collection): void {
            $collection->unique('key');
            $collection->index('expiration');
        });
        Schema::connection('mongodb')->create('cache_locks', function (Blueprint $collection): void {
            $collection->unique('key');
        });

        // --- hps (stok HP) ---------------------------------------------------
        Schema::connection('mongodb')->create('hps', function (Blueprint $collection): void {
            $collection->unique('imei');
            $collection->index('status');
            $collection->index(['status' => 1, 'created_at' => -1]);
            $collection->index('ditambahkan_oleh');
        });

        // --- services ---------------------------------------------------------
        Schema::connection('mongodb')->create('services', function (Blueprint $collection): void {
            $collection->index('hp_id');
            $collection->index('tanggal_service');
        });

        // --- penjualans --------------------------------------------------------
        Schema::connection('mongodb')->create('penjualans', function (Blueprint $collection): void {
            $collection->index('tanggal_jual');
            $collection->index('user_id');
            $collection->index('nama_pembeli');
        });

        // --- detail_penjualans --------------------------------------------------
        Schema::connection('mongodb')->create('detail_penjualans', function (Blueprint $collection): void {
            $collection->index('penjualan_id');
            $collection->index('hp_id');
            $collection->index('created_at');
        });

        // --- cash_flows -----------------------------------------------------------
        Schema::connection('mongodb')->create('cash_flows', function (Blueprint $collection): void {
            $collection->index('date');
            $collection->index('type');
            $collection->index('category');
            $collection->index(['reference_type' => 1, 'reference_id' => 1]);
        });

        // --- arus_kas ----------------------------------------------------------------
        Schema::connection('mongodb')->create('arus_kas', function (Blueprint $collection): void {
            $collection->index('tanggal');
            $collection->index('jenis');
        });

        // --- salary_transactions ---------------------------------------------------------
        Schema::connection('mongodb')->create('salary_transactions', function (Blueprint $collection): void {
            $collection->index('user_id');
            $collection->index('admin_id');
            $collection->index('type');
        });

        // --- jobs (queue) — sync driver dipakai, tapi siapkan kalau nanti perlu -----------
        Schema::connection('mongodb')->create('jobs', function (Blueprint $collection): void {
            $collection->index('queue');
        });
        Schema::connection('mongodb')->create('job_batches', function (Blueprint $collection): void {
            $collection->unique('id');
        });
        Schema::connection('mongodb')->create('failed_jobs', function (Blueprint $collection): void {
            $collection->unique('uuid');
        });
    }

    public function down(): void
    {
        foreach ([
            'users', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks',
            'hps', 'services', 'penjualans', 'detail_penjualans',
            'cash_flows', 'arus_kas', 'salary_transactions',
            'jobs', 'job_batches', 'failed_jobs',
        ] as $name) {
            Schema::connection('mongodb')->dropIfExists($name);
        }
    }
};
