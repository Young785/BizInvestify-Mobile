<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
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
          ->assertJsonStructure([
              'data' => [
                  'token',
                  'user' => [
                      'id',
                      'email',
                      'roles',
                      'user_permissions',
                  ],
              ],
          ]);
    }

    public function test_login_includes_roles_when_assigned(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create([
            'email' => 'buyer@example.com',
            'password' => Hash::make('secret1234'),
            'role' => 'buyer',
        ]);
        $user->assignRole('buyer');

        $this->postJson('/api/login', [
            'email' => 'buyer@example.com',
            'password' => 'secret1234',
        ])->assertStatus(200)
          ->assertJsonPath('data.user.roles.0.name', 'buyer');
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

    public function test_me_includes_roles_and_permissions(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['role' => 'buyer']);
        $user->assignRole('buyer');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/me')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'roles' => [
                            ['name', 'permissions' => [['name']]],
                        ],
                    ],
                ],
            ]);
    }
}


