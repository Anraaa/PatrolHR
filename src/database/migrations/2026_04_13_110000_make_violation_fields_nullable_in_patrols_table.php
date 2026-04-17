<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patrols', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->change();
            $table->foreignId('violation_id')->nullable()->change();
            $table->foreignId('action_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('patrols', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable(false)->change();
            $table->foreignId('violation_id')->nullable(false)->change();
            $table->foreignId('action_id')->nullable(false)->change();
        });
    }
};
