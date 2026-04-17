<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('uuid', 36)->after('id')->nullable();
        });

        // Generate UUID for existing locations
        foreach (\App\Models\Location::all() as $location) {
            \Illuminate\Support\Facades\DB::table('locations')
                ->where('id', $location->id)
                ->update(['uuid' => Str::uuid()->toString()]);
        }

        Schema::table('locations', function (Blueprint $table) {
            $table->string('uuid', 36)->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
