<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_validates_and_creates_user(): void
    {
        $payload = [
            // Use seller to satisfy controller validation and sqlite enum
            'role' => 'seller',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'businessName' => 'John LLC',
            'businessType' => 'Retail',
            // For buyer role, investor fields are not required
            'agreeToTerms' => true,
            'agreeToPrivacy' => true,
            'confirmAge' => true,
            'confirmIdentity' => true,
        ];

        $res = $this->postJson('/api/register', $payload);
        $res->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['user_id', 'email', 'verification_step']]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => 'seller',
        ]);
    }

    public function test_login_requires_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('secret1234'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
        ])->assertStatus(401);

        $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'secret1234',
        ])->assertStatus(200)
          ->assertJsonPath('success', true)
          ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_profile_requires_authentication(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_me_returns_user_when_authenticated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/me')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $user->id);
    }
}


