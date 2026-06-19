<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsServiceRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_retention_rate_uses_login_activity(): void
    {
        $retainedUser = User::factory()->create();
        $churnedUser = User::factory()->create();

        $this->seedLogin($retainedUser, 15);
        $this->seedLogin($churnedUser, 15);
        $this->seedLogin($retainedUser, 5);

        $service = app(AnalyticsService::class);
        $metrics = $service->getPerformanceMetrics(now()->subDays(10));

        $this->assertSame(50.0, $metrics['retention_rate']);
        $this->assertSame(50.0, $metrics['churn_rate']);
        $this->assertNotEmpty($metrics['cohort_analysis']);
    }

    private function seedLogin(User $user, int $daysAgo): void
    {
        $log = ActivityLog::create([
            'user_id' => $user->id,
            'activity_type' => 'login',
            'description' => 'Login activity',
        ]);

        $timestamp = now()->subDays($daysAgo);
        $log->forceFill([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ])->save();
    }
}
