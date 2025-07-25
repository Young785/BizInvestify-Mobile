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
            $table->enum('role', ['seller', 'buyer', 'admin'])->default('buyer')->after('email');
            $table->string('phone')->nullable()->after('name');
            $table->string('avatar_url')->nullable()->after('phone');
            $table->enum('kyc_status', ['pending', 'verified', 'rejected'])->default('pending')->after('avatar_url');
            $table->json('kyc_documents')->nullable()->after('kyc_status');
            $table->decimal('trust_score', 3, 2)->default(0.00)->after('kyc_documents');
            $table->boolean('is_verified')->default(false)->after('trust_score');
            
            $table->index(['role', 'kyc_status']);
            $table->index(['trust_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'phone',
                'avatar_url',
                'kyc_status',
                'kyc_documents',
                'trust_score',
                'is_verified'
            ]);
            
            $table->dropIndex(['role', 'kyc_status']);
            $table->dropIndex(['trust_score']);
        });
    }
};
