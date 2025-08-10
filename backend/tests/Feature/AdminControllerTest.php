<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_require_permissions(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/admin/dashboard/stats')
            ->assertStatus(403);
    }

    public function test_super_admin_can_access_admin_dashboard(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        // Create an admin user with an allowed role for sqlite enum in tests
        $admin = User::factory()->create(['role' => 'admin']);
        // Give broad permission using spatie roles trait
        $admin->assignRole('super_admin');
        $token = $admin->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/admin/dashboard/stats')
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}


