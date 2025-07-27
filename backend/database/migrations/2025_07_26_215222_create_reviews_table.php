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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reviewable_type');
            $table->unsignedBigInteger('reviewable_id');
            $table->integer('rating')->comment('Rating from 1 to 5');
            $table->string('title')->nullable(); // Review title
            $table->text('content'); // Review content
            $table->json('images')->nullable(); // Review images
            $table->boolean('is_verified_purchase')->default(false); // Whether reviewer actually purchased/invested
            $table->boolean('is_helpful')->default(false); // Marked as helpful by others
            $table->integer('helpful_count')->default(0); // Number of helpful votes
            $table->boolean('is_approved')->default(true); // Admin approval status
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Prevent duplicate reviews from same user
            $table->unique(['user_id', 'reviewable_type', 'reviewable_id']);
            
            // Indexes for better performance
            $table->index(['reviewable_type', 'reviewable_id']);
            $table->index(['user_id', 'rating']);
            $table->index(['is_approved', 'created_at']);
            $table->index(['helpful_count', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
