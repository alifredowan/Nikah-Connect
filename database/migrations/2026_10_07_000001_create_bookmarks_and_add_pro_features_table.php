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
        if (! Schema::hasTable('bookmarks')) {
            Schema::create('bookmarks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('bookmarked_user_id')->constrained('users')->cascadeOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'bookmarked_user_id']);
                $table->index(['user_id', 'created_at']);
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'is_incognito')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_incognito')->default(false)->after('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmarks');

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_incognito')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_incognito');
            });
        }
    }
};
