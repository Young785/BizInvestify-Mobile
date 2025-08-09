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
            // Email verification
            $table->string('email_verification_token')->nullable()->after('email_verified_at');
            $table->timestamp('email_verification_expires_at')->nullable()->after('email_verification_token');
            
            // Phone verification
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->string('phone_verification_token')->nullable()->after('phone_verified_at');
            $table->timestamp('phone_verification_expires_at')->nullable()->after('phone_verification_token');
            
            // Two-factor authentication
            $table->string('two_factor_secret')->nullable()->after('password');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            
            // Password reset
            $table->string('password_reset_token')->nullable()->after('remember_token');
            $table->timestamp('password_reset_expires_at')->nullable()->after('password_reset_token');
            
            // Session management
            $table->boolean('remember_device')->default(false)->after('password_reset_expires_at');
            $table->json('trusted_devices')->nullable()->after('remember_device');
            
            // Additional verification fields
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('business_name')->nullable()->after('role');
            $table->string('business_type')->nullable()->after('business_name');
            $table->string('investment_amount')->nullable()->after('business_type');
            $table->string('investment_focus')->nullable()->after('investment_amount');
            
            // Add indexes for performance
            $table->index(['email_verification_token']);
            $table->index(['phone_verification_token']);
            $table->index(['password_reset_token']);
            $table->index(['email_verified_at']);
            $table->index(['phone_verified_at']);
            $table->index(['two_factor_confirmed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_verification_token',
                'email_verification_expires_at',
                'phone_verified_at',
                'phone_verification_token', 
                'phone_verification_expires_at',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'password_reset_token',
                'password_reset_expires_at',
                'remember_device',
                'trusted_devices',
                'first_name',
                'last_name',
                'business_name',
                'business_type',
                'investment_amount',
                'investment_focus'
            ]);
            
            $table->dropIndex(['email_verification_token']);
            $table->dropIndex(['phone_verification_token']);
            $table->dropIndex(['password_reset_token']);
            $table->dropIndex(['email_verified_at']);
            $table->dropIndex(['phone_verified_at']);
            $table->dropIndex(['two_factor_confirmed_at']);
        });
    }
}; 