<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\RealtimeEvent;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealtimeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_poll_requires_authentication(): void
    {
        $this->getJson('/api/realtime/poll')->assertStatus(401);
    }

    public function test_user_can_poll_realtime_events(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['role' => 'buyer']);
        $user->assignRole('buyer');

        RealtimeEvent::create([
            'user_id' => $user->id,
            'channel' => 'notifications',
            'event_type' => 'notification.new',
            'payload' => ['id' => '1', 'title' => 'Hello'],
            'created_at' => now(),
        ]);

        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/realtime/poll?since_id=0&channels=notifications')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.events')
            ->assertJsonPath('data.events.0.channel', 'notifications');
    }

    public function test_notification_observer_publishes_realtime_event(): void
    {
        $user = User::factory()->create();

        Notification::factory()->for($user)->create([
            'title' => 'Observer test',
            'message' => 'Realtime payload',
        ]);

        $this->assertDatabaseHas('realtime_events', [
            'user_id' => $user->id,
            'channel' => 'notifications',
            'event_type' => 'notification.new',
        ]);
    }
}
