<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Subsidiary;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Tora Holding Subsidiaries (Companies)
        $toraManufacturing = Subsidiary::create([
            'name' => 'Tora Manufacturing Ltd.',
            'code' => 'TML',
            'status' => 'active',
        ]);

        $toraTech = Subsidiary::create([
            'name' => 'Tora Technologies & Solutions',
            'code' => 'TTS',
            'status' => 'active',
        ]);

        $toraLogistics = Subsidiary::create([
            'name' => 'Tora Logistics & Supply Chain',
            'code' => 'TLS',
            'status' => 'active',
        ]);

        // 2. Seed Departments per Company
        Department::create(['subsidiary_id' => $toraManufacturing->id, 'name' => 'Operations & Production', 'status' => 'active']);
        Department::create(['subsidiary_id' => $toraManufacturing->id, 'name' => 'Quality Assurance', 'status' => 'active']);
        Department::create(['subsidiary_id' => $toraManufacturing->id, 'name' => 'Human Resources', 'status' => 'active']);

        Department::create(['subsidiary_id' => $toraTech->id, 'name' => 'Software Engineering', 'status' => 'active']);
        Department::create(['subsidiary_id' => $toraTech->id, 'name' => 'Cloud & Infrastructure', 'status' => 'active']);
        Department::create(['subsidiary_id' => $toraTech->id, 'name' => 'Product Management', 'status' => 'active']);

        Department::create(['subsidiary_id' => $toraLogistics->id, 'name' => 'Fleet Management', 'status' => 'active']);
        Department::create(['subsidiary_id' => $toraLogistics->id, 'name' => 'Warehouse Operations', 'status' => 'active']);

        // 3. Seed Users for each key role
        // System Administrator (Holding level)
        $admin = User::create([
            'name' => 'Tora System Administrator',
            'email' => 'admin@tora.com',
            'password' => Hash::make('password123'),
            'role' => 'Admin',
            'status' => 'active',
            'subsidiary_id' => null,
        ]);

        // HR Manager (Holding level - cross-subsidiary visibility)
        $hrManager = User::create([
            'name' => 'Selamawit Desta',
            'email' => 'hrmanager@tora.com',
            'password' => Hash::make('password123'),
            'role' => 'HR Manager',
            'status' => 'active',
            'subsidiary_id' => null,
        ]);

        // Recruiter (Assigned to Tora Manufacturing)
        $recruiter = User::create([
            'name' => 'Dawit Alemayehu',
            'email' => 'recruiter@tora.com',
            'password' => Hash::make('password123'),
            'role' => 'Recruiter',
            'status' => 'active',
            'subsidiary_id' => $toraManufacturing->id,
        ]);

        // Hiring Manager (Assigned to Tora Technologies)
        $hiringManager = User::create([
            'name' => 'Yonas Tesfaye',
            'email' => 'hiring@tora.com',
            'password' => Hash::make('password123'),
            'role' => 'Hiring Manager',
            'status' => 'active',
            'subsidiary_id' => $toraTech->id,
        ]);

        // Sample Applicant
        $applicant = User::create([
            'name' => 'Bethlehem Kebede',
            'email' => 'applicant@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'Applicant',
            'status' => 'active',
            'subsidiary_id' => null,
        ]);

        // Initial audit records
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'seed_database',
            'entity_type' => 'System',
            'entity_id' => null,
            'after_state' => ['seeded_roles' => 5, 'seeded_companies' => 3],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'System Seeder',
        ]);
    }
}
