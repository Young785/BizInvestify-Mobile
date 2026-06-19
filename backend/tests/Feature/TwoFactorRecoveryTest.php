<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_recovery_code_verifies_session_and_is_consumed(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => 'TESTSECRET',
            'two_factor_confirmed_at' => now(),
            'two_factor_skipped' => false,
            'two_factor_recovery_codes' => json_encode(['ABCD-1234', 'EFGH-5678']),
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $this->actingAs($user, 'sanctum')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->withoutMiddleware(\App\Http\Middleware\TwoFactorMiddleware::class)
            ->postJson('/api/verify-2fa-recovery', ['code' => 'ABCD-1234'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.remaining_recovery_codes', 1);

        $user->refresh();
        $remaining = json_decode($user->two_factor_recovery_codes, true);
        $this->assertSame(['EFGH-5678'], $remaining);
    }
}
