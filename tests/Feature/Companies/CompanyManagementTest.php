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

class CompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $coordinator;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(VerificationRequirementSeeder::class);

        $this->coordinator = User::factory()->create([
            'role' => UserRole::OC,
            'status' => UserStatus::ACTIVE,
            'must_change_password' => false,
        ]);

        $this->student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'must_change_password' => false,
        ]);
    }

    public function test_coordinator_can_view_companies_index_with_filters(): void
    {
        Company::create([
            'name' => 'Bulacan Cloud Systems',
            'classification' => CompanyClassification::EXISTING_PARTNER,
            'verification_status' => CompanyVerificationStatus::VERIFIED,
            'is_active' => true,
            'industry' => 'Cloud Computing',
            'city' => 'Malolos',
        ]);

        Company::create([
            'name' => 'Metro Web Works',
            'classification' => CompanyClassification::NEW_COMPANY,
            'verification_status' => CompanyVerificationStatus::PENDING,
            'is_active' => true,
            'industry' => 'Web Development',
            'city' => 'Quezon City',
        ]);

        // Access directory without filters
        $response = $this->actingAs($this->coordinator)->get(route('coordinator.companies.index'));
        $response->assertStatus(200);
        $response->assertSee('Bulacan Cloud Systems');
        $response->assertSee('Metro Web Works');

        // Filter by keyword
        $filterResponse = $this->actingAs($this->coordinator)
            ->get(route('coordinator.companies.index', ['search' => 'Bulacan']));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Bulacan Cloud Systems');
        $filterResponse->assertDontSee('Metro Web Works');

        // Filter by classification
        $classResponse = $this->actingAs($this->coordinator)
            ->get(route('coordinator.companies.index', ['classification' => 'new_company']));
        $classResponse->assertStatus(200);
        $classResponse->assertSee('Metro Web Works');
        $classResponse->assertDontSee('Bulacan Cloud Systems');
    }

    public function test_coordinator_can_create_company_record_and_auto_populates_checklist(): void
    {
        $response = $this->actingAs($this->coordinator)->post(route('coordinator.companies.store'), [
            'name' => 'AgriTech Analytics Corp',
            'industry' => 'Data Analytics',
            'address' => 'Poblacion, San Ildefonso',
            'city' => 'San Ildefonso',
            'contact_person' => 'Juan Dela Cruz',
            'contact_email' => 'contact@agritech.ph',
            'contact_phone' => '+639181234567',
            'website' => 'https://agritech.ph',
            'description' => 'Agricultural big data and IoT sensor systems provider.',
            'bsit_relevance_note' => 'Aligns with database administration and data mining subjects.',
            'classification' => CompanyClassification::NEW_COMPANY->value,
        ]);

        $company = Company::where('name', 'AgriTech Analytics Corp')->first();
        $this->assertNotNull($company);
        $response->assertRedirect(route('coordinator.companies.show', $company));

        // Company classification and initial status
        $this->assertEquals(CompanyClassification::NEW_COMPANY, $company->classification);
        $this->assertEquals(CompanyVerificationStatus::PENDING, $company->verification_status);
        $this->assertTrue($company->is_active);

        // Auto-seeded checklist items (5 requirements seeded)
        $this->assertEquals(5, $company->verificationItems()->count());

        // Audit log created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'company.created',
            'subject_type' => Company::class,
            'subject_id' => $company->id,
            'actor_id' => $this->coordinator->id,
        ]);

        // Verification log created
        $this->assertDatabaseHas('company_verification_logs', [
            'company_id' => $company->id,
            'to_status' => CompanyVerificationStatus::PENDING->value,
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_non_coordinator_cannot_create_company(): void
    {
        $response = $this->actingAs($this->student)->post(route('coordinator.companies.store'), [
            'name' => 'Hacker Company Ltd',
            'industry' => 'Cybersecurity',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('companies', ['name' => 'Hacker Company Ltd']);
    }

    public function test_company_name_must_be_unique(): void
    {
        Company::create([
            'name' => 'Unique Tech Solutions',
            'classification' => CompanyClassification::NEW_COMPANY,
            'verification_status' => CompanyVerificationStatus::PENDING,
        ]);

        $response = $this->actingAs($this->coordinator)->post(route('coordinator.companies.store'), [
            'name' => 'Unique Tech Solutions',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_coordinator_can_update_company_profile(): void
    {
        $company = Company::create([
            'name' => 'Old Name Corp',
            'industry' => 'Old Industry',
            'classification' => CompanyClassification::NEW_COMPANY,
            'verification_status' => CompanyVerificationStatus::PENDING,
        ]);

        $response = $this->actingAs($this->coordinator)->put(route('coordinator.companies.update', $company), [
            'name' => 'Updated Name Corp',
            'industry' => 'New Enterprise Industry',
            'address' => 'Updated Address',
            'city' => 'Updated City',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('coordinator.companies.show', $company));

        $company->refresh();
        $this->assertEquals('Updated Name Corp', $company->name);
        $this->assertEquals('New Enterprise Industry', $company->industry);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'company.updated',
            'subject_type' => Company::class,
            'subject_id' => $company->id,
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_coordinator_can_soft_delete_company(): void
    {
        $company = Company::create([
            'name' => 'Archivable Company Inc',
            'classification' => CompanyClassification::NEW_COMPANY,
            'verification_status' => CompanyVerificationStatus::PENDING,
        ]);

        $response = $this->actingAs($this->coordinator)->delete(route('coordinator.companies.destroy', $company));
        $response->assertRedirect(route('coordinator.companies.index'));

        $this->assertSoftDeleted('companies', ['id' => $company->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'company.archived',
            'subject_type' => Company::class,
            'subject_id' => $company->id,
            'actor_id' => $this->coordinator->id,
        ]);
    }
}
