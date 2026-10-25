<?php

namespace Tests\Feature\Dashboards;

use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\MoaCustodianParty;
use App\Enums\MoaStatus;
use App\Enums\MoaType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Moa;
use App\Models\PartnerAccount;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_oc_dashboard_renders_unplaced_students_widget_and_attendance_scope_card(): void
    {
        $oc = User::factory()->create([
            'role' => UserRole::OC,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $profile = StudentProfile::create([
            'user_id' => $student->id,
            'student_number' => 'BSAU-2026-001',
            'program' => 'BSIT',
            'year_level' => '4',
            'has_active_placement' => false,
        ]);

        $response = $this->actingAs($oc)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('OJT Coordinator Command Center');
        $response->assertSee('Students Without a Company');
        $response->assertSee('BSAU-2026-001');
        $response->assertSee('DTR & Attendance Tracking', false);
        $response->assertSee('Not in current scope');
    }

    public function test_coc_dashboard_displays_assigned_students_and_tasks(): void
    {
        $coc = User::factory()->create([
            'role' => UserRole::COC,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $profile = StudentProfile::create([
            'user_id' => $student->id,
            'student_number' => 'BSAU-2026-002',
            'program' => 'BSIT',
            'year_level' => '4',
            'assigned_coc_id' => $coc->id,
            'has_active_placement' => false,
        ]);

        $response = $this->actingAs($coc)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Co-Coordinator Workspace');
        $response->assertSee('My Supervised Student Cohort');
        $response->assertSee('BSAU-2026-002');
    }

    public function test_ua_dashboard_displays_moas_under_legal_review(): void
    {
        $ua = User::factory()->create([
            'role' => UserRole::UA,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $company = Company::create([
            'name' => 'Legal Partner Tech',
            'classification' => CompanyClassification::NEW_COMPANY,
            'verification_status' => CompanyVerificationStatus::UNDER_REVIEW,
            'is_active' => true,
        ]);

        $moa = Moa::create([
            'company_id' => $company->id,
            'status' => MoaStatus::UNDER_LEGAL_REVIEW,
            'current_custodian_party' => MoaCustodianParty::UA,
            'type' => MoaType::UNIVERSITY_COMPANY,
            'school_year' => '2026-2027',
            'created_by' => $ua->id,
        ]);

        $response = $this->actingAs($ua)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('University Attorney Legal Console');
        $response->assertSee('MOAs Awaiting Legal Vetting');
        $response->assertSee('Legal Partner Tech');
    }

    public function test_pc_dashboard_displays_company_portal_without_student_rankings(): void
    {
        $pcUser = User::factory()->create([
            'role' => UserRole::PC,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $company = Company::create([
            'name' => 'Host Enterprise Inc',
            'classification' => CompanyClassification::EXISTING_PARTNER,
            'verification_status' => CompanyVerificationStatus::VERIFIED,
            'is_active' => true,
        ]);

        PartnerAccount::create([
            'user_id' => $pcUser->id,
            'company_id' => $company->id,
            'can_post_opportunities' => true,
            'can_view_applicants' => true,
        ]);

        $response = $this->actingAs($pcUser)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Host Enterprise Inc Portal');
        $response->assertSee('Institutional Rule BR-20');
        $response->assertDontSee('Best-Fit Score');
        $response->assertDontSee('Rank #');
    }

    public function test_student_dashboard_displays_profile_completeness_and_placement_or_recommendations(): void
    {
        $student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $profile = StudentProfile::create([
            'user_id' => $student->id,
            'student_number' => 'BSAU-2026-003',
            'program' => 'BSIT',
            'year_level' => '4',
            'contact_number' => '09123456789',
            'address' => 'Magalang, Pampanga',
            'has_active_placement' => false,
            'consented_at' => now(),
        ]);

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Student Intern Hub');
        $response->assertSee('Profile Readiness & Verification', false);
        $response->assertSee('Seeking OJT Placement');
    }
}
