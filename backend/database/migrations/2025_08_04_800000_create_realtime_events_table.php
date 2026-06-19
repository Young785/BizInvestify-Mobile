<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realtime_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 50);
            $table->string('event_type', 80);
            $table->json('payload');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'id']);
            $table->index(['user_id', 'channel', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realtime_events');
    }
};
