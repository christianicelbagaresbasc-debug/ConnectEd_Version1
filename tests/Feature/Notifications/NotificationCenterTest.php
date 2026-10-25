<?php

namespace Tests\Feature\Notifications;

use App\Enums\UserRole;
use App\Models\User;
use App\Notifications\ConnectEdNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => 'active',
            'must_change_password' => false,
            'approved_at' => now(),
        ]);
    }

    public function test_rule_br02_notifications_use_database_channel_only(): void
    {
        $notification = new ConnectEdNotification(
            title: 'Test Title',
            body: 'Test Body',
            actionUrl: '/dashboard',
            type: 'test_notification'
        );

        $channels = $notification->via($this->user);

        // Rule BR-02: In-system notifications only. No email or chat.
        $this->assertEquals(['database'], $channels);
        $this->assertNotContains('mail', $channels);
        $this->assertNotContains('broadcast', $channels);
    }

    public function test_user_can_view_notifications_index(): void
    {
        $this->user->notify(new ConnectEdNotification(
            title: 'Application Accepted',
            body: 'Your application has been accepted.',
            actionUrl: '/student/applications/1',
            type: 'application_decision'
        ));

        $response = $this->actingAs($this->user)
            ->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('Application Accepted');
        $response->assertSee('Your application has been accepted.');
    }

    public function test_unread_count_json_endpoint(): void
    {
        $this->user->notify(new ConnectEdNotification(
            title: 'Notif 1',
            body: 'Body 1',
            actionUrl: null,
            type: 'info'
        ));

        $this->user->notify(new ConnectEdNotification(
            title: 'Notif 2',
            body: 'Body 2',
            actionUrl: null,
            type: 'info'
        ));

        $response = $this->actingAs($this->user)
            ->getJson(route('notifications.unread-count'));

        $response->assertOk();
        $response->assertJson([
            'unread_count' => 2,
        ]);
    }

    public function test_user_can_mark_notification_as_read_and_redirect_to_action_url(): void
    {
        $this->user->notify(new ConnectEdNotification(
            title: 'Actionable Notice',
            body: 'Please check your placement',
            actionUrl: '/student/placements/5',
            type: 'placement_update'
        ));

        $notification = $this->user->unreadNotifications()->first();
        $this->assertNotNull($notification);

        $response = $this->actingAs($this->user)
            ->post(route('notifications.read', $notification->id));

        $response->assertRedirect('/student/placements/5');
        $this->assertEquals(0, $this->user->fresh()->unreadNotifications()->count());
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $this->user->notify(new ConnectEdNotification(
            title: 'Notif 1',
            body: 'Body 1',
            actionUrl: null,
            type: 'info'
        ));

        $this->user->notify(new ConnectEdNotification(
            title: 'Notif 2',
            body: 'Body 2',
            actionUrl: null,
            type: 'info'
        ));

        $this->assertEquals(2, $this->user->unreadNotifications()->count());

        $response = $this->actingAs($this->user)
            ->post(route('notifications.read-all'));

        $response->assertRedirect();
        $this->assertEquals(0, $this->user->fresh()->unreadNotifications()->count());
    }
}
