<?php

namespace Tests\Feature\Companies;

use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\VerificationRequirementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyPromotionRuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $coordinator;
    protected User $student;
    protected User $coCoordinator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(VerificationRequirementSeeder::class);

        $this->coordinator = User::factory()->create([
            'role' => UserRole::OC,
            'status' => UserStatus::ACTIVE,
            'must_change_password' => false,
        ]);

        $this->coCoordinator = User::factory()->create([
            'role' => UserRole::COC,
            'status' => UserStatus::ACTIVE,
            'must_change_password' => false,
        ]);

        $this->student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'must_change_password' => false,
        ]);
    }

    public function test_unverified_new_company_cannot_be_promoted_to_existing_partner(): void
    {
        // Unverified / Pending company
        $company = Company::create([
            'name' => 'Pending Tech Inc',
            'classification' => CompanyClassification::NEW_COMPANY,
            'verification_status' => CompanyVerificationStatus::PENDING,
            'is_active' => true,
        ]);

        $this->assertFalse($company->canBePromoted());

        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.companies.promote', $company));

        $response->assertSessionHas('error');

        $company->refresh();
        $this->assertEquals(CompanyClassification::NEW_COMPANY, $company->classification);
        $this->assertNull($company->promoted_by);
        $this->assertNull($company->promoted_at);
    }

    public function test_verified_new_company_can_be_promoted_by_coordinator(): void
    {
        // Verified New Company
        $company = Company::create([
            'name' => 'Fully Verified Innovations',
            'classification' => CompanyClassification::NEW_COMPANY,
            'verification_status' => CompanyVerificationStatus::VERIFIED,
            'is_active' => true,
        ]);

        $this->assertTrue($company->canBePromoted());

        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.companies.promote', $company));

        $response->assertSessionHas('status');

        $company->refresh();
        $this->assertEquals(CompanyClassification::EXISTING_PARTNER, $company->classification);
        $this->assertEquals($this->coordinator->id, $company->promoted_by);
        $this->assertNotNull($company->promoted_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'company.promoted',
            'subject_type' => Company::class,
            'subject_id' => $company->id,
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_existing_partner_cannot_be_promoted_again(): void
    {
        $company = Company::create([
            'name' => 'Already Partner Ltd',
            'classification' => CompanyClassification::EXISTING_PARTNER,
            'verification_status' => CompanyVerificationStatus::VERIFIED,
            'is_active' => true,
        ]);

        $this->assertFalse($company->canBePromoted());

        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.companies.promote', $company));

        $response->assertSessionHas('error');
    }

    public function test_non_coordinator_cannot_promote_company(): void
    {
        $company = Company::create([
            'name' => 'Ready For Promotion Inc',
            'classification' => CompanyClassification::NEW_COMPANY,
            'verification_status' => CompanyVerificationStatus::VERIFIED,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->student)
            ->post(route('coordinator.companies.promote', $company));

        $response->assertStatus(403);

        $company->refresh();
        $this->assertEquals(CompanyClassification::NEW_COMPANY, $company->classification);
    }
}
