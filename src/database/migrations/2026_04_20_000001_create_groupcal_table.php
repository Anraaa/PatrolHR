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
        Schema::create('groupcal', function (Blueprint $table) {
            $table->string('date_shift', 20)->primary();
            $table->string('shfgroup', 3)->nullable();
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';
        });
        
        // Data akan di-seed melalui GroupcalSeeder
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groupcal');
    }
};