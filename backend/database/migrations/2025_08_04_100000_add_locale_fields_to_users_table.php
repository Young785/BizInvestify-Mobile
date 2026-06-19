<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('country_code', 2)->nullable()->after('country');
            $table->string('preferred_currency', 3)->nullable()->after('country_code');
            $table->string('preferred_language', 5)->nullable()->after('preferred_currency');
            $table->string('timezone', 64)->nullable()->after('preferred_language');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'country_code',
                'preferred_currency',
                'preferred_language',
                'timezone',
            ]);
        });
    }
};
