<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the role enum to include all roles
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('seller', 'buyer', 'admin', 'super_admin', 'investor', 'moderator', 'support') DEFAULT 'buyer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('seller', 'buyer', 'admin', 'super_admin') DEFAULT 'buyer'");
    }
};
