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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('payment_id')->nullable()->after('payment_method')->index();
            $table->string('currency', 10)->default('USD')->after('payment_id');
            $table->json('payment_details')->nullable()->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['payment_id']);
            $table->dropColumn(['payment_id', 'currency', 'payment_details']);
        });
    }
};
