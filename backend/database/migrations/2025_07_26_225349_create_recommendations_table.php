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
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('recommendable_type'); // 'product' or 'business'
            $table->unsignedBigInteger('recommendable_id'); // ID of the recommended item
            $table->decimal('score', 5, 4)->default(0); // Recommendation score (0-1)
            $table->string('algorithm_type'); // 'collaborative', 'content_based', 'hybrid', 'trending'
            $table->json('factors')->nullable(); // Factors that influenced the recommendation
            $table->boolean('is_viewed')->default(false); // Whether user viewed this recommendation
            $table->boolean('is_clicked')->default(false); // Whether user clicked this recommendation
            $table->boolean('is_purchased')->default(false); // Whether user purchased/invested
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'is_active']);
            $table->index(['recommendable_type', 'recommendable_id']);
            $table->index(['algorithm_type', 'score']);
            $table->index(['user_id', 'is_viewed']);
            $table->index(['user_id', 'is_clicked']);
            $table->index(['user_id', 'is_purchased']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
