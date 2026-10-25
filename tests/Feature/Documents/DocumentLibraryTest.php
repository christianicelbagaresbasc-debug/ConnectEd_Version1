<?php

namespace Tests\Feature\Documents;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Document;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected User $oc;

    protected User $student;

    protected User $otherStudent;

    protected Document $studentResume;

    protected Document $otherStudentResume;

    protected Document $templateDoc;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->oc = User::factory()->create([
            'role' => UserRole::OC,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        StudentProfile::create([
            'user_id' => $this->student->id,
            'student_number' => 'BSAU-2026-DOC-1',
            'program' => 'BSIT',
            'year_level' => '4',
        ]);

        $this->otherStudent = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        StudentProfile::create([
            'user_id' => $this->otherStudent->id,
            'student_number' => 'BSAU-2026-DOC-2',
            'program' => 'BSIT',
            'year_level' => '4',
        ]);

        // Create sample files in storage
        Storage::disk('local')->put('resumes/resume1.pdf', 'dummy content 1');
        Storage::disk('local')->put('resumes/resume2.pdf', 'dummy content 2');
        Storage::disk('local')->put('templates/moa_template.docx', 'dummy template');

        $this->studentResume = Document::create([
            'owner_user_id' => $this->student->id,
            'documentable_type' => 'App\Models\StudentProfile',
            'documentable_id' => 1,
            'category' => 'resume',
            'original_name' => 'student1_resume.pdf',
            'storage_key' => 'resumes/resume1.pdf',
            'stored_path' => 'resumes/resume1.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'checksum_sha256' => hash('sha256', 'dummy content 1'),
            'validation_status' => 'ok',
            'review_status' => 'accepted',
            'uploaded_by' => $this->student->id,
        ]);

        $this->otherStudentResume = Document::create([
            'owner_user_id' => $this->otherStudent->id,
            'documentable_type' => 'App\Models\StudentProfile',
            'documentable_id' => 2,
            'category' => 'resume',
            'original_name' => 'other_student_resume.pdf',
            'storage_key' => 'resumes/resume2.pdf',
            'stored_path' => 'resumes/resume2.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'size_bytes' => 2048,
            'checksum_sha256' => hash('sha256', 'dummy content 2'),
            'validation_status' => 'ok',
            'review_status' => 'accepted',
            'uploaded_by' => $this->otherStudent->id,
        ]);

        $this->templateDoc = Document::create([
            'owner_user_id' => $this->oc->id,
            'category' => 'template',
            'original_name' => 'bsau_moa_template.docx',
            'storage_key' => 'templates/moa_template.docx',
            'stored_path' => 'templates/moa_template.docx',
            'disk' => 'local',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'size_bytes' => 4096,
            'checksum_sha256' => hash('sha256', 'dummy template'),
            'validation_status' => 'ok',
            'review_status' => 'accepted',
            'uploaded_by' => $this->oc->id,
        ]);
    }

    public function test_oc_can_view_all_documents_in_library(): void
    {
        $response = $this->actingAs($this->oc)->get(route('document-library.index'));

        $response->assertOk();
        $response->assertSee('student1_resume.pdf');
        $response->assertSee('other_student_resume.pdf');
        $response->assertSee('bsau_moa_template.docx');
    }

    public function test_student_only_views_own_documents_and_templates(): void
    {
        $response = $this->actingAs($this->student)->get(route('document-library.index'));

        $response->assertOk();
        $response->assertSee('student1_resume.pdf');
        $response->assertSee('bsau_moa_template.docx');
        $response->assertDontSee('other_student_resume.pdf');
    }

    public function test_coordinator_can_view_completeness_matrix(): void
    {
        $response = $this->actingAs($this->oc)->get(route('document-library.completeness'));

        $response->assertOk();
        $response->assertSee('Document Completeness Matrix');
        $response->assertSee('BSAU-2026-DOC-1');
        $response->assertSee('BSAU-2026-DOC-2');
    }

    public function test_student_cannot_access_completeness_matrix(): void
    {
        $response = $this->actingAs($this->student)->get(route('document-library.completeness'));
        $response->assertForbidden();
    }

    public function test_authorized_user_can_download_zip_bundle_and_audit_is_recorded(): void
    {
        $response = $this->actingAs($this->oc)->post(route('document-library.bundle'), [
            'document_ids' => [$this->studentResume->id, $this->templateDoc->id],
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/zip');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'document.bundle_downloaded',
            'actor_id' => $this->oc->id,
        ]);
    }

    public function test_bundle_download_enforces_50_files_limit(): void
    {
        $fakeIds = range(1, 51);

        $response = $this->actingAs($this->oc)->post(route('document-library.bundle'), [
            'document_ids' => $fakeIds,
        ]);

        $response->assertSessionHasErrors(['bundle']);
    }
}
