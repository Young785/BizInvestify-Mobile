<?php

use App\Models\Business;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        Business::query()->orderBy('id')->each(function (Business $business) {
            $business->slug = Business::generateUniqueSlug($business->name, $business->id);
            $business->saveQuietly();
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
