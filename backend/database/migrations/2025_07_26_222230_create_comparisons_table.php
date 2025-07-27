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
        Schema::create('comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Null for guest comparisons
            $table->string('session_id')->nullable(); // For guest comparisons
            $table->string('type'); // 'product' or 'business'
            $table->json('items'); // Array of item IDs being compared
            $table->json('attributes')->nullable(); // Custom attributes to compare
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['user_id', 'type']);
            $table->index(['session_id', 'type']);
            $table->index('last_accessed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comparisons');
    }
};
