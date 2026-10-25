<?php

namespace Tests\Feature\Security;

use App\Enums\ApplicationStatus;
use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\OpportunityStatus;
use App\Enums\PlacementStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Application;
use App\Models\Company;
use App\Models\Opportunity;
use App\Models\PartnerAccount;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_attached_to_responses(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->assertHeader('Content-Security-Policy');
    }

    public function test_custom_404_error_view_is_rendered(): void
    {
        $response = $this->get('/non-existent-endpoint-404-check');

        $response->assertNotFound();
        $response->assertSee('Page Not Found');
        $response->assertSee('404');
    }

    public function test_custom_403_error_view_is_rendered(): void
    {
        $student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($student)->get(route('reports.index'));

        $response->assertForbidden();
        $response->assertSee('Access Restricted');
        $response->assertSee('403');
    }

    public function test_idor_student_cannot_view_another_students_application(): void
    {
        $student1 = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);
        $profile1 = StudentProfile::create([
            'user_id' => $student1->id,
            'student_number' => 'BSAU-2026-IDOR-1',
            'program' => 'BSIT',
            'year_level' => '4',
        ]);

        $student2 = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);
        $profile2 = StudentProfile::create([
            'user_id' => $student2->id,
            'student_number' => 'BSAU-2026-IDOR-2',
            'program' => 'BSIT',
            'year_level' => '4',
        ]);

        $company = Company::create([
            'name' => 'IDOR Test Company',
            'classification' => CompanyClassification::EXISTING_PARTNER,
            'verification_status' => CompanyVerificationStatus::VERIFIED,
            'is_active' => true,
        ]);

        $opportunity = Opportunity::create([
            'company_id' => $company->id,
            'title' => 'Software Intern',
            'description' => 'Test software intern opportunity description',
            'status' => OpportunityStatus::OPEN,
            'slots_total' => 2,
            'created_by' => $student1->id,
        ]);

        $application2 = Application::create([
            'student_profile_id' => $profile2->id,
            'opportunity_id' => $opportunity->id,
            'status' => ApplicationStatus::PENDING,
        ]);

        // Student 1 attempts to view Student 2's application
        $response = $this->actingAs($student1)->get(route('student.applications.show', $application2));

        $response->assertForbidden();
    }

    public function test_idor_student_cannot_view_another_students_placement(): void
    {
        $student1 = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);
        StudentProfile::create([
            'user_id' => $student1->id,
            'student_number' => 'BSAU-2026-IDOR-3',
            'program' => 'BSIT',
            'year_level' => '4',
        ]);

        $student2 = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);
        $profile2 = StudentProfile::create([
            'user_id' => $student2->id,
            'student_number' => 'BSAU-2026-IDOR-4',
            'program' => 'BSIT',
            'year_level' => '4',
        ]);

        $company = Company::create([
            'name' => 'Placement Test Corp',
            'classification' => CompanyClassification::EXISTING_PARTNER,
            'verification_status' => CompanyVerificationStatus::VERIFIED,
            'is_active' => true,
        ]);

        $placement2 = Placement::create([
            'student_profile_id' => $profile2->id,
            'company_id' => $company->id,
            'status' => PlacementStatus::ONGOING,
            'is_active' => true,
            'confirmed_by' => $student2->id,
            'school_year' => '2026-2027',
        ]);

        // Student 1 attempts to view Student 2's placement
        $response = $this->actingAs($student1)->get(route('student.placements.show', $placement2));

        $response->assertForbidden();
    }

    public function test_idor_company_cannot_view_applications_for_another_companys_opportunity(): void
    {
        $company1 = Company::create([
            'name' => 'Company A',
            'classification' => CompanyClassification::EXISTING_PARTNER,
            'verification_status' => CompanyVerificationStatus::VERIFIED,
            'is_active' => true,
        ]);
        $pcUser1 = User::factory()->create([
            'role' => UserRole::PC,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);
        PartnerAccount::create([
            'user_id' => $pcUser1->id,
            'company_id' => $company1->id,
            'can_view_applicants' => true,
            'can_post_opportunities' => true,
        ]);

        $company2 = Company::create([
            'name' => 'Company B',
            'classification' => CompanyClassification::EXISTING_PARTNER,
            'verification_status' => CompanyVerificationStatus::VERIFIED,
            'is_active' => true,
        ]);
        $opp2 = Opportunity::create([
            'company_id' => $company2->id,
            'title' => 'QA Intern',
            'description' => 'Test QA intern opportunity description',
            'status' => OpportunityStatus::OPEN,
            'slots_total' => 2,
            'created_by' => $pcUser1->id,
        ]);

        $student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);
        $profile = StudentProfile::create([
            'user_id' => $student->id,
            'student_number' => 'BSAU-2026-IDOR-5',
            'program' => 'BSIT',
            'year_level' => '4',
        ]);

        $application2 = Application::create([
            'student_profile_id' => $profile->id,
            'opportunity_id' => $opp2->id,
            'status' => ApplicationStatus::PENDING,
        ]);

        // Company 1 user attempts to view Company 2's application
        $response = $this->actingAs($pcUser1)->get(route('company.applications.show', $application2));

        $response->assertForbidden();
    }
}
