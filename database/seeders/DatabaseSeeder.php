<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with production-ready essentials.
     */
    public function run(): void
    {
        // 1. Core Platform Governance & Verification Settings
        $this->call(PlatformSettingSeeder::class);

        // 2. Auto-create Master Super Administrator
        $this->call(SuperAdminSeeder::class);
    }
}
