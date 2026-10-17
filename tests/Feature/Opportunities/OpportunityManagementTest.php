<?php

namespace Tests\Feature\Opportunities;

use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\OpportunityRequirementLevel;
use App\Enums\OpportunityStatus;
use App\Enums\OpportunityWorkArrangement;
use App\Enums\SkillStatus;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Opportunity;
use App\Models\PartnerAccount;
use App\Models\Skill;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpportunityManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $coordinator;
    protected User $student;
    protected User $pcUser;
    protected Company $verifiedCompany;
    protected Skill $laravelSkill;
    protected Skill $dockerSkill;

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
        StudentProfile::create([
            'user_id' => $this->student->id,
            'student_number' => '2023-0001',
            'program' => 'BSIT',
            'year_level' => '3rd Year',
            'consented_at' => now(),
            'privacy_notice_version' => '1.0',
        ]);

        $this->verifiedCompany = Company::create([
            'name' => 'Bulacan Tech Solutions',
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
            'company_id' => $this->verifiedCompany->id,
        ]);

        $this->laravelSkill = Skill::create(['name' => 'Laravel', 'category' => 'Web Development', 'status' => SkillStatus::ACTIVE]);
        $this->dockerSkill = Skill::create(['name' => 'Docker', 'category' => 'DevOps', 'status' => SkillStatus::ACTIVE]);
    }

    public function test_coordinator_can_view_opportunities_index(): void
    {
        Opportunity::create([
            'company_id' => $this->verifiedCompany->id,
            'title' => 'Software Engineer Intern',
            'description' => 'Internship position for BSIT students',
            'slots_total' => 3,
            'work_arrangement' => OpportunityWorkArrangement::HYBRID,
            'status' => OpportunityStatus::OPEN,
            'created_by' => $this->coordinator->id,
            'school_year' => '2026-2027',
        ]);

        $response = $this->actingAs($this->coordinator)
            ->get(route('opportunities.index'));

        $response->assertOk()
            ->assertViewIs('opportunities.index')
            ->assertSee('Software Engineer Intern')
            ->assertSee('Bulacan Tech Solutions');
    }

    public function test_coordinator_can_create_and_publish_opportunity_with_skills(): void
    {
        $response = $this->actingAs($this->coordinator)
            ->post(route('opportunities.store'), [
                'company_id' => $this->verifiedCompany->id,
                'title' => 'Backend Developer Intern',
                'description' => 'Work with Laravel and PostgreSQL on production systems.',
                'qualifications' => 'Must have taken Web Development course.',
                'slots_total' => 4,
                'work_arrangement' => 'remote',
                'deadline' => now()->addMonth()->toDateString(),
                'status' => 'open',
                'required_skills' => [$this->laravelSkill->id],
                'preferred_skills' => [$this->dockerSkill->id],
            ]);

        $opportunity = Opportunity::where('title', 'Backend Developer Intern')->first();
        $this->assertNotNull($opportunity);

        $response->assertRedirect(route('opportunities.show', $opportunity));

        $this->assertEquals(OpportunityStatus::OPEN, $opportunity->status);
        $this->assertEquals(4, $opportunity->slots_total);
        $this->assertEquals(1, $opportunity->requiredSkills()->count());
        $this->assertEquals(1, $opportunity->preferredSkills()->count());

        $this->assertDatabaseHas('opportunity_skills', [
            'opportunity_id' => $opportunity->id,
            'skill_id' => $this->laravelSkill->id,
            'requirement_level' => OpportunityRequirementLevel::REQUIRED->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'opportunity.created',
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_partner_company_posting_defaults_to_pending_approval_under_a5(): void
    {
        $response = $this->actingAs($this->pcUser)
            ->post(route('opportunities.store'), [
                'title' => 'Frontend React Intern',
                'description' => 'Help build client web interfaces.',
                'slots_total' => 2,
                'work_arrangement' => 'onsite',
                'deadline' => now()->addWeeks(2)->toDateString(),
                'required_skills' => [$this->laravelSkill->id],
            ]);

        $opportunity = Opportunity::where('title', 'Frontend React Intern')->first();
        $this->assertNotNull($opportunity);

        $response->assertRedirect(route('opportunities.show', $opportunity));

        // Must be pending approval per Assumption A5
        $this->assertEquals(OpportunityStatus::PENDING_APPROVAL, $opportunity->status);
        $this->assertEquals($this->verifiedCompany->id, $opportunity->company_id);
    }

    public function test_coordinator_can_approve_pending_opportunity(): void
    {
        $opportunity = Opportunity::create([
            'company_id' => $this->verifiedCompany->id,
            'title' => 'QA Intern',
            'description' => 'Automated test suite maintenance',
            'slots_total' => 2,
            'work_arrangement' => OpportunityWorkArrangement::REMOTE,
            'status' => OpportunityStatus::PENDING_APPROVAL,
            'created_by' => $this->pcUser->id,
            'school_year' => '2026-2027',
        ]);

        $response = $this->actingAs($this->coordinator)
            ->post(route('opportunities.approve', $opportunity));

        $response->assertRedirect(route('opportunities.show', $opportunity));

        $opportunity->refresh();
        $this->assertEquals(OpportunityStatus::OPEN, $opportunity->status);
        $this->assertEquals($this->coordinator->id, $opportunity->approved_by);
        $this->assertNotNull($opportunity->approved_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'opportunity.approved',
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_student_can_only_view_open_opportunities_from_verified_existing_partners(): void
    {
        // 1. Open verified partner opportunity -> visible
        $openOpp = Opportunity::create([
            'company_id' => $this->verifiedCompany->id,
            'title' => 'Open Verified Opportunity',
            'description' => 'Ready for applicants',
            'slots_total' => 2,
            'work_arrangement' => OpportunityWorkArrangement::HYBRID,
            'status' => OpportunityStatus::OPEN,
            'created_by' => $this->coordinator->id,
            'school_year' => '2026-2027',
        ]);

        // 2. Pending approval opportunity -> not visible to student
        $pendingOpp = Opportunity::create([
            'company_id' => $this->verifiedCompany->id,
            'title' => 'Pending Review Opportunity',
            'description' => 'Awaiting approval',
            'slots_total' => 1,
            'work_arrangement' => OpportunityWorkArrangement::ONSITE,
            'status' => OpportunityStatus::PENDING_APPROVAL,
            'created_by' => $this->pcUser->id,
            'school_year' => '2026-2027',
        ]);

        // 3. New unverified company opportunity -> not visible to student
        $newCompany = Company::create([
            'name' => 'Unverified Startup',
            'classification' => CompanyClassification::NEW_COMPANY,
            'verification_status' => CompanyVerificationStatus::PENDING,
            'is_active' => true,
        ]);
        $unverifiedOpp = Opportunity::create([
            'company_id' => $newCompany->id,
            'title' => 'Unverified Opportunity',
            'description' => 'From new company',
            'slots_total' => 1,
            'work_arrangement' => OpportunityWorkArrangement::REMOTE,
            'status' => OpportunityStatus::OPEN,
            'created_by' => $this->coordinator->id,
            'school_year' => '2026-2027',
        ]);

        $indexResponse = $this->actingAs($this->student)
            ->get(route('opportunities.index'));

        $indexResponse->assertOk()
            ->assertSee('Open Verified Opportunity')
            ->assertDontSee('Pending Review Opportunity')
            ->assertDontSee('Unverified Opportunity');

        // Direct show tests
        $this->actingAs($this->student)
            ->get(route('opportunities.show', $openOpp))
            ->assertOk();

        $this->actingAs($this->student)
            ->get(route('opportunities.show', $pendingOpp))
            ->assertForbidden();

        $this->actingAs($this->student)
            ->get(route('opportunities.show', $unverifiedOpp))
            ->assertForbidden();
    }

    public function test_coordinator_can_update_opportunity_and_sync_skills(): void
    {
        $opportunity = Opportunity::create([
            'company_id' => $this->verifiedCompany->id,
            'title' => 'Initial Title',
            'description' => 'Initial Description',
            'slots_total' => 2,
            'work_arrangement' => OpportunityWorkArrangement::ONSITE,
            'status' => OpportunityStatus::OPEN,
            'created_by' => $this->coordinator->id,
            'school_year' => '2026-2027',
        ]);

        $response = $this->actingAs($this->coordinator)
            ->put(route('opportunities.update', $opportunity), [
                'title' => 'Updated Title',
                'description' => 'Updated Description',
                'slots_total' => 5,
                'work_arrangement' => 'hybrid',
                'required_skills' => [$this->dockerSkill->id],
            ]);

        $response->assertRedirect(route('opportunities.show', $opportunity));

        $opportunity->refresh();
        $this->assertEquals('Updated Title', $opportunity->title);
        $this->assertEquals(5, $opportunity->slots_total);
        $this->assertEquals(OpportunityWorkArrangement::HYBRID, $opportunity->work_arrangement);
        $this->assertEquals(1, $opportunity->requiredSkills()->count());
        $this->assertEquals($this->dockerSkill->id, $opportunity->requiredSkills()->first()->id);
    }

    public function test_coordinator_can_soft_delete_opportunity(): void
    {
        $opportunity = Opportunity::create([
            'company_id' => $this->verifiedCompany->id,
            'title' => 'Deprecating Opportunity',
            'description' => 'Will be archived',
            'slots_total' => 1,
            'work_arrangement' => OpportunityWorkArrangement::ONSITE,
            'status' => OpportunityStatus::OPEN,
            'created_by' => $this->coordinator->id,
            'school_year' => '2026-2027',
        ]);

        $response = $this->actingAs($this->coordinator)
            ->delete(route('opportunities.destroy', $opportunity));

        $response->assertRedirect(route('opportunities.index'));

        $this->assertSoftDeleted('opportunities', ['id' => $opportunity->id]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'opportunity.archived',
            'actor_id' => $this->coordinator->id,
        ]);
    }
}
