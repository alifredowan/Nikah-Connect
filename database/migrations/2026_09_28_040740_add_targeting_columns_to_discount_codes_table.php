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
        Schema::table('discount_codes', function (Blueprint $table) {
            $table->string('plan_slug')->nullable()->after('discount_percentage');
            $table->text('allowed_emails')->nullable()->after('plan_slug');
            $table->string('description')->nullable()->after('allowed_emails');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discount_codes', function (Blueprint $table) {
            $table->dropColumn(['plan_slug', 'allowed_emails', 'description']);
        });
    }
};
