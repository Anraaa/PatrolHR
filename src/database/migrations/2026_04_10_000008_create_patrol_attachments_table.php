<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrol_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patrol_id')->constrained('patrols')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('type'); // photo, signature
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrol_attachments');
    }
};
