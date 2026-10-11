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
        Schema::create('hps', function (Blueprint $table) {
            $table->id();
            $table->string('imei')->unique();
            $table->string('merk_model');
            $table->string('sumber_beli')->nullable();
            $table->decimal('harga_beli_awal', 15, 2);
            $table->decimal('total_modal', 15, 2);
            $table->enum('status', ['READY', 'SERVICE', 'SOLD'])->default('READY');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hps');
    }
};
