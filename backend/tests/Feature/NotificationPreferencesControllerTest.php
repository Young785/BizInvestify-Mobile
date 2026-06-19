<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPreferencesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_settings_require_authentication(): void
    {
        $this->getJson('/api/user/notification-settings')->assertStatus(401);
    }

    public function test_user_can_fetch_default_notification_settings(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user/notification-settings')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.push_notifications', true)
            ->assertJsonPath('data.message_notifications', true)
            ->assertJsonPath('data.frequency', 'immediate');
    }

    public function test_user_can_update_notification_settings(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/user/notification-settings', [
                'push_notifications' => false,
                'message_notifications' => false,
                'frequency' => 'daily',
            ])
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.push_notifications', false)
            ->assertJsonPath('data.message_notifications', false)
            ->assertJsonPath('data.frequency', 'daily');

        $user->refresh();
        $this->assertFalse($user->notification_preferences['push_notifications']);
        $this->assertSame('daily', $user->notification_preferences['frequency']);
    }
}
