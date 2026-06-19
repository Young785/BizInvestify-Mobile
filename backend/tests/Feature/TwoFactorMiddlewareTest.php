<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TwoFactorVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class TwoFactorMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocks_protected_api_when_2fa_enabled_but_token_not_verified(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create([
            'two_factor_secret' => 'secret',
            'two_factor_confirmed_at' => now(),
            'two_factor_skipped' => false,
        ]);
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/profile')
            ->assertStatus(403)
            ->assertJsonPath('data.requires_2fa_verification', true);
    }

    public function test_allows_protected_api_after_token_is_marked_verified(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create([
            'two_factor_secret' => 'secret',
            'two_factor_confirmed_at' => now(),
            'two_factor_skipped' => false,
        ]);
        $accessToken = $user->createToken('t');
        $plainTextToken = $accessToken->plainTextToken;

        app(TwoFactorVerificationService::class)->markTokenVerified($accessToken->accessToken);

        $this->withHeader('Authorization', 'Bearer '.$plainTextToken)
            ->getJson('/api/profile')
            ->assertStatus(200);
    }

    public function test_skips_middleware_for_users_who_skipped_2fa(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create([
            'two_factor_secret' => 'secret',
            'two_factor_confirmed_at' => now(),
            'two_factor_skipped' => true,
        ]);
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/profile')
            ->assertStatus(200);
    }

    public function test_allows_check_2fa_status_endpoint(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create([
            'two_factor_secret' => 'secret',
            'two_factor_confirmed_at' => now(),
            'two_factor_skipped' => false,
        ]);
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/check-2fa-status')
            ->assertStatus(200)
            ->assertJsonPath('data.requires_2fa_verification', true);
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
