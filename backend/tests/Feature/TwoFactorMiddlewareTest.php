<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class TwoFactorMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocks_protected_api_when_2fa_enabled_but_session_not_verified(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create([
            'two_factor_secret' => 'secret',
            'two_factor_confirmed_at' => now(),
        ]);
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/profile')
            ->assertStatus(403)
            ->assertJsonPath('data.requires_2fa_verification', true);
    }

    public function test_allows_skip_endpoints(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create([
            'two_factor_secret' => 'secret',
            'two_factor_confirmed_at' => now(),
        ]);
        $token = $user->createToken('t')->plainTextToken;

        // Use withSession to provide a session store for the request
        // actingAs ensures session is available
        // Also disable rate limit and 2FA middleware here; endpoint should still be reachable
        $this->withoutMiddleware([\App\Http\Middleware\RateLimitMiddleware::class, \App\Http\Middleware\TwoFactorMiddleware::class]);
        $this->actingAs($user, 'sanctum')
            ->withSession(['_token' => 'test'])
            ->getJson('/api/check-2fa-status')
            ->assertStatus(200);
    }
}


