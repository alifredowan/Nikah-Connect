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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 8, 2)->default(0.00);
            $table->decimal('annual_price', 8, 2)->default(0.00);
            $table->integer('daily_profile_views')->default(10);
            $table->integer('daily_interests')->default(5);
            $table->boolean('advanced_filters')->default(false);
            $table->boolean('profile_boost')->default(false);
            $table->boolean('see_who_viewed')->default(false);
            $table->boolean('dedicated_advisor')->default(false);
            $table->string('badge_text')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
