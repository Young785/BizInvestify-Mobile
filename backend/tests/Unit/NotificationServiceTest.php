<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NotificationService::class);
    }

    public function test_format_includes_ui_metadata_and_action_url(): void
    {
        $notification = Notification::factory()->create([
            'type' => 'payment_received',
            'title' => 'Payment received',
            'message' => 'Funds have arrived.',
            'data' => ['amount' => 100],
        ]);

        $formatted = $this->service->format($notification);

        $this->assertSame('success', $formatted['ui_type']);
        $this->assertSame('transaction', $formatted['category']);
        $this->assertSame('/dashboard/transactions', $formatted['action_url']);
    }

    public function test_should_deliver_in_app_respects_preferences(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => [
                'push_notifications' => true,
                'message_notifications' => false,
            ],
        ]);

        $this->assertFalse($this->service->shouldDeliverInApp($user, 'message'));
        $this->assertTrue($this->service->shouldDeliverInApp($user, 'kyc_approved'));
    }

    public function test_create_notification_is_skipped_when_category_disabled(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => [
                'push_notifications' => true,
                'message_notifications' => false,
            ],
        ]);

        $created = Notification::createNotification(
            $user->id,
            'message',
            'New message',
            'You have a new message.'
        );

        $this->assertNull($created);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_log_registration_creates_activity_and_welcome_notification(): void
    {
        $user = User::factory()->create();

        NotificationService::logRegistration($user);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'activity_type' => 'register',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'Welcome to BizInvestify',
        ]);
    }

    public function test_log_failed_login_creates_security_notification_for_existing_user(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        NotificationService::logFailedLogin('user@example.com');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'activity_type' => 'login_failed',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'security_alert',
        ]);
    }

    public function test_log_login_records_activity(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);

        NotificationService::logLogin($user, false);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'activity_type' => 'login',
        ]);
    }

    public function test_notify_with_email_creates_notification_and_sends_mail(): void
    {
        Event::fake([MessageSending::class]);

        $user = User::factory()->create();

        $notification = $this->service->notifyWithEmail(
            $user,
            'refund',
            'Refund Processed',
            'Your refund has been issued.',
            ['action_url' => '/dashboard/transactions']
        );

        $this->assertNotNull($notification);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'refund',
        ]);

        Event::assertDispatched(MessageSending::class);
    }
}
