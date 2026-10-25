<?php

namespace Tests\Feature\Tasks;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\StudentProfile;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $oc;

    protected User $coc;

    protected User $student;

    protected StudentProfile $studentProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->oc = User::factory()->create([
            'role' => UserRole::OC,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->coc = User::factory()->create([
            'role' => UserRole::COC,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->studentProfile = StudentProfile::create([
            'user_id' => $this->student->id,
            'student_number' => 'BSAU-2026-TASK',
            'program' => 'BSIT',
            'year_level' => '4',
            'has_active_placement' => false,
        ]);
    }

    public function test_oc_can_create_and_delegate_task_to_coc(): void
    {
        $response = $this->actingAs($this->oc)->post(route('tasks.store'), [
            'title' => 'Audit MOA records for Group A',
            'description' => 'Check notarized copy uploaded in Document Library.',
            'assigned_to' => $this->coc->id,
            'due_date' => now()->addDays(3)->toDateString(),
            'priority' => TaskPriority::HIGH->value,
            'related_student_profile_id' => $this->studentProfile->id,
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('tasks', [
            'title' => 'Audit MOA records for Group A',
            'assigned_to' => $this->coc->id,
            'created_by' => $this->oc->id,
            'priority' => TaskPriority::HIGH->value,
            'status' => TaskStatus::TODO->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'task.created',
            'actor_id' => $this->oc->id,
        ]);

        // Notification delivered to COC database channel (BR-02)
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->coc->id,
            'type' => 'App\Notifications\ConnectEdNotification',
        ]);
    }

    public function test_coc_can_view_assigned_tasks_and_update_status_to_done(): void
    {
        $task = Task::create([
            'title' => 'Verify Weekly Accomplishment Report',
            'created_by' => $this->oc->id,
            'assigned_to' => $this->coc->id,
            'priority' => TaskPriority::MEDIUM,
            'status' => TaskStatus::IN_PROGRESS,
            'due_date' => now()->addDays(1),
        ]);

        $response = $this->actingAs($this->coc)->patch(route('tasks.update', $task), [
            'status' => TaskStatus::DONE->value,
        ]);

        $response->assertRedirect(route('tasks.index'));
        $task->refresh();

        $this->assertEquals(TaskStatus::DONE, $task->status);
        $this->assertNotNull($task->completed_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'task.status_changed',
            'actor_id' => $this->coc->id,
        ]);

        // Creator notified of task completion
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->oc->id,
            'type' => 'App\Notifications\ConnectEdNotification',
        ]);
    }

    public function test_student_cannot_access_or_create_tasks(): void
    {
        $response = $this->actingAs($this->student)->get(route('tasks.index'));
        $response->assertForbidden();

        $response = $this->actingAs($this->student)->post(route('tasks.store'), [
            'title' => 'Unauthorized Task',
            'assigned_to' => $this->coc->id,
            'priority' => 'low',
        ]);
        $response->assertForbidden();
    }

    public function test_oc_can_assign_single_student_to_coc(): void
    {
        $response = $this->actingAs($this->oc)->post(
            route('coordinator.students.assign-coc', $this->studentProfile),
            ['assigned_coc_id' => $this->coc->id]
        );

        $response->assertRedirect();
        $this->studentProfile->refresh();

        $this->assertEquals($this->coc->id, $this->studentProfile->assigned_coc_id);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'student.coc_assigned',
            'actor_id' => $this->oc->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->coc->id,
            'type' => 'App\Notifications\ConnectEdNotification',
        ]);
    }

    public function test_oc_can_bulk_assign_student_cohort_to_coc(): void
    {
        $student2 = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);
        $profile2 = StudentProfile::create([
            'user_id' => $student2->id,
            'student_number' => 'BSAU-2026-COHORT-2',
            'program' => 'BSIT',
            'year_level' => '4',
        ]);

        $response = $this->actingAs($this->oc)->post(
            route('coordinator.students.bulk-assign-coc'),
            [
                'student_ids' => [$this->studentProfile->id, $profile2->id],
                'assigned_coc_id' => $this->coc->id,
            ]
        );

        $response->assertRedirect();
        $this->studentProfile->refresh();
        $profile2->refresh();

        $this->assertEquals($this->coc->id, $this->studentProfile->assigned_coc_id);
        $this->assertEquals($this->coc->id, $profile2->assigned_coc_id);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'student.coc_bulk_assigned',
            'actor_id' => $this->oc->id,
        ]);
    }
}
