<?php

namespace Tests\Feature\Coordinator;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentRegistrationApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected User $coordinator;

    protected User $pendingStudent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coordinator = User::factory()->create([
            'role' => UserRole::OC,
            'status' => UserStatus::ACTIVE,
            'must_change_password' => false,
        ]);

        $this->pendingStudent = User::factory()->create([
            'name' => 'Alice Guo',
            'email' => 'alice@bsau.edu.ph',
            'role' => UserRole::STUDENT,
            'status' => UserStatus::PENDING,
            'must_change_password' => false,
        ]);

        StudentProfile::create([
            'user_id' => $this->pendingStudent->id,
            'student_number' => '2023-20001',
            'program' => 'BSIT',
            'year_level' => '4th Year',
            'school_year' => '2026-2027',
            'has_active_placement' => false,
            'consented_at' => now(),
            'privacy_notice_version' => 'v1.0-draft',
        ]);
    }

    public function test_coordinator_can_view_pending_students_list(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('coordinator.students.pending'));

        $response->assertStatus(200);
        $response->assertSee('Student Registration Approvals');
        $response->assertSee('Alice Guo');
        $response->assertSee('2023-20001');
    }

    public function test_coordinator_can_filter_pending_students_by_search_or_program(): void
    {
        $otherStudent = User::factory()->create([
            'name' => 'Bob Smith',
            'email' => 'bob@bsau.edu.ph',
            'role' => UserRole::STUDENT,
            'status' => UserStatus::PENDING,
        ]);
        StudentProfile::create([
            'user_id' => $otherStudent->id,
            'student_number' => '2023-99999',
            'program' => 'BSIT',
            'year_level' => '4th Year',
            'school_year' => '2026-2027',
            'has_active_placement' => false,
            'consented_at' => now(),
            'privacy_notice_version' => 'v1.0-draft',
        ]);

        $response = $this->actingAs($this->coordinator)
            ->get(route('coordinator.students.pending', ['search' => 'Alice']));

        $response->assertStatus(200);
        $response->assertSee('Alice Guo');
        $response->assertDontSee('Bob Smith');
    }

    public function test_coordinator_can_approve_pending_student(): void
    {
        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.students.approve', $this->pendingStudent));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->pendingStudent->refresh();
        $this->assertEquals(UserStatus::ACTIVE, $this->pendingStudent->status);
        $this->assertEquals($this->coordinator->id, $this->pendingStudent->approved_by);
        $this->assertNotNull($this->pendingStudent->approved_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'student.approved',
            'subject_type' => User::class,
            'subject_id' => $this->pendingStudent->id,
            'actor_id' => $this->coordinator->id,
        ]);

        // Student can now log in
        auth()->logout();

        $loginRes = $this->post('/login', [
            'email' => $this->pendingStudent->email,
            'password' => 'password',
        ]);
        $this->assertAuthenticatedAs($this->pendingStudent);
    }

    public function test_coordinator_can_reject_pending_student_with_reason(): void
    {
        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.students.reject', $this->pendingStudent), [
                'reason' => 'Student number mismatch against university registrar records.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertSoftDeleted('users', [
            'id' => $this->pendingStudent->id,
            'status' => UserStatus::SUSPENDED->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'student.rejected',
            'subject_type' => User::class,
            'subject_id' => $this->pendingStudent->id,
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_non_coordinator_cannot_approve_or_reject_students(): void
    {
        $studentUser = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->actingAs($studentUser)->get(route('coordinator.students.pending'));
        $response->assertStatus(403);

        $response = $this->actingAs($studentUser)
            ->post(route('coordinator.students.approve', $this->pendingStudent));
        $response->assertStatus(403);
    }
}
