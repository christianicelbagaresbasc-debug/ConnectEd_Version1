<?php

namespace Tests\Feature\Companies;

use App\Enums\CompanyClassification;
use App\Enums\CompanySource;
use App\Enums\CompanySuggestionStatus;
use App\Enums\CompanyVerificationStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\CompanySuggestion;
use App\Models\StudentProfile;
use App\Models\User;
use Database\Seeders\VerificationRequirementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySuggestionTest extends TestCase
{
    use RefreshDatabase;

    protected User $coordinator;
    protected User $studentUser;
    protected StudentProfile $studentProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(VerificationRequirementSeeder::class);

        $this->coordinator = User::factory()->create([
            'role' => UserRole::OC,
            'status' => UserStatus::ACTIVE,
            'must_change_password' => false,
        ]);

        $this->studentUser = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'must_change_password' => false,
        ]);

        $this->studentProfile = StudentProfile::create([
            'user_id' => $this->studentUser->id,
            'student_number' => '2023-10042',
            'program' => 'BS Information Technology',
            'year_level' => 4,
            'school_year' => '2026-2027',
        ]);
    }

    public function test_student_can_submit_company_suggestion(): void
    {
        $response = $this->actingAs($this->studentUser)->post(route('student.suggestions.store'), [
            'company_name' => 'NextGen Cloud Systems',
            'contact_details' => 'HR: Jane Doe, Email: hr@nextgen.ph',
            'notes' => 'Great cloud infrastructure lab, highly relevant to AWS and networking.',
        ]);

        $response->assertRedirect(route('student.suggestions.index'));
        $response->assertSessionHas('status');

        $suggestion = CompanySuggestion::where('company_name', 'NextGen Cloud Systems')->first();
        $this->assertNotNull($suggestion);
        $this->assertEquals($this->studentProfile->id, $suggestion->student_profile_id);
        $this->assertEquals(CompanySuggestionStatus::SUBMITTED, $suggestion->status);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'company_suggestion.submitted',
            'subject_type' => CompanySuggestion::class,
            'subject_id' => $suggestion->id,
            'actor_id' => $this->studentUser->id,
        ]);
    }

    public function test_student_can_view_own_suggestions(): void
    {
        CompanySuggestion::create([
            'student_profile_id' => $this->studentProfile->id,
            'company_name' => 'Student Suggested Corp',
            'status' => CompanySuggestionStatus::SUBMITTED,
        ]);

        $response = $this->actingAs($this->studentUser)->get(route('student.suggestions.index'));
        $response->assertStatus(200);
        $response->assertSee('Student Suggested Corp');
    }

    public function test_coordinator_can_view_suggestions_queue(): void
    {
        CompanySuggestion::create([
            'student_profile_id' => $this->studentProfile->id,
            'company_name' => 'Queue Company Ltd',
            'status' => CompanySuggestionStatus::SUBMITTED,
        ]);

        $response = $this->actingAs($this->coordinator)->get(route('coordinator.suggestions.index'));
        $response->assertStatus(200);
        $response->assertSee('Queue Company Ltd');
        $response->assertSee($this->studentUser->name);
    }

    public function test_coordinator_can_convert_suggestion_to_new_company(): void
    {
        $suggestion = CompanySuggestion::create([
            'student_profile_id' => $this->studentProfile->id,
            'company_name' => 'Smart Agri Tech Inc',
            'contact_details' => 'CEO: Mark Lopez',
            'notes' => 'Agricultural IoT systems in San Ildefonso.',
            'status' => CompanySuggestionStatus::SUBMITTED,
        ]);

        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.suggestions.convert', $suggestion), [
                'industry' => 'Agri-Tech & IoT',
                'city' => 'San Ildefonso',
                'address' => 'Barangay Pinaod',
                'contact_person' => 'Mark Lopez',
                'contact_email' => 'mark@smartagri.ph',
                'bsit_relevance_note' => 'Strong embedded systems and IoT backend alignment.',
            ]);

        $company = Company::where('name', 'Smart Agri Tech Inc')->first();
        $this->assertNotNull($company);
        $response->assertRedirect(route('coordinator.companies.show', $company));

        // Company attributes
        $this->assertEquals(CompanyClassification::NEW_COMPANY, $company->classification);
        $this->assertEquals(CompanyVerificationStatus::PENDING, $company->verification_status);
        $this->assertEquals(CompanySource::STUDENT_SUGGESTION, $company->source);
        $this->assertEquals(5, $company->verificationItems()->count());

        // Suggestion updated
        $suggestion->refresh();
        $this->assertEquals(CompanySuggestionStatus::CONVERTED, $suggestion->status);
        $this->assertEquals($company->id, $suggestion->company_id);
        $this->assertEquals($this->coordinator->id, $suggestion->handled_by);
        $this->assertNotNull($suggestion->handled_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'company_suggestion.converted',
            'subject_type' => CompanySuggestion::class,
            'subject_id' => $suggestion->id,
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_coordinator_can_dismiss_suggestion_with_reason(): void
    {
        $suggestion = CompanySuggestion::create([
            'student_profile_id' => $this->studentProfile->id,
            'company_name' => 'Ineligible Corp',
            'status' => CompanySuggestionStatus::SUBMITTED,
        ]);

        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.suggestions.dismiss', $suggestion), [
                'reason' => 'Company location is outside permitted regional internship zone.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $suggestion->refresh();
        $this->assertEquals(CompanySuggestionStatus::DISMISSED, $suggestion->status);
        $this->assertEquals($this->coordinator->id, $suggestion->handled_by);
        $this->assertStringContainsString('outside permitted regional internship zone', $suggestion->notes);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'company_suggestion.dismissed',
            'subject_type' => CompanySuggestion::class,
            'subject_id' => $suggestion->id,
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_student_directory_only_shows_verified_or_existing_partners(): void
    {
        // 1. Verified New Company (visible)
        $verifiedCompany = Company::create([
            'name' => 'Visible Verified Partner',
            'classification' => CompanyClassification::NEW_COMPANY,
            'verification_status' => CompanyVerificationStatus::VERIFIED,
            'is_active' => true,
        ]);

        // 2. Existing Partner (visible)
        $existingPartner = Company::create([
            'name' => 'Visible Existing Partner',
            'classification' => CompanyClassification::EXISTING_PARTNER,
            'verification_status' => CompanyVerificationStatus::PENDING,
            'is_active' => true,
        ]);

        // 3. Pending Unverified New Company (hidden from students)
        $hiddenCompany = Company::create([
            'name' => 'Hidden Pending Company',
            'classification' => CompanyClassification::NEW_COMPANY,
            'verification_status' => CompanyVerificationStatus::PENDING,
            'is_active' => true,
        ]);

        // 4. Rejected Company (hidden from students)
        $rejectedCompany = Company::create([
            'name' => 'Hidden Rejected Company',
            'classification' => CompanyClassification::NEW_COMPANY,
            'verification_status' => CompanyVerificationStatus::REJECTED,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->studentUser)->get(route('student.companies.index'));
        $response->assertStatus(200);

        $response->assertSee('Visible Verified Partner');
        $response->assertSee('Visible Existing Partner');
        $response->assertDontSee('Hidden Pending Company');
        $response->assertDontSee('Hidden Rejected Company');
    }
}
