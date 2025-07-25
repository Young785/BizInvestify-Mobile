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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('category')->nullable();
            $table->json('tags')->nullable();
            $table->json('images')->nullable();
            $table->enum('status', ['active', 'sold', 'inactive'])->default('active');
            $table->integer('inventory_count')->default(1);
            $table->integer('views_count')->default(0);
            $table->timestamps();
            
            $table->index(['seller_id', 'status']);
            $table->index(['category', 'status']);
            $table->index(['price', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
