<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create super_admin role
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
        $adminUser->assignRole('super_admin');

        // Create employee untuk admin user
        Employee::firstOrCreate(
            ['nip' => 'NIP00001'],
            [
                'user_id' => $adminUser->id,
                'name' => 'Admin',
                'shfgroup' => 'A',
            ]
        );

        $this->call([
            GroupcalSeeder::class,
        ]);
    }
}
