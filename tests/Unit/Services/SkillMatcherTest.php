<?php

namespace Tests\Unit\Services;

use App\Enums\OpportunityRequirementLevel;
use App\Enums\OpportunityStatus;
use App\Enums\SkillStatus;
use App\Models\Company;
use App\Models\Opportunity;
use App\Models\OpportunitySkill;
use App\Models\Skill;
use App\Models\User;
use App\Services\SkillMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillMatcherTest extends TestCase
{
    use RefreshDatabase;

    protected SkillMatcher $matcher;

    protected User $user;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matcher = new SkillMatcher;

        $this->user = User::factory()->create();

        $this->company = Company::create([
            'name' => 'Tech Solutions Corp',
            'classification' => 'existing_partner',
            'verification_status' => 'verified',
            'is_active' => true,
        ]);
    }

    protected function createOpportunity(): Opportunity
    {
        return Opportunity::create([
            'company_id' => $this->company->id,
            'title' => 'Software Engineer Intern',
            'description' => 'Internship position',
            'slots_total' => 2,
            'status' => OpportunityStatus::OPEN,
            'created_by' => $this->user->id,
            'school_year' => '2026-2027',
        ]);
    }

    public function test_all_skills_matched_gives_100_percent_strong_match(): void
    {
        $opp = $this->createOpportunity();

        $php = Skill::create(['name' => 'PHP', 'category' => 'Programming', 'status' => SkillStatus::ACTIVE]);
        $laravel = Skill::create(['name' => 'Laravel', 'category' => 'Web Development', 'status' => SkillStatus::ACTIVE]);
        $git = Skill::create(['name' => 'Git', 'category' => 'Tools', 'status' => SkillStatus::ACTIVE]);

        OpportunitySkill::create([
            'opportunity_id' => $opp->id,
            'skill_id' => $php->id,
            'requirement_level' => OpportunityRequirementLevel::REQUIRED,
        ]);
        OpportunitySkill::create([
            'opportunity_id' => $opp->id,
            'skill_id' => $laravel->id,
            'requirement_level' => OpportunityRequirementLevel::REQUIRED,
        ]);
        OpportunitySkill::create([
            'opportunity_id' => $opp->id,
            'skill_id' => $git->id,
            'requirement_level' => OpportunityRequirementLevel::PREFERRED,
        ]);

        $result = $this->matcher->match([$php->id, $laravel->id, $git->id], $opp);

        $this->assertEquals(100, $result['score']);
        $this->assertEquals('Strong match', $result['label']);
        $this->assertEquals(3, $result['matched_skills']->count());
        $this->assertEquals(0, $result['missing_required']->count());
        $this->assertEquals(0, $result['missing_preferred']->count());
    }

    public function test_none_matched_gives_0_percent_low_match(): void
    {
        $opp = $this->createOpportunity();

        $react = Skill::create(['name' => 'React', 'category' => 'Frontend', 'status' => SkillStatus::ACTIVE]);
        $node = Skill::create(['name' => 'Node.js', 'category' => 'Backend', 'status' => SkillStatus::ACTIVE]);

        OpportunitySkill::create([
            'opportunity_id' => $opp->id,
            'skill_id' => $react->id,
            'requirement_level' => OpportunityRequirementLevel::REQUIRED,
        ]);
        OpportunitySkill::create([
            'opportunity_id' => $opp->id,
            'skill_id' => $node->id,
            'requirement_level' => OpportunityRequirementLevel::PREFERRED,
        ]);

        $python = Skill::create(['name' => 'Python', 'category' => 'Programming', 'status' => SkillStatus::ACTIVE]);

        $result = $this->matcher->match([$python->id], $opp);

        $this->assertEquals(0, $result['score']);
        $this->assertEquals('Low match', $result['label']);
        $this->assertEquals(0, $result['matched_skills']->count());
        $this->assertEquals(1, $result['missing_required']->count());
        $this->assertEquals(1, $result['missing_preferred']->count());
    }

    public function test_only_preferred_matched(): void
    {
        $opp = $this->createOpportunity();

        $csharp = Skill::create(['name' => 'C#', 'category' => 'Programming', 'status' => SkillStatus::ACTIVE]);
        $dotnet = Skill::create(['name' => '.NET', 'category' => 'Framework', 'status' => SkillStatus::ACTIVE]);
        $docker = Skill::create(['name' => 'Docker', 'category' => 'DevOps', 'status' => SkillStatus::ACTIVE]);

        // 2 Required (Weight = 2*2 = 4)
        OpportunitySkill::create([
            'opportunity_id' => $opp->id,
            'skill_id' => $csharp->id,
            'requirement_level' => OpportunityRequirementLevel::REQUIRED,
        ]);
        OpportunitySkill::create([
            'opportunity_id' => $opp->id,
            'skill_id' => $dotnet->id,
            'requirement_level' => OpportunityRequirementLevel::REQUIRED,
        ]);

        // 1 Preferred (Weight = 1*1 = 1)
        OpportunitySkill::create([
            'opportunity_id' => $opp->id,
            'skill_id' => $docker->id,
            'requirement_level' => OpportunityRequirementLevel::PREFERRED,
        ]);

        // Student has only Docker
        // matched = 1, total = (4 + 1) = 5 -> 1/5 = 20%
        $result = $this->matcher->match([$docker->id], $opp);

        $this->assertEquals(20, $result['score']);
        $this->assertEquals('Partial match', $result['label']);
        $this->assertEquals(1, $result['matched_skills']->count());
        $this->assertEquals(2, $result['missing_required']->count());
        $this->assertEquals(0, $result['missing_preferred']->count());
    }

    public function test_no_required_skills_only_preferred(): void
    {
        $opp = $this->createOpportunity();

        $html = Skill::create(['name' => 'HTML5', 'category' => 'Frontend', 'status' => SkillStatus::ACTIVE]);
        $css = Skill::create(['name' => 'CSS3', 'category' => 'Frontend', 'status' => SkillStatus::ACTIVE]);

        OpportunitySkill::create([
            'opportunity_id' => $opp->id,
            'skill_id' => $html->id,
            'requirement_level' => OpportunityRequirementLevel::PREFERRED,
        ]);
        OpportunitySkill::create([
            'opportunity_id' => $opp->id,
            'skill_id' => $css->id,
            'requirement_level' => OpportunityRequirementLevel::PREFERRED,
        ]);

        // Student has only HTML5: 1/2 = 50% -> Good match
        $result = $this->matcher->match([$html->id], $opp);

        $this->assertEquals(50, $result['score']);
        $this->assertEquals('Good match', $result['label']);
    }

    public function test_no_skills_at_all_on_opportunity_returns_null_score(): void
    {
        $opp = $this->createOpportunity();

        $result = $this->matcher->match([1, 2, 3], $opp);

        $this->assertNull($result['score']);
        $this->assertEquals('No skill data', $result['label']);
    }

    public function test_duplicate_skill_ids_in_student_input_are_deduplicated(): void
    {
        $opp = $this->createOpportunity();

        $java = Skill::create(['name' => 'Java', 'category' => 'Programming', 'status' => SkillStatus::ACTIVE]);
        OpportunitySkill::create([
            'opportunity_id' => $opp->id,
            'skill_id' => $java->id,
            'requirement_level' => OpportunityRequirementLevel::REQUIRED,
        ]);

        // Student passes duplicate IDs
        $result = $this->matcher->match([$java->id, $java->id, $java->id], $opp);

        $this->assertEquals(100, $result['score']);
        $this->assertEquals(1, $result['matched_skills']->count());
    }

    public function test_inactive_and_archived_skills_are_ignored(): void
    {
        $opp = $this->createOpportunity();

        $activeSkill = Skill::create(['name' => 'SQL', 'category' => 'Database', 'status' => SkillStatus::ACTIVE]);
        $inactiveSkill = Skill::create(['name' => 'Legacy COBOL', 'category' => 'Legacy', 'status' => SkillStatus::INACTIVE]);

        OpportunitySkill::create([
            'opportunity_id' => $opp->id,
            'skill_id' => $activeSkill->id,
            'requirement_level' => OpportunityRequirementLevel::REQUIRED,
        ]);
        OpportunitySkill::create([
            'opportunity_id' => $opp->id,
            'skill_id' => $inactiveSkill->id,
            'requirement_level' => OpportunityRequirementLevel::REQUIRED,
        ]);

        // Student has SQL: only active skill counts, denominator = 2, score = 2/2 = 100%
        $result = $this->matcher->match([$activeSkill->id], $opp);

        $this->assertEquals(100, $result['score']);
        $this->assertEquals(1, $result['total_required_count']);
    }
}
