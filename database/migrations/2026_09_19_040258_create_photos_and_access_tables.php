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
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_private')->default(false);
            $table->boolean('is_blurred')->default(true); // default blurred per FR-2.2 & 6.3
            $table->string('moderation_status')->default('approved'); // pending, approved, rejected
            $table->timestamps();

            $table->index(['profile_id', 'is_primary']);
        });

        Schema::create('photo_access_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('photo_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('granted_to_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('active'); // active, revoked
            $table->timestamps();

            $table->unique(['profile_id', 'granted_to_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_access_grants');
        Schema::dropIfExists('photos');
    }
};
