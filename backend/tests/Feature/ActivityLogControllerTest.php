<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogControllerTest extends TestCase
{
    use RefreshDatabase;

    private function authenticatedUser(): User
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['role' => 'buyer']);
        $user->assignRole('buyer');

        return $user;
    }

    public function test_activity_logs_require_authentication(): void
    {
        $this->getJson('/api/activity-logs')->assertStatus(401);
    }

    public function test_user_can_list_activity_logs(): void
    {
        $user = $this->authenticatedUser();
        ActivityLog::log($user->id, 'login', 'User logged in', [], 'low');

        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/activity-logs')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.data.0.activity_type', 'login');
    }

    public function test_activity_type_filter_is_supported(): void
    {
        $user = $this->authenticatedUser();
        ActivityLog::log($user->id, 'login', 'User logged in', [], 'low');
        ActivityLog::log($user->id, 'profile_update', 'Profile updated', [], 'low');

        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/activity-logs?activity_type=login')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.activity_type', 'login');
    }

    public function test_recent_activities_endpoint(): void
    {
        $user = $this->authenticatedUser();
        ActivityLog::log($user->id, 'login', 'User logged in', [], 'low');

        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/activity-logs/recent')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }
}
