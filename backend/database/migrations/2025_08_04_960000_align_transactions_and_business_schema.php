<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite' && app()->runningUnitTests()) {
            $this->rebuildTransactionsTableForSqlite();

            return;
        }

        if (! Schema::hasTable('transactions')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('transactions', 'type')) {
                $table->string('type', 32)->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('transactions', 'currency')) {
                $table->string('currency', 3)->default('USD')->after('amount');
            }
            if (! Schema::hasColumn('transactions', 'commission') && Schema::hasColumn('transactions', 'commission_amount')) {
                $table->decimal('commission', 15, 2)->default(0)->after('amount');
            } elseif (! Schema::hasColumn('transactions', 'commission')) {
                $table->decimal('commission', 15, 2)->default(0)->after('amount');
            }
            if (! Schema::hasColumn('transactions', 'net_amount')) {
                $table->decimal('net_amount', 15, 2)->nullable()->after('commission');
            }
            if (! Schema::hasColumn('transactions', 'reference_id')) {
                $table->string('reference_id')->nullable()->after('status');
            }
            if (! Schema::hasColumn('transactions', 'metadata')) {
                $table->json('metadata')->nullable()->after('reference_id');
            }
            if (! Schema::hasColumn('transactions', 'payment_intent_id') && Schema::hasColumn('transactions', 'stripe_payment_intent_id')) {
                $table->string('payment_intent_id')->nullable()->after('metadata');
            } elseif (! Schema::hasColumn('transactions', 'payment_intent_id')) {
                $table->string('payment_intent_id')->nullable()->after('metadata');
            }
            if (! Schema::hasColumn('transactions', 'payment_reference')) {
                $table->string('payment_reference')->nullable();
            }
            if (! Schema::hasColumn('transactions', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (! Schema::hasColumn('transactions', 'refund_reason')) {
                $table->text('refund_reason')->nullable();
            }
            if (! Schema::hasColumn('transactions', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable();
            }
            if (! Schema::hasColumn('transactions', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
        });

        if (Schema::hasColumn('transactions', 'commission_amount') && Schema::hasColumn('transactions', 'commission')) {
            DB::table('transactions')
                ->whereNull('commission')
                ->orWhere('commission', 0)
                ->update(['commission' => DB::raw('commission_amount')]);
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE transactions MODIFY buyer_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE transactions MODIFY seller_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE transactions MODIFY listing_id BIGINT UNSIGNED NULL');

            if (Schema::hasColumn('transactions', 'listing_type')) {
                DB::statement('ALTER TABLE transactions MODIFY listing_type VARCHAR(32) NULL');
            }

            if (Schema::hasColumn('transactions', 'status')) {
                DB::statement("ALTER TABLE transactions MODIFY status VARCHAR(32) NOT NULL DEFAULT 'pending'");
            }
        }

        if (Schema::hasTable('businesses') && ! Schema::hasColumn('businesses', 'funded_amount')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->decimal('funded_amount', 15, 2)->default(0)->after('funding_goal');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('businesses') && Schema::hasColumn('businesses', 'funded_amount')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->dropColumn('funded_amount');
            });
        }
    }

    private function rebuildTransactionsTableForSqlite(): void
    {
        Schema::dropIfExists('transactions');

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('buyer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('listing_id')->nullable();
            $table->string('listing_type', 32)->nullable();
            $table->string('type', 32)->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('commission', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('status', 32)->default('pending');
            $table->string('reference_id')->nullable();
            $table->string('payment_intent_id')->nullable();
            $table->string('payment_reference')->nullable();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->text('refund_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'type', 'status']);
            $table->index(['buyer_id', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index(['listing_id', 'listing_type']);
            $table->index(['created_at']);
        });

        if (Schema::hasTable('businesses') && ! Schema::hasColumn('businesses', 'funded_amount')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->decimal('funded_amount', 15, 2)->default(0)->after('funding_goal');
            });
        }
    }
};
