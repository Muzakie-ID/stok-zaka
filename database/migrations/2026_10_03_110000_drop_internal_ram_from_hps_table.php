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
            $table->dropColumn(['internal', 'ram']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hps', function (Blueprint $table) {
            $table->string('internal')->nullable()->after('merk_model');
            $table->string('ram')->nullable()->after('internal');
        });
    }
};
