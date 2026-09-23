<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'admin@nikahconnect.com');
        $password = env('SUPER_ADMIN_PASSWORD', 'Password123!');
        $name = env('SUPER_ADMIN_NAME', 'Super Administrator');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'permissions' => [
                    'manage_verifications',
                    'manage_reports',
                    'view_audit_logs',
                    'manage_settings',
                ],
                'gender' => 'male',
                'dob' => '1990-01-01',
                'marital_status' => 'never_married',
                'is_verified' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
