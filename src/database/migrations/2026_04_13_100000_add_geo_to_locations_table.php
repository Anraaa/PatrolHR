<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('uuid');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->unsignedSmallInteger('radius_meters')->default(15)->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'radius_meters']);
        });
    }
};
