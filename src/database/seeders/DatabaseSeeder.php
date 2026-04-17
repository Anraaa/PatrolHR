<?php

namespace Database\Seeders;

use App\Models\Action;
use App\Models\Alert;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Location;
use App\Models\Patrol;
use App\Models\PatrolAttachment;
use App\Models\Shift;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create super_admin role
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        $admin->assignRole('super_admin');

        // Create manager & PIC users
        $manager = User::factory()->create([
            'name' => 'Manager Keamanan',
            'email' => 'manager@checksheet.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
        ]);

        $pics = collect();
        foreach (['Budi Santoso', 'Andi Wijaya', 'Rini Hartati'] as $name) {
            $pics->push(User::factory()->create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@checksheet.com',
                'password' => Hash::make('password'),
                'role' => 'pic',
            ]));
        }

        // Departments
        $departments = collect([
            'Produksi', 'Quality Control', 'Warehouse', 'Maintenance',
            'HRD', 'Finance', 'IT', 'Logistik',
        ])->map(fn ($name) => Department::create(['name' => $name]));

        // Employees
        $employees = collect();
        $nipCounter = 1;
        foreach ($departments as $dept) {
            for ($i = 0; $i < 5; $i++) {
                $employees->push(Employee::create([
                    'nip' => 'NIP' . str_pad($nipCounter++, 5, '0', STR_PAD_LEFT),
                    'name' => fake('id_ID')->name(),
                    'dept_id' => $dept->id,
                ]));
            }
        }

        // Shifts
        $shifts = collect([
            'Shift 1 (06:00 - 14:00)',
            'Shift 2 (14:00 - 22:00)',
            'Shift 3 (22:00 - 06:00)',
        ])->map(fn ($name) => Shift::create(['name' => $name]));

        // Locations
        $locations = collect([
            'Gerbang Utama', 'Area Produksi A', 'Area Produksi B',
            'Gudang Bahan Baku', 'Gudang Finished Goods', 'Kantor Utama',
            'Parkiran Karyawan', 'Area Loading Dock', 'Kantin', 'Musholla',
        ])->map(fn ($name) => Location::create(['name' => $name]));

        // Violations
        $violations = collect([
            'Tidak memakai APD', 'Merokok di area terlarang', 'Tidur saat jam kerja',
            'Tidak membawa ID Card', 'Meninggalkan area kerja tanpa izin',
            'Menggunakan HP saat bekerja', 'Tidak mengikuti prosedur K3',
            'Parkir tidak pada tempatnya', 'Membawa makanan ke area produksi',
            'Tidak melakukan absensi',
        ])->map(fn ($name) => Violation::create(['name' => $name]));

        // Actions
        $actions = collect([
            'Pengarahan', 'Teguran Lisan', 'Teguran Tertulis',
            'Surat Peringatan 1', 'Surat Peringatan 2', 'Surat Peringatan 3',
        ])->map(fn ($name) => Action::create(['name' => $name]));

        // Patrols (30 dummy records)
        $patrols = collect();
        for ($i = 0; $i < 30; $i++) {
            $patrols->push(Patrol::create([
                'user_id' => $pics->random()->id,
                'employee_id' => $employees->random()->id,
                'shift_id' => $shifts->random()->id,
                'location_id' => $locations->random()->id,
                'violation_id' => $violations->random()->id,
                'action_id' => $actions->random()->id,
                'description' => fake('id_ID')->sentence(10),
                'patrol_time' => fake()->dateTimeBetween('-30 days', 'now'),
            ]));
        }

        // Patrol Attachments (some patrols get attachments)
        foreach ($patrols->take(15) as $patrol) {
            PatrolAttachment::create([
                'patrol_id' => $patrol->id,
                'file_path' => 'patrol-attachments/sample-' . $patrol->id . '.jpg',
                'type' => fake()->randomElement(['photo', 'signature']),
            ]);
        }

        // Alerts
        foreach ($patrols->take(20) as $patrol) {
            Alert::create([
                'user_id' => $manager->id,
                'patrol_id' => $patrol->id,
                'message' => 'Pelanggaran terdeteksi: ' . $patrol->violation->name . ' oleh ' . $patrol->employee->name . ' di ' . $patrol->location->name,
                'status' => fake()->randomElement(['sent', 'read']),
            ]);
        }
    }
}
