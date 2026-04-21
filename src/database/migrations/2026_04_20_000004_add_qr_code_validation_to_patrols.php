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
        Schema::table('patrols', function (Blueprint $table) {
            $table->string('qr_code_token')->nullable()->after('user_id')->unique();
            $table->timestamp('qr_scanned_at')->nullable()->after('patrol_time');
            $table->ipAddress('qr_scanned_ip')->nullable()->after('qr_scanned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patrols', function (Blueprint $table) {
            $table->dropColumn(['qr_code_token', 'qr_scanned_at', 'qr_scanned_ip']);
        });
    }
};
