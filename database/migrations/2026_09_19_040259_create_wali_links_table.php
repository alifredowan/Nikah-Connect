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
        Schema::create('wali_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seeker_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('wali_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('wali_name')->nullable();
            $table->string('wali_phone')->nullable();
            $table->string('wali_email')->nullable();
            $table->string('relationship_type'); // father, brother, uncle, grandfather, other_mahram
            $table->string('permission_level')->default('approve_required'); // view_only, approve_required, full_proxy
            $table->string('status')->default('pending'); // pending, active, rejected, revoked
            $table->string('invite_token')->nullable()->index();
            $table->timestamps();

            $table->index(['seeker_user_id', 'status']);
            $table->index(['wali_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wali_links');
    }
};
