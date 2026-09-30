<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First ensure existing emails are cleaned and lowercase
        DB::table('users')->update([
            'email' => DB::raw('LOWER(TRIM(email))'),
        ]);

        $driver = DB::getDriverName();
        if ($driver === 'pgsql' || $driver === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS users_lower_email_unique ON users (LOWER(email))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql' || $driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS users_lower_email_unique');
        }
    }
};
