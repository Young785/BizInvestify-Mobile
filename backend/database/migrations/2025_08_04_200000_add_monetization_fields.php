<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'subscription_plan')) {
                $table->string('subscription_plan')->default('starter')->after('role');
            }
            if (!Schema::hasColumn('users', 'subscription_expires_at')) {
                $table->timestamp('subscription_expires_at')->nullable()->after('subscription_plan');
            }
        });

        Schema::table('featured_listings', function (Blueprint $table) {
            if (!Schema::hasColumn('featured_listings', 'transaction_id')) {
                $table->unsignedBigInteger('transaction_id')->nullable()->after('user_id');
            }
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql' && Schema::hasTable('featured_listings')) {
            DB::statement("ALTER TABLE featured_listings MODIFY COLUMN status ENUM('pending','active','paused','completed','cancelled') DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'subscription_plan')) {
                $table->dropColumn('subscription_plan');
            }
            if (Schema::hasColumn('users', 'subscription_expires_at')) {
                $table->dropColumn('subscription_expires_at');
            }
        });

        Schema::table('featured_listings', function (Blueprint $table) {
            if (Schema::hasColumn('featured_listings', 'transaction_id')) {
                $table->dropColumn('transaction_id');
            }
        });
    }
};
