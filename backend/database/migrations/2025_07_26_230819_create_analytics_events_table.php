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
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Null for anonymous events
            $table->string('session_id')->nullable(); // For anonymous tracking
            $table->string('event_type'); // 'page_view', 'click', 'search', 'purchase', 'investment', 'signup', 'login'
            $table->string('event_category'); // 'user_engagement', 'ecommerce', 'authentication', 'search'
            $table->string('event_action'); // Specific action within category
            $table->string('event_label')->nullable(); // Additional context
            $table->json('event_data')->nullable(); // Additional event data
            $table->string('page_url')->nullable(); // Page where event occurred
            $table->string('referrer')->nullable(); // Where user came from
            $table->string('user_agent')->nullable(); // Browser/device info
            $table->string('ip_address')->nullable(); // User IP (hashed for privacy)
            $table->string('country')->nullable(); // User location
            $table->string('city')->nullable();
            $table->string('device_type')->nullable(); // 'desktop', 'mobile', 'tablet'
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->decimal('value', 10, 2)->nullable(); // Monetary value for ecommerce events
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'event_type']);
            $table->index(['event_category', 'event_action']);
            $table->index(['created_at']);
            $table->index(['session_id']);
            $table->index(['country', 'city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
