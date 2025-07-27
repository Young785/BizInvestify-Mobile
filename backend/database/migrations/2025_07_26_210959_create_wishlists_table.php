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
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('wishlistable'); // For products and businesses
            $table->string('notes')->nullable(); // User notes about the item
            $table->boolean('is_public')->default(false); // Whether the wishlist is public
            $table->timestamps();
            
            // Prevent duplicate items in wishlist
            $table->unique(['user_id', 'wishlistable_type', 'wishlistable_id']);
            
            // Indexes for better performance
            $table->index(['user_id', 'wishlistable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
