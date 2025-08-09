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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->string('product_name');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_intent_id')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['created_at']);
            $table->index(['order_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
}; 