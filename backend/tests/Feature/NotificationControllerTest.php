<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
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

    public function test_notifications_require_authentication(): void
    {
        $this->getJson('/api/notifications')->assertStatus(401);
    }

    public function test_notifications_require_permission(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/notifications')
            ->assertStatus(403);
    }

    public function test_user_can_list_formatted_notifications(): void
    {
        $user = $this->authenticatedUser();
        Notification::factory()->for($user)->create([
            'type' => 'kyc_approved',
            'title' => 'KYC Approved',
            'message' => 'Your identity has been verified.',
        ]);

        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/notifications')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.data.0.ui_type', 'success')
            ->assertJsonPath('data.data.0.category', 'kyc')
            ->assertJsonPath('data.data.0.action_url', '/dashboard/profile/kyc');
    }

    public function test_unread_only_filter_uses_is_read_column(): void
    {
        $user = $this->authenticatedUser();
        Notification::factory()->for($user)->unread()->create(['title' => 'Unread item']);
        Notification::factory()->for($user)->read()->create(['title' => 'Read item']);

        $token = $user->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/notifications?unread_only=1')
            ->assertStatus(200);

        $titles = collect($response->json('data.data'))->pluck('title')->all();
        $this->assertContains('Unread item', $titles);
        $this->assertNotContains('Read item', $titles);
    }

    public function test_search_filters_notifications(): void
    {
        $user = $this->authenticatedUser();
        Notification::factory()->for($user)->create([
            'title' => 'Payment received',
            'message' => 'You received a payout.',
        ]);
        Notification::factory()->for($user)->create([
            'title' => 'New message',
            'message' => 'Someone sent you a chat message.',
        ]);

        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/notifications?search=payment')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.title', 'Payment received');
    }

    public function test_mark_notification_as_read(): void
    {
        $user = $this->authenticatedUser();
        $notification = Notification::factory()->for($user)->unread()->create();
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/notifications/{$notification->id}/read")
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_read', true);

        $this->assertTrue($notification->fresh()->is_read);
    }

    public function test_mark_all_notifications_as_read(): void
    {
        $user = $this->authenticatedUser();
        Notification::factory()->for($user)->count(3)->unread()->create();
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/notifications/mark-all-read')
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertSame(0, Notification::where('user_id', $user->id)->unread()->count());
    }

    public function test_delete_notification(): void
    {
        $user = $this->authenticatedUser();
        $notification = Notification::factory()->for($user)->create();
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/notifications/{$notification->id}")
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_unread_count_endpoint(): void
    {
        $user = $this->authenticatedUser();
        Notification::factory()->for($user)->count(2)->unread()->create();
        Notification::factory()->for($user)->read()->create();
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/notifications/unread-count')
            ->assertStatus(200)
            ->assertJsonPath('data.unread_count', 2);
    }
}
