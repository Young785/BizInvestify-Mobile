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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('interests')->nullable(); // User interests and categories
            $table->json('preferred_categories')->nullable(); // Preferred product/business categories
            $table->json('preferred_locations')->nullable(); // Preferred locations
            $table->json('price_range')->nullable(); // Preferred price range
            $table->json('investment_range')->nullable(); // Preferred investment range
            $table->json('viewed_items')->nullable(); // Recently viewed items
            $table->json('search_history')->nullable(); // Search terms and frequency
            $table->json('click_behavior')->nullable(); // Click patterns and preferences
            $table->json('purchase_history')->nullable(); // Purchase/investment history
            $table->json('rating_history')->nullable(); // Items rated by user
            $table->json('wishlist_items')->nullable(); // Wishlist items
            $table->json('comparison_history')->nullable(); // Items compared
            $table->json('session_data')->nullable(); // Session-based preferences
            $table->timestamp('last_activity')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'last_activity']);
            $table->index('last_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
