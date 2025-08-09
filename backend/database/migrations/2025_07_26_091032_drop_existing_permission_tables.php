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
        // Drop foreign key constraints first if they exist
        if (Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            });
        }

        // Drop existing permission tables if they exist
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is only for cleanup, no rollback needed
    }
};
