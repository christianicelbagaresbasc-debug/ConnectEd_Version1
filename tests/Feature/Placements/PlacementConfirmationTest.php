<?php

namespace Tests\Feature\Placements;

use App\Enums\ApplicationStatus;
use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\OpportunityStatus;
use App\Enums\OpportunityWorkArrangement;
use App\Enums\PlacementStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Company;
use App\Models\Opportunity;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\PlacementService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlacementConfirmationTest extends TestCase
{
    use RefreshDatabase;

    protected User $coordinator;

    protected User $student;

    protected StudentProfile $studentProfile;

    protected Company $company;

    protected Opportunity $opportunity;

    protected Opportunity $otherOpportunity;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->studentProfile = StudentProfile::create([
            'user_id' => $this->student->id,
            'student_number' => '2023-1001',
            'program' => 'BSIT',
            'year_level' => '4th Year',
            'section' => '4-A',
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
            'slots_filled' => 0,
            'location' => 'San Ildefonso, Bulacan',
            'work_arrangement' => OpportunityWorkArrangement::HYBRID,
            'status' => OpportunityStatus::OPEN,
            'created_by' => $this->coordinator->id,
            'approved_by' => $this->coordinator->id,
            'approved_at' => now(),
            'school_year' => '2026-2027',
        ]);

        $this->otherOpportunity = Opportunity::create([
            'company_id' => $this->company->id,
            'title' => 'QA Intern',
            'description' => 'QA intern testing.',
            'slots_total' => 3,
            'slots_filled' => 0,
            'location' => 'San Ildefonso, Bulacan',
            'work_arrangement' => OpportunityWorkArrangement::ONSITE,
            'status' => OpportunityStatus::OPEN,
            'created_by' => $this->coordinator->id,
            'approved_by' => $this->coordinator->id,
            'approved_at' => now(),
            'school_year' => '2026-2027',
        ]);
    }

    public function test_dual_confirmation_finalizes_placement_when_student_confirms_after_coordinator(): void
    {
        // 1. Application is accepted by coordinator with auto_confirm
        $app = Application::create([
            'student_profile_id' => $this->studentProfile->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => ApplicationStatus::ACCEPTED,
            'submitted_at' => now()->subDay(),
            'decided_by' => $this->coordinator->id,
            'decided_at' => now(),
            'oc_confirmed_at' => now(),
            'school_year' => '2026-2027',
        ]);

        // Student also had an open application at otherOpportunity
        $otherApp = Application::create([
            'student_profile_id' => $this->studentProfile->id,
            'opportunity_id' => $this->otherOpportunity->id,
            'status' => ApplicationStatus::PENDING,
            'submitted_at' => now()->subDay(),
            'school_year' => '2026-2027',
        ]);

        // Student confirms
        $response = $this->actingAs($this->student)
            ->post(route('student.applications.confirm', $app));

        $response->assertRedirect();

        // Verify Application is now PLACED
        $this->assertEquals(ApplicationStatus::PLACED, $app->fresh()->status);
        $this->assertNotNull($app->fresh()->student_confirmed_at);

        // Verify Placement record exists
        $placement = Placement::where('student_profile_id', $this->studentProfile->id)->first();
        $this->assertNotNull($placement);
        $this->assertEquals(PlacementStatus::PLACED, $placement->status);
        $this->assertTrue($placement->is_active);
        $this->assertEquals($this->company->id, $placement->company_id);

        // Verify Student Profile has active placement flag
        $this->assertTrue($this->studentProfile->fresh()->has_active_placement);

        // Verify Opportunity slot incremented
        $this->assertEquals(1, $this->opportunity->fresh()->slots_filled);

        // Assumption A6: Verify other open application is auto-withdrawn
        $otherAppFresh = $otherApp->fresh();
        $this->assertEquals(ApplicationStatus::WITHDRAWN, $otherAppFresh->status);
        $this->assertStringContainsString('Placement finalized', $otherAppFresh->withdrawn_reason);
    }

    public function test_dual_confirmation_finalizes_placement_when_coordinator_confirms_after_student(): void
    {
        // Application is accepted, student confirmed first
        $app = Application::create([
            'student_profile_id' => $this->studentProfile->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => ApplicationStatus::ACCEPTED,
            'submitted_at' => now()->subDays(2),
            'decided_by' => $this->coordinator->id,
            'decided_at' => now()->subDay(),
            'student_confirmed_at' => now()->subDay(),
            'school_year' => '2026-2027',
        ]);

        // Coordinator confirms endorsement
        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.applications.confirm', $app));

        $response->assertRedirect();

        // Verify placement is finalized
        $this->assertEquals(ApplicationStatus::PLACED, $app->fresh()->status);
        $this->assertNotNull($app->fresh()->oc_confirmed_at);
        $this->assertDatabaseHas('placements', [
            'student_profile_id' => $this->studentProfile->id,
            'application_id' => $app->id,
            'is_active' => true,
        ]);
    }

    public function test_rule_br19_single_active_placement_concurrency_and_integrity_check(): void
    {
        // Setup an existing active placement for student
        Placement::create([
            'student_profile_id' => $this->studentProfile->id,
            'company_id' => $this->company->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => PlacementStatus::ONGOING,
            'is_active' => true,
            'confirmed_by' => $this->coordinator->id,
            'school_year' => '2026-2027',
        ]);

        $this->studentProfile->update(['has_active_placement' => true]);

        // Second application trying to finalize
        $secondApp = Application::create([
            'student_profile_id' => $this->studentProfile->id,
            'opportunity_id' => $this->otherOpportunity->id,
            'status' => ApplicationStatus::ACCEPTED,
            'submitted_at' => now()->subDay(),
            'school_year' => '2026-2027',
            'oc_confirmed_at' => now(),
        ]);

        $service = app(PlacementService::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Rule BR-19 prohibits multiple active placements');

        $service->finalizePlacement($secondApp, $this->coordinator);
    }

    public function test_opportunity_status_changes_to_filled_when_capacity_reached(): void
    {
        // 1-slot opportunity
        $tightOpportunity = Opportunity::create([
            'company_id' => $this->company->id,
            'title' => 'Single Slot Intern',
            'description' => 'Single slot intern description',
            'slots_total' => 1,
            'slots_filled' => 0,
            'location' => 'San Ildefonso, Bulacan',
            'work_arrangement' => OpportunityWorkArrangement::ONSITE,
            'status' => OpportunityStatus::OPEN,
            'created_by' => $this->coordinator->id,
            'approved_by' => $this->coordinator->id,
            'approved_at' => now(),
            'school_year' => '2026-2027',
        ]);

        $app = Application::create([
            'student_profile_id' => $this->studentProfile->id,
            'opportunity_id' => $tightOpportunity->id,
            'status' => ApplicationStatus::ACCEPTED,
            'submitted_at' => now()->subDay(),
            'oc_confirmed_at' => now(),
            'school_year' => '2026-2027',
        ]);

        $service = app(PlacementService::class);
        $service->confirmByStudent($app, $this->student);

        $this->assertEquals(OpportunityStatus::CLOSED, $tightOpportunity->fresh()->status);
        $this->assertEquals(1, $tightOpportunity->fresh()->slots_filled);
    }

    public function test_coordinator_can_authorize_placement_start(): void
    {
        $placement = Placement::create([
            'student_profile_id' => $this->studentProfile->id,
            'company_id' => $this->company->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => PlacementStatus::PLACED,
            'is_active' => true,
            'confirmed_by' => $this->coordinator->id,
            'school_year' => '2026-2027',
        ]);

        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.placements.authorize', $placement), [
                'start_date' => '2026-11-01',
                'end_date' => '2027-02-28',
                'note' => 'All pre-deployment requirements and MOA verified.',
            ]);

        $response->assertRedirect();

        $fresh = $placement->fresh();
        $this->assertEquals(PlacementStatus::AUTHORIZED, $fresh->status);
        $this->assertEquals('2026-11-01', $fresh->start_date->toDateString());
        $this->assertEquals('2027-02-28', $fresh->end_date->toDateString());
        $this->assertEquals('All pre-deployment requirements and MOA verified.', $fresh->start_authorization_note);
        $this->assertNotNull($fresh->start_authorized_at);
    }

    public function test_coordinator_can_progress_placement_status_to_ongoing_completed_and_terminated(): void
    {
        $placement = Placement::create([
            'student_profile_id' => $this->studentProfile->id,
            'company_id' => $this->company->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => PlacementStatus::AUTHORIZED,
            'is_active' => true,
            'confirmed_by' => $this->coordinator->id,
            'school_year' => '2026-2027',
        ]);
        $this->studentProfile->update(['has_active_placement' => true]);

        // Move to ONGOING
        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.placements.status', $placement), [
                'status' => 'ongoing',
                'remark' => 'Student reported on first day.',
            ]);

        $response->assertRedirect();
        $this->assertEquals(PlacementStatus::ONGOING, $placement->fresh()->status);
        $this->assertTrue($placement->fresh()->is_active);

        // Move to COMPLETED
        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.placements.status', $placement), [
                'status' => 'completed',
                'remark' => 'Completed 500 hours successfully.',
            ]);

        $response->assertRedirect();
        $fresh = $placement->fresh();
        $this->assertEquals(PlacementStatus::COMPLETED, $fresh->status);
        $this->assertFalse($fresh->is_active);
        // Student active placement released
        $this->assertFalse($this->studentProfile->fresh()->has_active_placement);
    }

    public function test_coordinator_terminating_placement_requires_reason(): void
    {
        $placement = Placement::create([
            'student_profile_id' => $this->studentProfile->id,
            'company_id' => $this->company->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => PlacementStatus::ONGOING,
            'is_active' => true,
            'confirmed_by' => $this->coordinator->id,
            'school_year' => '2026-2027',
        ]);
        $this->studentProfile->update(['has_active_placement' => true]);

        // Attempt termination without reason
        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.placements.status', $placement), [
                'status' => 'terminated',
            ]);

        $response->assertSessionHasErrors('termination_reason');

        // Terminate with reason
        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.placements.status', $placement), [
                'status' => 'terminated',
                'termination_reason' => 'Company facility relocated outside approved zone.',
            ]);

        $response->assertRedirect();
        $fresh = $placement->fresh();
        $this->assertEquals(PlacementStatus::TERMINATED, $fresh->status);
        $this->assertFalse($fresh->is_active);
        $this->assertEquals('Company facility relocated outside approved zone.', $fresh->termination_reason);
        $this->assertFalse($this->studentProfile->fresh()->has_active_placement);
    }
}
