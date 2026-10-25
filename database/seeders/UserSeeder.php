<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = env('SEED_DEFAULT_PASSWORD', 'ConnectEd2026!');

        if (app()->environment('production') && ! env('SEED_DEFAULT_PASSWORD')) {
            throw new \RuntimeException('SEED_DEFAULT_PASSWORD must be explicitly provided in production environments.');
        }

        $passwordHash = Hash::make($defaultPassword);

        $users = [
            [
                'name' => 'OJT Coordinator (OC)',
                'email' => 'oc@bsau.edu.ph',
                'role' => UserRole::OC,
                'status' => UserStatus::ACTIVE,
                'approved_at' => now(),
            ],
            [
                'name' => 'Co-OJT Coordinator (COC)',
                'email' => 'coc@bsau.edu.ph',
                'role' => UserRole::COC,
                'status' => UserStatus::ACTIVE,
                'approved_at' => now(),
            ],
            [
                'name' => 'University Attorney (UA)',
                'email' => 'ua@bsau.edu.ph',
                'role' => UserRole::UA,
                'status' => UserStatus::ACTIVE,
                'approved_at' => now(),
            ],
            [
                'name' => 'TechSolutions Partner Rep (PC)',
                'email' => 'pc@techsolutions.example.test',
                'role' => UserRole::PC,
                'status' => UserStatus::ACTIVE,
                'approved_at' => now(),
            ],
            [
                'name' => 'Juan Dela Cruz (Student)',
                'email' => 'student@bsau.edu.ph',
                'role' => UserRole::STUDENT,
                'status' => UserStatus::ACTIVE,
                'approved_at' => now(),
            ],
            [
                'name' => 'Maria Clara Santos (Pending Student)',
                'email' => 'pending.student@bsau.edu.ph',
                'role' => UserRole::STUDENT,
                'status' => UserStatus::PENDING,
                'approved_at' => null,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => $passwordHash,
                    'email_verified_at' => now(),
                    'must_change_password' => false,
                ])
            );
        }
    }
}
