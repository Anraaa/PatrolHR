<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE patrols MODIFY COLUMN signature LONGTEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE patrols MODIFY COLUMN signature VARCHAR(255) NULL');
    }
};
