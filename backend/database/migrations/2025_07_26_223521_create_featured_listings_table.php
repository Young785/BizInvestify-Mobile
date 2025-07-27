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
        Schema::create('featured_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Seller who paid for promotion
            $table->string('listable_type'); // 'product' or 'business'
            $table->unsignedBigInteger('listable_id'); // ID of the product or business
            $table->string('title'); // Custom title for the featured listing
            $table->text('description')->nullable(); // Custom description
            $table->json('banner_image')->nullable(); // Custom banner image
            $table->string('promotion_type'); // 'featured', 'sponsored', 'trending', 'editor_choice'
            $table->decimal('daily_budget', 10, 2)->default(0); // Daily spending limit
            $table->decimal('total_budget', 10, 2)->default(0); // Total budget allocated
            $table->decimal('spent_amount', 10, 2)->default(0); // Amount spent so far
            $table->integer('impressions')->default(0); // Number of views
            $table->integer('clicks')->default(0); // Number of clicks
            $table->decimal('ctr', 5, 2)->default(0); // Click-through rate
            $table->timestamp('start_date'); // When promotion starts
            $table->timestamp('end_date'); // When promotion ends
            $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');
            $table->json('targeting')->nullable(); // Targeting criteria (location, category, etc.)
            $table->json('performance_metrics')->nullable(); // Additional performance data
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['listable_type', 'listable_id']);
            $table->index(['user_id', 'status']);
            $table->index(['promotion_type', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index(['status', 'start_date']);
            $table->index('impressions');
            $table->index('clicks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featured_listings');
    }
};
