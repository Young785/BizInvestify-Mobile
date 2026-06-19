<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'shipping_carrier')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('shipping_carrier')->nullable()->after('notes');
                $table->string('tracking_number')->nullable()->after('shipping_carrier');
                $table->text('shipping_address')->nullable()->after('tracking_number');
                $table->timestamp('shipped_at')->nullable()->after('shipping_address');
                $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            });
        }

        if (! Schema::hasColumn('orders', 'seller_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('seller_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('order_status_histories')) {
            Schema::create('order_status_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('from_status')->nullable();
                $table->string('to_status');
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['order_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'seller_id')) {
                $table->dropConstrainedForeignId('seller_id');
            }
            $columns = ['shipping_carrier', 'tracking_number', 'shipping_address', 'shipped_at', 'delivered_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
