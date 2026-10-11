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
            $table->string('warna')->nullable()->after('merk_model');
            $table->text('keterangan_minus')->nullable()->after('warna');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hps', function (Blueprint $table) {
            $table->dropColumn(['warna', 'keterangan_minus']);
        });
    }
};
