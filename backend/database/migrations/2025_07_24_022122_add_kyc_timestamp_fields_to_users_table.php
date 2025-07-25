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
            $table->timestamp('kyc_verified_at')->nullable()->after('kyc_documents');
            $table->timestamp('kyc_rejected_at')->nullable()->after('kyc_verified_at');
            $table->text('kyc_rejection_reason')->nullable()->after('kyc_rejected_at');
            
            $table->index(['kyc_verified_at']);
            $table->index(['kyc_rejected_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'kyc_verified_at',
                'kyc_rejected_at',
                'kyc_rejection_reason'
            ]);
            
            $table->dropIndex(['kyc_verified_at']);
            $table->dropIndex(['kyc_rejected_at']);
        });
    }
};
