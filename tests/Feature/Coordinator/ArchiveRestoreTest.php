<?php

namespace Tests\Feature\Coordinator;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveRestoreTest extends TestCase
{
    use RefreshDatabase;

    protected User $coordinator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coordinator = User::factory()->create([
            'role' => UserRole::OC,
            'status' => UserStatus::ACTIVE,
            'must_change_password' => false,
        ]);
    }

    public function test_coordinator_can_view_archive_tabs(): void
    {
        $trashedUser = User::factory()->create([
            'name' => 'Archived Person',
            'role' => UserRole::STUDENT,
        ]);
        $trashedUser->delete();

        $response = $this->actingAs($this->coordinator)->get(route('coordinator.archive.index', ['tab' => 'users']));
        $response->assertStatus(200);
        $response->assertSee('Archived Accounts');
        $response->assertSee('Archived Person');

        $docResponse = $this->actingAs($this->coordinator)->get(route('coordinator.archive.index', ['tab' => 'documents']));
        $docResponse->assertStatus(200);
        $docResponse->assertSee('Archived Documents');
    }

    public function test_coordinator_can_restore_soft_deleted_user(): void
    {
        $trashedUser = User::factory()->create([
            'name' => 'Recoverable User',
            'role' => UserRole::STUDENT,
        ]);
        $trashedUser->delete();

        $this->assertSoftDeleted('users', ['id' => $trashedUser->id]);

        $response = $this->actingAs($this->coordinator)->post(
            route('coordinator.archive.restore', ['type' => 'users', 'id' => $trashedUser->id])
        );

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertNotSoftDeleted('users', ['id' => $trashedUser->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'record.restored',
            'subject_type' => User::class,
            'subject_id' => $trashedUser->id,
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_coordinator_can_restore_soft_deleted_document(): void
    {
        $document = Document::create([
            'owner_user_id' => $this->coordinator->id,
            'category' => 'template',
            'original_name' => 'internship_guidelines.pdf',
            'storage_key' => 'documents/test.pdf',
            'stored_path' => 'documents/test.pdf',
            'disk' => 'documents',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'validation_status' => 'ok',
            'uploaded_by' => $this->coordinator->id,
            'version_no' => 1,
            'is_current' => true,
            'review_status' => 'approved',
            'school_year' => '2026-2027',
        ]);
        $document->delete();

        $this->assertSoftDeleted('documents', ['id' => $document->id]);

        $response = $this->actingAs($this->coordinator)->post(
            route('coordinator.archive.restore', ['type' => 'documents', 'id' => $document->id])
        );

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertNotSoftDeleted('documents', ['id' => $document->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'record.restored',
            'subject_type' => Document::class,
            'subject_id' => $document->id,
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_non_coordinator_cannot_access_or_restore_archive(): void
    {
        $student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
        ]);

        $trashedUser = User::factory()->create();
        $trashedUser->delete();

        $response = $this->actingAs($student)->get(route('coordinator.archive.index'));
        $response->assertStatus(403);

        $postResponse = $this->actingAs($student)->post(
            route('coordinator.archive.restore', ['type' => 'users', 'id' => $trashedUser->id])
        );
        $postResponse->assertStatus(403);
    }
}
