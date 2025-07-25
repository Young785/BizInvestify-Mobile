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
            $table->string('email_2fa_code')->nullable();
            $table->timestamp('email_2fa_expires_at')->nullable();
            $table->boolean('email_2fa_enabled')->default(false);
            $table->boolean('two_factor_skipped')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_2fa_code',
                'email_2fa_expires_at',
                'email_2fa_enabled',
                'two_factor_skipped'
            ]);
        });
    }
};
