<?php

namespace Tests\Feature\Reports;

use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\PlacementStatus;
use App\Enums\SkillStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Placement;
use App\Models\Skill;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $oc;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_coordinator_can_access_reports_hub(): void
    {
        $response = $this->actingAs($this->oc)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Institutional OJT Reports Hub');
        $response->assertSee('Placements Summary Report');
        $response->assertSee('Company Accreditation Report');
        $response->assertSee('Skill Distribution & Demand', false);
    }

    public function test_student_cannot_access_reports(): void
    {
        $response = $this->actingAs($this->student)->get(route('reports.index'));
        $response->assertForbidden();

        $response = $this->actingAs($this->student)->get(route('reports.placements.csv'));
        $response->assertForbidden();
    }

    public function test_placements_report_and_csv_export(): void
    {
        $company = Company::create([
            'name' => 'Report Test Tech Corp',
            'classification' => CompanyClassification::EXISTING_PARTNER,
            'verification_status' => CompanyVerificationStatus::VERIFIED,
            'is_active' => true,
        ]);

        $profile = StudentProfile::create([
            'user_id' => $this->student->id,
            'student_number' => 'BSAU-2026-REP-1',
            'program' => 'BSIT',
            'year_level' => '4',
            'has_active_placement' => true,
        ]);

        $placement = Placement::create([
            'student_profile_id' => $profile->id,
            'company_id' => $company->id,
            'status' => PlacementStatus::ONGOING,
            'is_active' => true,
            'confirmed_by' => $this->oc->id,
            'school_year' => '2026-2027',
        ]);

        $response = $this->actingAs($this->oc)->get(route('reports.placements'));
        $response->assertOk();
        $response->assertSee('Report Test Tech Corp');
        $response->assertSee('BSAU-2026-REP-1');

        $csvResponse = $this->actingAs($this->oc)->get(route('reports.placements.csv'));
        $csvResponse->assertOk();
        $csvResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $csvResponse->streamedContent();
        $this->assertStringContainsString('"Student Number","Student Name"', $content);
        $this->assertStringContainsString('BSAU-2026-REP-1', $content);
        $this->assertStringContainsString('Report Test Tech Corp', $content);
    }

    public function test_companies_report_and_csv_export(): void
    {
        $company = Company::create([
            'name' => 'Alpha Industrial Inc',
            'classification' => CompanyClassification::EXISTING_PARTNER,
            'verification_status' => CompanyVerificationStatus::VERIFIED,
            'industry_sector' => 'Information Technology',
            'contact_person' => 'Jane Smith',
            'contact_email' => 'jane@alphaindustrial.ph',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->oc)->get(route('reports.companies'));
        $response->assertOk();
        $response->assertSee('Alpha Industrial Inc');

        $csvResponse = $this->actingAs($this->oc)->get(route('reports.companies.csv'));
        $csvResponse->assertOk();
        $csvResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $csvResponse->streamedContent();
        $this->assertStringContainsString('"Company Name",Classification,"Verification Status"', $content);
        $this->assertStringContainsString('Alpha Industrial Inc', $content);
    }

    public function test_skills_report_and_csv_export(): void
    {
        $skill = Skill::create([
            'name' => 'Laravel Framework',
            'category' => 'Backend Development',
            'status' => SkillStatus::ACTIVE,
            'created_by' => $this->oc->id,
        ]);

        $response = $this->actingAs($this->oc)->get(route('reports.skills'));
        $response->assertOk();
        $response->assertSee('Laravel Framework');

        $csvResponse = $this->actingAs($this->oc)->get(route('reports.skills.csv'));
        $csvResponse->assertOk();
        $csvResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $csvResponse->streamedContent();
        $this->assertStringContainsString('"Skill Name",Category,Status', $content);
        $this->assertStringContainsString('Laravel Framework', $content);
    }
}
