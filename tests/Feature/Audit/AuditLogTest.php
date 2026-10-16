<?php

namespace Tests\Feature\Audit;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_logs_are_append_only_and_cannot_be_updated(): void
    {
        $log = AuditLog::create([
            'action' => 'security.probe',
            'ip_address' => '127.0.0.1',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Audit log records are append-only and cannot be updated.');

        $log->update(['action' => 'tampered']);
    }

    public function test_audit_logs_cannot_be_deleted(): void
    {
        $log = AuditLog::create([
            'action' => 'security.probe',
            'ip_address' => '127.0.0.1',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Audit log records are immutable and cannot be deleted.');

        $log->delete();
    }

    public function test_audit_service_records_accurate_log_entry(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::OC,
            'status' => UserStatus::ACTIVE,
        ]);

        $this->actingAs($user);

        $log = AuditService::log('system.test_event', $user, [
            'meta' => 'test value',
            'nested' => ['a' => 1, 'b' => 2],
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'actor_id' => $user->id,
            'action' => 'system.test_event',
            'subject_type' => User::class,
            'subject_id' => $user->id,
        ]);

        $this->assertEquals(['a' => 1, 'b' => 2], $log->fresh()->changes['nested']);
    }

    public function test_coordinator_can_view_and_filter_audit_logs(): void
    {
        $coordinator = User::factory()->create([
            'role' => UserRole::OC,
            'status' => UserStatus::ACTIVE,
            'must_change_password' => false,
        ]);

        AuditLog::create([
            'actor_id' => $coordinator->id,
            'action' => 'student.approved',
            'created_at' => now(),
        ]);

        AuditLog::create([
            'actor_id' => $coordinator->id,
            'action' => 'staff.provisioned',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($coordinator)
            ->get(route('coordinator.audit-logs.index', ['action' => 'student.approved']));

        $response->assertStatus(200);
        $response->assertSee('student.approved');
        $response->assertDontSee('staff.provisioned');
    }

    public function test_non_coordinator_cannot_access_audit_logs(): void
    {
        $student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->actingAs($student)->get(route('coordinator.audit-logs.index'));
        $response->assertStatus(403);
    }
}
