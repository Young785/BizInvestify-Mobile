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
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('kyc_rejection_reason');
            $table->string('stripe_account_id')->nullable()->after('stripe_customer_id');
            $table->index('stripe_customer_id');
            $table->index('stripe_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['stripe_customer_id']);
            $table->dropIndex(['stripe_account_id']);
            $table->dropColumn(['stripe_customer_id', 'stripe_account_id']);
        });
    }
};
