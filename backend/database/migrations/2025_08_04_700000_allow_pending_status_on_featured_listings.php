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

        if ($driver === 'mysql' && Schema::hasTable('featured_listings')) {
            DB::statement("ALTER TABLE featured_listings MODIFY COLUMN status ENUM('pending','active','paused','completed','cancelled') DEFAULT 'pending'");

            return;
        }

        if ($driver !== 'sqlite' || ! Schema::hasTable('featured_listings')) {
            return;
        }

        if (Schema::hasTable('featured_listings_old')) {
            if (Schema::hasTable('featured_listings') && DB::table('featured_listings')->count() === 0) {
                DB::statement('
                    INSERT INTO featured_listings (
                        id, user_id, transaction_id, listable_type, listable_id, title, description,
                        banner_image, promotion_type, daily_budget, total_budget, spent_amount,
                        impressions, clicks, ctr, start_date, end_date, status, targeting,
                        performance_metrics, approved_at, approved_by, rejection_reason,
                        created_at, updated_at, deleted_at
                    )
                    SELECT
                        id, user_id, transaction_id, listable_type, listable_id, title, description,
                        banner_image, promotion_type, daily_budget, total_budget, spent_amount,
                        impressions, clicks, ctr, start_date, end_date, status, targeting,
                        performance_metrics, approved_at, approved_by, rejection_reason,
                        created_at, updated_at, deleted_at
                    FROM featured_listings_old
                ');
            }

            Schema::drop('featured_listings_old');
        }

        if (! $this->statusAllowsPending()) {
            DB::statement('PRAGMA foreign_keys=OFF');
            Schema::rename('featured_listings', 'featured_listings_old');

            Schema::create('featured_listings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('transaction_id')->nullable();
                $table->string('listable_type');
                $table->unsignedBigInteger('listable_id');
                $table->string('title');
                $table->text('description')->nullable();
                $table->json('banner_image')->nullable();
                $table->string('promotion_type');
                $table->decimal('daily_budget', 10, 2)->default(0);
                $table->decimal('total_budget', 10, 2)->default(0);
                $table->decimal('spent_amount', 10, 2)->default(0);
                $table->integer('impressions')->default(0);
                $table->integer('clicks')->default(0);
                $table->decimal('ctr', 5, 2)->default(0);
                $table->timestamp('start_date');
                $table->timestamp('end_date');
                $table->string('status')->default('pending');
                $table->json('targeting')->nullable();
                $table->json('performance_metrics')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            DB::statement('
                INSERT INTO featured_listings (
                    id, user_id, transaction_id, listable_type, listable_id, title, description,
                    banner_image, promotion_type, daily_budget, total_budget, spent_amount,
                    impressions, clicks, ctr, start_date, end_date, status, targeting,
                    performance_metrics, approved_at, approved_by, rejection_reason,
                    created_at, updated_at, deleted_at
                )
                SELECT
                    id, user_id, transaction_id, listable_type, listable_id, title, description,
                    banner_image, promotion_type, daily_budget, total_budget, spent_amount,
                    impressions, clicks, ctr, start_date, end_date, status, targeting,
                    performance_metrics, approved_at, approved_by, rejection_reason,
                    created_at, updated_at, deleted_at
                FROM featured_listings_old
            ');

            Schema::drop('featured_listings_old');
            DB::statement('PRAGMA foreign_keys=ON');
        }

        DB::statement('CREATE INDEX IF NOT EXISTS featured_listings_listable_type_listable_id_index ON featured_listings (listable_type, listable_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS featured_listings_user_id_status_index ON featured_listings (user_id, status)');
        DB::statement('CREATE INDEX IF NOT EXISTS featured_listings_promotion_type_status_index ON featured_listings (promotion_type, status)');
        DB::statement('CREATE INDEX IF NOT EXISTS featured_listings_start_date_end_date_index ON featured_listings (start_date, end_date)');
        DB::statement('CREATE INDEX IF NOT EXISTS featured_listings_status_start_date_index ON featured_listings (status, start_date)');
        DB::statement('CREATE INDEX IF NOT EXISTS featured_listings_impressions_index ON featured_listings (impressions)');
        DB::statement('CREATE INDEX IF NOT EXISTS featured_listings_clicks_index ON featured_listings (clicks)');
    }

    private function statusAllowsPending(): bool
    {
        try {
            DB::table('featured_listings')->insert([
                'user_id' => DB::table('users')->value('id') ?? 1,
                'listable_type' => 'product',
                'listable_id' => 1,
                'title' => '__pending_probe__',
                'promotion_type' => 'featured',
                'daily_budget' => 1,
                'total_budget' => 1,
                'start_date' => now(),
                'end_date' => now()->addDay(),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('featured_listings')->where('title', '__pending_probe__')->delete();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function down(): void
    {
        //
    }
};
