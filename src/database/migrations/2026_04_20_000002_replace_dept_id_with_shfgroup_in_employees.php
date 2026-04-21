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
        Schema::table('employees', function (Blueprint $table) {
            // Hapus foreign key constraint jika ada
            $table->dropForeign(['dept_id']);
            
            // Hapus kolom dept_id
            $table->dropColumn('dept_id');
            
            // Tambah kolom shfgroup
            $table->string('shfgroup', 3)->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Hapus kolom shfgroup
            $table->dropColumn('shfgroup');
            
            // Kembalikan kolom dept_id
            $table->foreignId('dept_id')->nullable()->constrained('departments')->onDelete('set null');
        });
    }
};
