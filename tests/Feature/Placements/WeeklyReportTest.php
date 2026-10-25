<?php

namespace Tests\Feature\Placements;

use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\OpportunityStatus;
use App\Enums\OpportunityWorkArrangement;
use App\Enums\PlacementStatus;
use App\Enums\UserRole;
use App\Enums\WeeklyReportStatus;
use App\Models\Company;
use App\Models\Document;
use App\Models\Opportunity;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\User;
use App\Models\WeeklyReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WeeklyReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $coordinator;

    protected User $student;

    protected User $otherStudent;

    protected StudentProfile $studentProfile;

    protected StudentProfile $otherStudentProfile;

    protected Company $company;

    protected Opportunity $opportunity;

    protected Placement $placement;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('documents');

        $this->coordinator = User::factory()->create([
            'role' => UserRole::OC,
            'status' => 'active',
            'must_change_password' => false,
        ]);

        $this->student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => 'active',
            'must_change_password' => false,
            'approved_at' => now(),
        ]);

        $this->otherStudent = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => 'active',
            'must_change_password' => false,
            'approved_at' => now(),
        ]);

        $this->studentProfile = StudentProfile::create([
            'user_id' => $this->student->id,
            'student_number' => '2023-1001',
            'program' => 'BSIT',
            'year_level' => '4th Year',
            'section' => '4-A',
            'has_active_placement' => true,
            'consented_at' => now(),
            'privacy_notice_version' => '1.0',
        ]);

        $this->otherStudentProfile = StudentProfile::create([
            'user_id' => $this->otherStudent->id,
            'student_number' => '2023-1002',
            'program' => 'BSIT',
            'year_level' => '4th Year',
            'section' => '4-B',
            'has_active_placement' => false,
            'consented_at' => now(),
            'privacy_notice_version' => '1.0',
        ]);

        $this->company = Company::create([
            'name' => 'Metro IT Systems',
            'classification' => CompanyClassification::EXISTING_PARTNER,
            'verification_status' => CompanyVerificationStatus::VERIFIED,
            'is_active' => true,
        ]);

        $this->opportunity = Opportunity::create([
            'company_id' => $this->company->id,
            'title' => 'Web Application Developer Intern',
            'description' => 'Develop Laravel systems with BSAU ICS standards.',
            'slots_total' => 2,
            'slots_filled' => 1,
            'location' => 'San Ildefonso, Bulacan',
            'work_arrangement' => OpportunityWorkArrangement::HYBRID,
            'status' => OpportunityStatus::OPEN,
            'created_by' => $this->coordinator->id,
            'approved_by' => $this->coordinator->id,
            'approved_at' => now(),
            'school_year' => '2026-2027',
        ]);

        $this->placement = Placement::create([
            'student_profile_id' => $this->studentProfile->id,
            'company_id' => $this->company->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => PlacementStatus::ONGOING,
            'is_active' => true,
            'confirmed_by' => $this->coordinator->id,
            'school_year' => '2026-2027',
        ]);
    }

    public function test_student_can_upload_and_submit_weekly_report(): void
    {
        $file = UploadedFile::fake()->create('week_1_accomplishment.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->student)
            ->post(route('student.placements.reports.store', $this->placement), [
                'week_start' => '2026-11-03',
                'report_file' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('weekly_reports', [
            'placement_id' => $this->placement->id,
            'status' => WeeklyReportStatus::SUBMITTED->value,
        ]);

        $report = WeeklyReport::where('placement_id', $this->placement->id)->first();
        $this->assertNotNull($report);
        $this->assertNotNull($report->document_id);
        $this->assertEquals('2026-11-02', $report->week_start->toDateString()); // Carbon startOfWeek() Monday

        // Verify document record
        $this->assertDatabaseHas('documents', [
            'id' => $report->document_id,
            'category' => 'weekly_report',
            'owner_user_id' => $this->student->id,
        ]);

        // Verify notification sent to coordinator
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->coordinator->id,
            'notifiable_type' => User::class,
        ]);
    }

    public function test_student_cannot_submit_report_for_another_students_placement(): void
    {
        $file = UploadedFile::fake()->create('hack_attempt.pdf', 200, 'application/pdf');

        $response = $this->actingAs($this->otherStudent)
            ->post(route('student.placements.reports.store', $this->placement), [
                'week_start' => '2026-11-03',
                'report_file' => $file,
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('weekly_reports', [
            'placement_id' => $this->placement->id,
        ]);
    }

    public function test_coordinator_can_approve_weekly_report(): void
    {
        $doc = Document::create([
            'category' => 'weekly_report',
            'disk' => 'documents',
            'storage_key' => 'weekly_reports/test1.pdf',
            'stored_path' => 'weekly_reports/test1.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'checksum_sha256' => 'dummyhash123',
            'owner_user_id' => $this->student->id,
            'uploaded_by' => $this->student->id,
            'documentable_type' => Placement::class,
            'documentable_id' => $this->placement->id,
            'validation_status' => 'ok',
        ]);

        $report = WeeklyReport::create([
            'placement_id' => $this->placement->id,
            'week_start' => '2026-11-02',
            'document_id' => $doc->id,
            'status' => WeeklyReportStatus::SUBMITTED,
        ]);

        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.weekly-reports.review', $report), [
                'status' => 'reviewed',
                'remark' => 'Good documentation of daily tasks.',
            ]);

        $response->assertRedirect();
        $fresh = $report->fresh();
        $this->assertEquals(WeeklyReportStatus::REVIEWED, $fresh->status);
        $this->assertEquals($this->coordinator->id, $fresh->reviewed_by);
        $this->assertEquals('Good documentation of daily tasks.', $fresh->remark);

        // Verify student received notification
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->student->id,
            'notifiable_type' => User::class,
        ]);
    }

    public function test_coordinator_flagging_weekly_report_requires_remark(): void
    {
        $doc = Document::create([
            'category' => 'weekly_report',
            'disk' => 'documents',
            'storage_key' => 'weekly_reports/test2.pdf',
            'stored_path' => 'weekly_reports/test2.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'checksum_sha256' => 'dummyhash123',
            'owner_user_id' => $this->student->id,
            'uploaded_by' => $this->student->id,
            'documentable_type' => Placement::class,
            'documentable_id' => $this->placement->id,
            'validation_status' => 'ok',
        ]);

        $report = WeeklyReport::create([
            'placement_id' => $this->placement->id,
            'week_start' => '2026-11-02',
            'document_id' => $doc->id,
            'status' => WeeklyReportStatus::SUBMITTED,
        ]);

        // Attempt flagging without remark
        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.weekly-reports.review', $report), [
                'status' => 'flagged',
            ]);

        $response->assertSessionHasErrors('remark');

        // Flag with remark
        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.weekly-reports.review', $report), [
                'status' => 'flagged',
                'remark' => 'Please include supervisor signature on page 2.',
            ]);

        $response->assertRedirect();
        $fresh = $report->fresh();
        $this->assertEquals(WeeklyReportStatus::FLAGGED, $fresh->status);
        $this->assertEquals('Please include supervisor signature on page 2.', $fresh->remark);
    }
}
