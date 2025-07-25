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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('industry')->nullable();
            $table->decimal('valuation', 15, 2)->nullable();
            $table->decimal('funding_goal', 15, 2)->nullable();
            $table->decimal('equity_offered', 5, 2)->nullable();
            $table->string('pitch_deck_url')->nullable();
            $table->text('business_plan')->nullable();
            $table->decimal('revenue', 15, 2)->nullable();
            $table->decimal('profit_margin', 5, 2)->nullable();
            $table->integer('employees_count')->nullable();
            $table->integer('founded_year')->nullable();
            $table->string('location')->nullable();
            $table->json('images')->nullable();
            $table->enum('status', ['active', 'funded', 'sold', 'inactive'])->default('active');
            $table->integer('views_count')->default(0);
            $table->timestamps();
            
            $table->index(['seller_id', 'status']);
            $table->index(['industry', 'status']);
            $table->index(['funding_goal', 'status']);
            $table->index(['valuation', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
