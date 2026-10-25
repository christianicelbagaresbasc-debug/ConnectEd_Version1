<?php

namespace Tests\Feature\Documents;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentAuthorizationMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected User $oc;

    protected User $studentA;

    protected User $studentB;

    protected User $ua;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('documents');

        $this->oc = User::factory()->create(['role' => UserRole::OC, 'status' => UserStatus::ACTIVE]);
        $this->studentA = User::factory()->create(['role' => UserRole::STUDENT, 'status' => UserStatus::ACTIVE]);
        $this->studentB = User::factory()->create(['role' => UserRole::STUDENT, 'status' => UserStatus::ACTIVE]);
        $this->ua = User::factory()->create(['role' => UserRole::UA, 'status' => UserStatus::ACTIVE]);
    }

    public function test_coordinator_can_request_upload_slots_for_any_category(): void
    {
        $response = $this->actingAs($this->oc)->postJson(route('documents.slots'), [
            'files' => [
                [
                    'name' => 'ojt_guidelines_2026.pdf',
                    'size' => 1024 * 500, // 500 KB
                    'category' => 'template',
                    'mime' => 'application/pdf',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['batch_id', 'files' => [['document_id', 'storage_key', 'upload_url']]]);
    }

    public function test_student_can_request_upload_slot_for_own_resume(): void
    {
        $response = $this->actingAs($this->studentA)->postJson(route('documents.slots'), [
            'files' => [
                [
                    'name' => 'juan_resume.pdf',
                    'size' => 1024 * 1024, // 1 MB
                    'category' => 'resume',
                    'owner_user_id' => $this->studentA->id,
                    'mime' => 'application/pdf',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('documents', [
            'owner_user_id' => $this->studentA->id,
            'category' => 'resume',
            'uploaded_by' => $this->studentA->id,
        ]);
    }

    public function test_student_cannot_upload_for_another_student(): void
    {
        $response = $this->actingAs($this->studentA)->postJson(route('documents.slots'), [
            'files' => [
                [
                    'name' => 'fake_resume.pdf',
                    'size' => 1024 * 1024,
                    'category' => 'resume',
                    'owner_user_id' => $this->studentB->id, // Another student
                    'mime' => 'application/pdf',
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['files']);
    }

    public function test_student_cannot_upload_company_verification_docs(): void
    {
        $response = $this->actingAs($this->studentA)->postJson(route('documents.slots'), [
            'files' => [
                [
                    'name' => 'bir_2303.pdf',
                    'size' => 1024 * 500,
                    'category' => 'verification',
                    'owner_user_id' => $this->studentA->id,
                    'mime' => 'application/pdf',
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['files']);
    }

    public function test_student_can_download_own_document_but_not_others(): void
    {
        $docA = Document::create([
            'owner_user_id' => $this->studentA->id,
            'category' => 'resume',
            'original_name' => 'student_a_resume.pdf',
            'storage_key' => 'documents/2026/01/doc_a.pdf',
            'stored_path' => 'documents/2026/01/doc_a.pdf',
            'disk' => 'documents',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'validation_status' => 'ok',
            'uploaded_by' => $this->studentA->id,
            'version_no' => 1,
            'is_current' => true,
            'review_status' => 'approved',
            'school_year' => '2026-2027',
        ]);
        Storage::disk('documents')->put('documents/2026/01/doc_a.pdf', "%PDF-1.4\nTest Resume Content");

        // Student A downloads own resume -> allowed (redirects to download URL)
        $resA = $this->actingAs($this->studentA)->get(route('documents.download', $docA));
        $resA->assertRedirect();

        // Student B tries to download Student A resume -> 403 Forbidden
        $resB = $this->actingAs($this->studentB)->get(route('documents.download', $docA));
        $resB->assertStatus(403);
    }

    public function test_university_attorney_can_download_moa_and_logs_audit_event(): void
    {
        $moaDoc = Document::create([
            'owner_user_id' => $this->studentA->id,
            'category' => 'moa',
            'original_name' => 'moa_agreement.pdf',
            'storage_key' => 'documents/2026/01/moa.pdf',
            'stored_path' => 'documents/2026/01/moa.pdf',
            'disk' => 'documents',
            'mime_type' => 'application/pdf',
            'size_bytes' => 2048,
            'validation_status' => 'ok',
            'uploaded_by' => $this->studentA->id,
            'version_no' => 1,
            'is_current' => true,
            'review_status' => 'pending_review',
            'school_year' => '2026-2027',
        ]);
        Storage::disk('documents')->put('documents/2026/01/moa.pdf', "%PDF-1.4\nMOA Content");

        $response = $this->actingAs($this->ua)->get(route('documents.download', $moaDoc));
        $response->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'document.downloaded',
            'subject_type' => Document::class,
            'subject_id' => $moaDoc->id,
            'actor_id' => $this->ua->id,
        ]);
    }
}
