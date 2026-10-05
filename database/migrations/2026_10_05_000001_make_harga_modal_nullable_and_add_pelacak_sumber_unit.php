<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hps', function (Blueprint $table) {
            // Karyawan boleh input stok tanpa harga modal -> kolom jadi nullable
            $table->decimal('harga_beli_awal', 15, 2)->nullable()->change();
            $table->decimal('total_modal', 15, 2)->nullable()->change();
            // Pelacak siapa yang menambahkan unit stok ini
            $table->foreignId('ditambahkan_oleh')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });

        Schema::table('penjualans', function (Blueprint $table) {
            // Siapa (user) yang mencatat penjualan ini
            $table->foreignId('user_id')->nullable()->after('nama_pembeli')->constrained('users')->nullOnDelete();
        });

        Schema::table('detail_penjualans', function (Blueprint $table) {
            // Unit tanpa modal yang terjual → modal & laba bisa null (dihitung saat admin melengkapi modal)
            $table->decimal('modal_terakhir', 15, 2)->nullable()->change();
            $table->decimal('laba_rugi', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('hps', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ditambahkan_oleh');
            $table->decimal('harga_beli_awal', 15, 2)->nullable(false)->change();
            $table->decimal('total_modal', 15, 2)->nullable(false)->change();
        });

        Schema::table('detail_penjualans', function (Blueprint $table) {
            $table->decimal('modal_terakhir', 15, 2)->nullable(false)->change();
            $table->decimal('laba_rugi', 15, 2)->nullable(false)->change();
        });
    }
};
