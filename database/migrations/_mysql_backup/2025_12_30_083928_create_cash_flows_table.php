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
        Schema::create('cash_flows', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('type', ['income', 'expense']); // Pemasukan / Pengeluaran
            $table->string('category'); // modal_awal, stok, penjualan, gaji, operasional, prive, dll
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->nullableMorphs('reference'); // Untuk link ke model lain (Hp, Penjualan, dll)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_flows');
    }
};
