<?php

namespace Tests\Feature\Applications;

use App\Enums\ApplicationStatus;
use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\OpportunityStatus;
use App\Enums\OpportunityWorkArrangement;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Company;
use App\Models\Opportunity;
use App\Models\PartnerAccount;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $coordinator;

    protected User $student;

    protected StudentProfile $studentProfile;

    protected User $pcUser;

    protected Company $company;

    protected Opportunity $opportunity;

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

        $this->pcUser = User::factory()->create([
            'role' => UserRole::PC,
            'status' => 'active',
            'must_change_password' => false,
        ]);

        PartnerAccount::create([
            'user_id' => $this->pcUser->id,
            'company_id' => $this->company->id,
            'contact_person' => 'Lead Recruiter',
        ]);

        $this->opportunity = Opportunity::create([
            'company_id' => $this->company->id,
            'title' => 'Web Application Developer Intern',
            'description' => 'Develop Laravel systems with BSAU ICS standards.',
            'slots_total' => 3,
            'slots_filled' => 0,
            'location' => 'San Ildefonso, Bulacan',
            'work_arrangement' => OpportunityWorkArrangement::HYBRID,
            'status' => OpportunityStatus::OPEN,
            'created_by' => $this->coordinator->id,
            'approved_by' => $this->coordinator->id,
            'approved_at' => now(),
            'school_year' => '2026-2027',
        ]);
    }

    public function test_student_can_apply_for_open_opportunity(): void
    {
        $response = $this->actingAs($this->student)
            ->post(route('student.opportunities.apply', $this->opportunity), [
                'cover_note' => 'I am eager to contribute to web applications.',
            ]);

        $response->assertRedirect(route('student.applications.index'));
        $this->assertDatabaseHas('applications', [
            'student_profile_id' => $this->studentProfile->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => ApplicationStatus::PENDING->value,
            'cover_note' => 'I am eager to contribute to web applications.',
        ]);

        $this->assertDatabaseHas('application_status_logs', [
            'to_status' => ApplicationStatus::PENDING->value,
            'actor_id' => $this->student->id,
        ]);
    }

    public function test_student_cannot_apply_if_they_have_active_placement_br19(): void
    {
        $this->studentProfile->update(['has_active_placement' => true]);

        $response = $this->actingAs($this->student)
            ->post(route('student.opportunities.apply', $this->opportunity), [
                'cover_note' => 'Attempting duplicate placement.',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('applications', [
            'student_profile_id' => $this->studentProfile->id,
            'opportunity_id' => $this->opportunity->id,
        ]);
    }

    public function test_student_cannot_apply_twice_to_same_opportunity(): void
    {
        Application::create([
            'student_profile_id' => $this->studentProfile->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => ApplicationStatus::PENDING,
        ]);

        $response = $this->actingAs($this->student)
            ->from(route('opportunities.show', $this->opportunity))
            ->post(route('student.opportunities.apply', $this->opportunity), [
                'cover_note' => 'Duplicate application',
            ]);

        $response->assertRedirect(route('opportunities.show', $this->opportunity));
        $this->assertEquals(1, Application::where('student_profile_id', $this->studentProfile->id)->where('opportunity_id', $this->opportunity->id)->count());
    }

    public function test_coordinator_can_view_applications_queue(): void
    {
        $app = Application::create([
            'student_profile_id' => $this->studentProfile->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => ApplicationStatus::PENDING,
        ]);

        $response = $this->actingAs($this->coordinator)
            ->get(route('coordinator.applications.index'));

        $response->assertOk();
        $response->assertSee($this->student->name);
        $response->assertSee($this->opportunity->title);
    }

    public function test_rule_br20_no_match_scores_calculated_or_ranked_in_applicant_lists(): void
    {
        Application::create([
            'student_profile_id' => $this->studentProfile->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => ApplicationStatus::PENDING,
        ]);

        $response = $this->actingAs($this->coordinator)
            ->get(route('coordinator.applications.index'));

        $response->assertOk();
        // Ensure no score-related column or badge exists for coordinators/companies
        $response->assertDontSee('Match Score:');
        $response->assertDontSee('% Fit');
        $response->assertSee('Rule BR-20: Non-Ranking Application Queue');
    }

    public function test_coordinator_can_mark_application_under_review(): void
    {
        $this->withoutExceptionHandling();
        $app = Application::create([

            'student_profile_id' => $this->studentProfile->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => ApplicationStatus::PENDING,
        ]);

        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.applications.review', $app), [
                'remark' => 'Profile forwarded to department lead.',
            ]);

        $response->assertRedirect();
        $this->assertEquals(ApplicationStatus::UNDER_REVIEW, $app->fresh()->status);
        $this->assertDatabaseHas('application_status_logs', [
            'application_id' => $app->id,
            'from_status' => ApplicationStatus::PENDING->value,
            'to_status' => ApplicationStatus::UNDER_REVIEW->value,
        ]);
    }

    public function test_coordinator_can_extend_offer_with_auto_endorsement(): void
    {
        $app = Application::create([
            'student_profile_id' => $this->studentProfile->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => ApplicationStatus::UNDER_REVIEW,
        ]);

        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.applications.decide', $app), [
                'decision' => 'accepted',
                'remark' => 'Accepted for web developer slot.',
                'auto_confirm' => 1,
            ]);

        $response->assertRedirect();
        $fresh = $app->fresh();
        $this->assertEquals(ApplicationStatus::ACCEPTED, $fresh->status);
        $this->assertNotNull($fresh->oc_confirmed_at);
        $this->assertNull($fresh->student_confirmed_at);
    }

    public function test_coordinator_can_reject_application_with_reason(): void
    {
        $app = Application::create([
            'student_profile_id' => $this->studentProfile->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => ApplicationStatus::PENDING,
        ]);

        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.applications.decide', $app), [
                'decision' => 'rejected',
                'remark' => 'Candidate prerequisites incomplete.',
            ]);

        $response->assertRedirect();
        $this->assertEquals(ApplicationStatus::REJECTED, $app->fresh()->status);
        $this->assertEquals('Candidate prerequisites incomplete.', $app->fresh()->decision_remark);
    }

    public function test_student_can_confirm_accepted_offer(): void
    {
        $app = Application::create([
            'student_profile_id' => $this->studentProfile->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => ApplicationStatus::ACCEPTED,
        ]);

        $response = $this->actingAs($this->student)
            ->post(route('student.applications.confirm', $app));

        $response->assertRedirect();
        $this->assertNotNull($app->fresh()->student_confirmed_at);
    }

    public function test_student_can_decline_offer(): void
    {
        $app = Application::create([
            'student_profile_id' => $this->studentProfile->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => ApplicationStatus::ACCEPTED,
        ]);

        $response = $this->actingAs($this->student)
            ->post(route('student.applications.decline', $app), [
                'reason' => 'Accepted another local company.',
            ]);

        $response->assertRedirect(route('student.applications.index'));
        $this->assertEquals(ApplicationStatus::DECLINED, $app->fresh()->status);
        $this->assertEquals('Accepted another local company.', $app->fresh()->withdrawn_reason);
    }

    public function test_student_can_withdraw_pending_application(): void
    {
        $app = Application::create([
            'student_profile_id' => $this->studentProfile->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => ApplicationStatus::PENDING,
        ]);

        $response = $this->actingAs($this->student)
            ->post(route('student.applications.withdraw', $app), [
                'reason' => 'Changed career preference.',
            ]);

        $response->assertRedirect(route('student.applications.index'));
        $this->assertEquals(ApplicationStatus::WITHDRAWN, $app->fresh()->status);
        $this->assertEquals('Changed career preference.', $app->fresh()->withdrawn_reason);
    }

    public function test_partner_company_can_view_own_applicants_and_decide(): void
    {
        $app = Application::create([
            'student_profile_id' => $this->studentProfile->id,
            'opportunity_id' => $this->opportunity->id,
            'status' => ApplicationStatus::PENDING,
        ]);

        // Partner company views applicant queue
        $response = $this->actingAs($this->pcUser)
            ->get(route('company.applications.index'));

        $response->assertOk();
        $response->assertSee($this->student->name);

        // Partner company extends offer
        $decisionResponse = $this->actingAs($this->pcUser)
            ->post(route('company.applications.decide', $app), [
                'decision' => 'accepted',
                'remark' => 'Welcome to the team!',
            ]);

        $decisionResponse->assertRedirect();
        $this->assertEquals(ApplicationStatus::ACCEPTED, $app->fresh()->status);
    }
}
