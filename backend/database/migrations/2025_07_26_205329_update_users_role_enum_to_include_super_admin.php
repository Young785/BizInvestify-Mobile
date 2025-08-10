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
        // Only run on MySQL/MariaDB. SQLite (used in tests) doesn't support MODIFY/ENUM.
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('seller', 'buyer', 'admin', 'super_admin') DEFAULT 'buyer'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'])) {
            // Revert back to original enum
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('seller', 'buyer', 'admin') DEFAULT 'buyer'");
        }
    }
};
