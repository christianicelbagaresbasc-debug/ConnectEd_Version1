<?php

namespace Tests\Feature\Documents;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Document;
use App\Models\DocumentBatch;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected DocumentService $documentService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('documents');

        $this->student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
        ]);

        $this->documentService = app(DocumentService::class);
    }

    public function test_rejects_unauthorized_file_extension(): void
    {
        $response = $this->actingAs($this->student)->postJson(route('documents.slots'), [
            'files' => [
                [
                    'name' => 'malicious_script.php',
                    'size' => 1024,
                    'category' => 'resume',
                    'owner_user_id' => $this->student->id,
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['files']);
    }

    public function test_rejects_oversized_resume(): void
    {
        $response = $this->actingAs($this->student)->postJson(route('documents.slots'), [
            'files' => [
                [
                    'name' => 'huge_resume.pdf',
                    'size' => 6 * 1024 * 1024, // 6 MB (Resume limit is 5MB)
                    'category' => 'resume',
                    'owner_user_id' => $this->student->id,
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['files']);
    }

    public function test_rejects_file_with_mismatched_magic_bytes_during_batch_confirmation(): void
    {
        $batch = DocumentBatch::create([
            'user_id' => $this->student->id,
            'status' => 'pending',
            'file_count' => 1,
        ]);

        $fakePdf = Document::create([
            'owner_user_id' => $this->student->id,
            'category' => 'resume',
            'original_name' => 'trojan.pdf',
            'storage_key' => 'documents/2026/01/fake.pdf',
            'stored_path' => 'documents/2026/01/fake.pdf',
            'disk' => 'documents',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'validation_status' => 'pending',
            'uploaded_by' => $this->student->id,
            'upload_batch_id' => $batch->id,
            'version_no' => 1,
            'is_current' => true,
            'review_status' => 'pending_review',
            'school_year' => '2026-2027',
        ]);

        // Place plain text or executable bytes into storage pretending to be a PDF
        Storage::disk('documents')->put('documents/2026/01/fake.pdf', 'MZThisIsAnExecutableFileNotAPdf');

        $response = $this->actingAs($this->student)
            ->postJson(route('documents.confirm', $batch));

        $response->assertStatus(200);

        $fakePdf->refresh();
        $this->assertEquals('rejected', $fakePdf->validation_status);
        $this->assertStringContainsString('Security check failed', $fakePdf->validation_remark);
    }

    public function test_accepts_valid_file_and_calculates_sha256_checksum(): void
    {
        $batch = DocumentBatch::create([
            'user_id' => $this->student->id,
            'status' => 'pending',
            'file_count' => 1,
        ]);

        $validPdf = Document::create([
            'owner_user_id' => $this->student->id,
            'category' => 'resume',
            'original_name' => 'legit_resume.pdf',
            'storage_key' => 'documents/2026/01/legit.pdf',
            'stored_path' => 'documents/2026/01/legit.pdf',
            'disk' => 'documents',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'validation_status' => 'pending',
            'uploaded_by' => $this->student->id,
            'upload_batch_id' => $batch->id,
            'version_no' => 1,
            'is_current' => true,
            'review_status' => 'pending_review',
            'school_year' => '2026-2027',
        ]);

        $pdfContent = "%PDF-1.7\nSample Genuine Resume Content for BSAU ICS OJT\n%%EOF";
        Storage::disk('documents')->put('documents/2026/01/legit.pdf', $pdfContent);

        $response = $this->actingAs($this->student)
            ->postJson(route('documents.confirm', $batch));

        $response->assertStatus(200);

        $validPdf->refresh();
        $this->assertEquals('ok', $validPdf->validation_status);
        $this->assertNull($validPdf->validation_remark);
        $this->assertEquals(hash('sha256', $pdfContent), $validPdf->checksum_sha256);
    }
}
