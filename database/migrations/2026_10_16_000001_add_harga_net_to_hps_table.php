<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom harga_net (harga pas) pada stok HP.
     * Dipakai sebagai default harga jual saat checkout penjualan.
     */
    public function up(): void
    {
        Schema::table('hps', function (Blueprint $table) {
            $table->decimal('harga_net', 15, 2)->nullable()->after('total_modal');
        });
    }

    public function down(): void
    {
        Schema::table('hps', function (Blueprint $table) {
            $table->dropColumn('harga_net');
        });
    }
};
