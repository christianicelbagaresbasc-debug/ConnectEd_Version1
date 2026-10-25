<?php

namespace Tests\Feature\Companies;

use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationItemStatus;
use App\Models\Company;
use App\Models\CompanyVerificationItem;
use App\Models\User;
use App\Models\VerificationRequirement;
use Database\Seeders\VerificationRequirementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $coordinator;

    protected User $student;

    protected Company $company;

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

        $this->company = Company::create([
            'name' => 'Bulacan Software Labs',
            'classification' => CompanyClassification::NEW_COMPANY,
            'verification_status' => CompanyVerificationStatus::PENDING,
            'is_active' => true,
        ]);

        // Auto-seed checklist items for the company
        foreach (VerificationRequirement::all() as $req) {
            CompanyVerificationItem::create([
                'company_id' => $this->company->id,
                'verification_requirement_id' => $req->id,
                'status' => VerificationItemStatus::MISSING,
            ]);
        }
    }

    public function test_coordinator_can_view_verification_checklist_page(): void
    {
        $response = $this->actingAs($this->coordinator)
            ->get(route('coordinator.companies.verification', $this->company));

        $response->assertStatus(200);
        $response->assertSee('6-Point Company Verification Checklist');
        $response->assertSee('Bulacan Software Labs');
        $response->assertSee('Proof of Business Registration');
    }

    public function test_coordinator_can_review_and_verify_checklist_item(): void
    {
        $item = $this->company->verificationItems()->first();
        $this->assertNotNull($item);

        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.companies.verification.items.update', [$this->company, $item]), [
                'status' => VerificationItemStatus::ACCEPTED->value,
                'remark' => 'Validated SEC certificate on official portal.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $item->refresh();
        $this->assertEquals(VerificationItemStatus::ACCEPTED, $item->status);
        $this->assertEquals('Validated SEC certificate on official portal.', $item->remark);
        $this->assertEquals($this->coordinator->id, $item->reviewed_by);
        $this->assertNotNull($item->reviewed_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'company_verification_item.reviewed',
            'subject_type' => Company::class,
            'subject_id' => $this->company->id,
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_coordinator_can_transition_overall_verification_status(): void
    {
        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.companies.verification.status.update', $this->company), [
                'verification_status' => CompanyVerificationStatus::VERIFIED->value,
                'remark' => 'All mandatory statutory documents submitted, verified, and confirmed authentic.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->company->refresh();
        $this->assertEquals(CompanyVerificationStatus::VERIFIED, $this->company->verification_status);

        // Verification log must be recorded
        $this->assertDatabaseHas('company_verification_logs', [
            'company_id' => $this->company->id,
            'from_status' => CompanyVerificationStatus::PENDING->value,
            'to_status' => CompanyVerificationStatus::VERIFIED->value,
            'remark' => 'All mandatory statutory documents submitted, verified, and confirmed authentic.',
            'actor_id' => $this->coordinator->id,
        ]);

        // Audit log must also be recorded
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'company.verification_status_changed',
            'subject_type' => Company::class,
            'subject_id' => $this->company->id,
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_overall_status_update_requires_a_minimum_remark(): void
    {
        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.companies.verification.status.update', $this->company), [
                'verification_status' => CompanyVerificationStatus::REJECTED->value,
                'remark' => 'No', // Too short (min:5)
            ]);

        $response->assertSessionHasErrors('remark');
    }

    public function test_non_coordinator_cannot_update_verification(): void
    {
        $item = $this->company->verificationItems()->first();

        $itemResponse = $this->actingAs($this->student)
            ->post(route('coordinator.companies.verification.items.update', [$this->company, $item]), [
                'status' => VerificationItemStatus::ACCEPTED->value,
            ]);
        $itemResponse->assertStatus(403);

        $statusResponse = $this->actingAs($this->student)
            ->post(route('coordinator.companies.verification.status.update', $this->company), [
                'verification_status' => CompanyVerificationStatus::VERIFIED->value,
                'remark' => 'Unauthorized student attempt.',
            ]);
        $statusResponse->assertStatus(403);
    }
}
