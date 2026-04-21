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
        // Add indexes to patrols table for frequently queried foreign keys
        Schema::table('patrols', function (Blueprint $table) {
            // Skip if indexes already exist
            if (!$this->indexExists('patrols', 'patrols_user_id_index')) {
                $table->index('user_id');
            }
            if (!$this->indexExists('patrols', 'patrols_employee_id_index')) {
                $table->index('employee_id');
            }
            if (!$this->indexExists('patrols', 'patrols_shift_id_index')) {
                $table->index('shift_id');
            }
            if (!$this->indexExists('patrols', 'patrols_location_id_index')) {
                $table->index('location_id');
            }
            if (!$this->indexExists('patrols', 'patrols_violation_id_index')) {
                $table->index('violation_id');
            }
            if (!$this->indexExists('patrols', 'patrols_qr_code_token_unique')) {
                $table->unique('qr_code_token');
            }
            if (!$this->indexExists('patrols', 'patrols_patrol_time_index')) {
                $table->index('patrol_time');
            }
        });

        // Add indexes to patrol_checkpoints table
        Schema::table('patrol_checkpoints', function (Blueprint $table) {
            if (!$this->indexExists('patrol_checkpoints', 'patrol_checkpoints_patrol_id_index')) {
                $table->index('patrol_id');
            }
        });

        // Add indexes to alerts table
        Schema::table('alerts', function (Blueprint $table) {
            if (!$this->indexExists('alerts', 'alerts_patrol_id_index')) {
                $table->index('patrol_id');
            }
        });

        // Add indexes to employees table
        Schema::table('employees', function (Blueprint $table) {
            if (!$this->indexExists('employees', 'employees_user_id_index')) {
                $table->index('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patrols', function (Blueprint $table) {
            $table->dropIndexIfExists(['user_id']);
            $table->dropIndexIfExists(['employee_id']);
            $table->dropIndexIfExists(['shift_id']);
            $table->dropIndexIfExists(['location_id']);
            $table->dropIndexIfExists(['violation_id']);
            $table->dropIndexIfExists(['qr_code_token']);
            $table->dropIndexIfExists(['patrol_time']);
        });

        Schema::table('patrol_checkpoints', function (Blueprint $table) {
            $table->dropIndexIfExists(['patrol_id']);
        });

        Schema::table('alerts', function (Blueprint $table) {
            $table->dropIndexIfExists(['patrol_id']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndexIfExists(['user_id']);
        });
    }

    /**
     * Helper method to check if index exists
     */
    private function indexExists($table, $index): bool
    {
        try {
            $indexes = \DB::select("SHOW INDEX FROM {$table}");
            foreach ($indexes as $idx) {
                if ($idx->Key_name === $index) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            // Silently fail if check doesn't work
        }
        return false;
    }
};
