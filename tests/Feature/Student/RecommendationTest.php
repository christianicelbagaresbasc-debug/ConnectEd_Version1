<?php

namespace Tests\Feature\Student;

use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\OpportunityRequirementLevel;
use App\Enums\OpportunityStatus;
use App\Enums\OpportunityWorkArrangement;
use App\Enums\SkillStatus;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Opportunity;
use App\Models\OpportunitySkill;
use App\Models\Skill;
use App\Models\StudentProfile;
use App\Models\StudentSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected StudentProfile $studentProfile;

    protected Company $verifiedCompany;

    protected Skill $laravelSkill;

    protected Skill $vueSkill;

    protected Skill $dockerSkill;

    protected Opportunity $opportunity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => 'active',
            'must_change_password' => false,
        ]);
        $this->studentProfile = StudentProfile::create([
            'user_id' => $this->student->id,
            'student_number' => '2023-0001',
            'program' => 'BSIT',
            'year_level' => '3rd Year',
            'consented_at' => now(),
            'privacy_notice_version' => '1.0',
        ]);

        $this->verifiedCompany = Company::create([
            'name' => 'San Ildefonso IT Solutions',
            'classification' => CompanyClassification::EXISTING_PARTNER,
            'verification_status' => CompanyVerificationStatus::VERIFIED,
            'is_active' => true,
        ]);

        $this->laravelSkill = Skill::create(['name' => 'Laravel', 'category' => 'Web Development', 'status' => SkillStatus::ACTIVE]);
        $this->vueSkill = Skill::create(['name' => 'Vue.js', 'category' => 'Web Development', 'status' => SkillStatus::ACTIVE]);
        $this->dockerSkill = Skill::create(['name' => 'Docker', 'category' => 'DevOps', 'status' => SkillStatus::ACTIVE]);

        $creator = User::factory()->create(['role' => UserRole::OC]);
        $this->opportunity = Opportunity::create([
            'company_id' => $this->verifiedCompany->id,
            'title' => 'Full-Stack Web Intern',
            'description' => 'Build web applications using modern stack.',
            'slots_total' => 3,
            'work_arrangement' => OpportunityWorkArrangement::HYBRID,
            'status' => OpportunityStatus::OPEN,
            'created_by' => $creator->id,
            'school_year' => '2026-2027',
        ]);

        // 2 Required Skills (Laravel, Vue) -> total_required = 2
        // 1 Preferred Skill (Docker) -> total_preferred = 1
        // Total Weighted = 2*2 + 1*1 = 5
        OpportunitySkill::create([
            'opportunity_id' => $this->opportunity->id,
            'skill_id' => $this->laravelSkill->id,
            'requirement_level' => OpportunityRequirementLevel::REQUIRED,
        ]);
        OpportunitySkill::create([
            'opportunity_id' => $this->opportunity->id,
            'skill_id' => $this->vueSkill->id,
            'requirement_level' => OpportunityRequirementLevel::REQUIRED,
        ]);
        OpportunitySkill::create([
            'opportunity_id' => $this->opportunity->id,
            'skill_id' => $this->dockerSkill->id,
            'requirement_level' => OpportunityRequirementLevel::PREFERRED,
        ]);
    }

    public function test_student_sees_accurate_match_score_and_reasoning_breakdown(): void
    {
        // Give student Laravel (required) and Docker (preferred), missing Vue (required)
        // matched_required = 1 (Laravel), matched_preferred = 1 (Docker)
        // weighted_matched = 2*1 + 1*1 = 3
        // weighted_total = 2*2 + 1*1 = 5
        // score = round(100 * 3 / 5) = 60% -> "Good match"
        StudentSkill::create([
            'student_profile_id' => $this->studentProfile->id,
            'skill_id' => $this->laravelSkill->id,
        ]);
        StudentSkill::create([
            'student_profile_id' => $this->studentProfile->id,
            'skill_id' => $this->dockerSkill->id,
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('student.recommendations.index'));

        $response->assertOk()
            ->assertViewIs('student.recommendations.index')
            ->assertSee('Full-Stack Web Intern')
            ->assertSee('60%')
            ->assertSee('Good match')
            ->assertSee('Matched Skills (2)')
            ->assertSee('Missing Required (1)')
            ->assertSee('Vue.js');
    }

    public function test_rule_br20_recommendations_route_is_strictly_forbidden_to_non_students(): void
    {
        $coordinator = User::factory()->create(['role' => UserRole::OC]);
        $pc = User::factory()->create(['role' => UserRole::PC]);
        $attorney = User::factory()->create(['role' => UserRole::UA]);

        $this->actingAs($coordinator)->get(route('student.recommendations.index'))->assertForbidden();
        $this->actingAs($pc)->get(route('student.recommendations.index'))->assertForbidden();
        $this->actingAs($attorney)->get(route('student.recommendations.index'))->assertForbidden();
    }

    public function test_rule_br20_no_controller_or_query_ranks_or_sorts_students_by_match_score(): void
    {
        // Scan all controller files to ensure match score is never in an orderBy or student list query
        $controllers = glob(app_path('Http/Controllers/**/*.php'));
        $violations = [];

        foreach ($controllers as $file) {
            $content = file_get_contents($file);
            if (preg_match('/orderBy.*score/i', $content) && ! str_contains($file, 'RecommendationController.php')) {
                $violations[] = basename($file);
            }
        }

        $this->assertEmpty(
            $violations,
            'Rule BR-20 violation: controllers must never order or rank students by match score: '.implode(', ', $violations)
        );
    }
}
