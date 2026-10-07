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
        if (! Schema::hasTable('marriages')) {
            Schema::create('marriages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('groom_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('bride_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('initiated_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('status')->default('pending_confirmation'); // pending_confirmation, confirmed, declined
                $table->date('marriage_date')->nullable();
                $table->text('confirmation_notes')->nullable();
                $table->string('story_title')->nullable();
                $table->text('story_body')->nullable();
                $table->boolean('story_is_public')->default(false);
                $table->timestamp('story_approved_at')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamps();

                $table->index(['groom_id', 'bride_id']);
                $table->index(['status', 'confirmed_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marriages');
    }
};
