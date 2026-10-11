<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catatan upah/saldo karyawan.
     * type = 'earn'  : admin memberi upah (saldo bertambah)
     * type = 'paid'  : admin mencairkan/transfer upah (saldo berkurang)
     */
    public function up(): void
    {
        Schema::create('salary_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['earn', 'paid']);
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_transactions');
    }
};
