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
        Schema::create('user_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date'); // Date for daily aggregation
            $table->integer('page_views')->default(0);
            $table->integer('unique_page_views')->default(0);
            $table->integer('sessions')->default(0);
            $table->integer('session_duration')->default(0); // Average session duration in seconds
            $table->integer('bounce_rate')->default(0); // Percentage as integer (0-100)
            $table->integer('searches')->default(0);
            $table->integer('unique_searches')->default(0);
            $table->integer('clicks')->default(0);
            $table->integer('unique_clicks')->default(0);
            $table->integer('purchases')->default(0);
            $table->decimal('purchase_value', 15, 2)->default(0);
            $table->integer('investments')->default(0);
            $table->decimal('investment_value', 15, 2)->default(0);
            $table->integer('reviews')->default(0);
            $table->integer('wishlist_adds')->default(0);
            $table->integer('comparisons')->default(0);
            $table->integer('featured_listing_views')->default(0);
            $table->integer('featured_listing_clicks')->default(0);
            $table->integer('recommendation_views')->default(0);
            $table->integer('recommendation_clicks')->default(0);
            $table->integer('recommendation_purchases')->default(0);
            $table->json('top_pages')->nullable(); // Most visited pages
            $table->json('top_searches')->nullable(); // Most searched terms
            $table->json('top_categories')->nullable(); // Most viewed categories
            $table->json('top_products')->nullable(); // Most viewed products
            $table->json('top_businesses')->nullable(); // Most viewed businesses
            $table->json('conversion_funnel')->nullable(); // Conversion funnel data
            $table->json('device_breakdown')->nullable(); // Device usage breakdown
            $table->json('location_breakdown')->nullable(); // Geographic breakdown
            $table->timestamps();

            // Unique constraint for user and date
            $table->unique(['user_id', 'date']);

            // Indexes for better performance
            $table->index(['user_id', 'date']);
            $table->index('date');
            $table->index(['purchase_value', 'date']);
            $table->index(['investment_value', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_analytics');
    }
};
