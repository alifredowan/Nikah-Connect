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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->string('education_level')->nullable();
            $table->string('profession')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('annual_income_range')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('mother_tongue')->nullable();
            $table->string('citizenship')->nullable();
            $table->text('bio')->nullable();

            // Religious and Islamic practice fields (FR-2.1, FR-6.4)
            $table->string('sect_madhhab')->nullable();
            $table->string('prayer_frequency')->nullable();
            $table->string('hijab_niqab_practice')->nullable();
            $table->string('beard_practice')->nullable();
            $table->string('quran_knowledge')->nullable();
            $table->string('mosque_attendance')->nullable();
            $table->string('halal_dietary_adherence')->nullable();
            $table->string('polygamy_opinion')->nullable();
            $table->string('desired_family_structure')->nullable();

            // Family background fields
            $table->string('parents_status')->nullable();
            $table->string('parents_occupation')->nullable();
            $table->unsignedSmallInteger('siblings_count')->default(0);
            $table->string('family_religiosity')->nullable();

            // Preferences & Privacy settings
            $table->json('partner_preferences')->nullable();
            $table->json('field_visibility')->nullable();
            $table->boolean('wali_required')->default(false);
            $table->unsignedTinyInteger('completeness_percentage')->default(0);
            $table->timestamps();

            $table->index(['city', 'country']);
            $table->index('sect_madhhab');
            $table->index('prayer_frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
