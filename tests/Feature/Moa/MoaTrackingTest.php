<?php

namespace Tests\Feature\Moa;

use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\MoaCustodianParty;
use App\Enums\MoaStatus;
use App\Enums\MoaType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Document;
use App\Models\Moa;
use App\Models\MoaTransfer;
use App\Models\MoaVersion;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoaTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $coordinator;

    protected User $attorney;

    protected User $studentUser;

    protected StudentProfile $studentProfile;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coordinator = User::factory()->create([
            'role' => UserRole::OC,
            'status' => UserStatus::ACTIVE,
            'must_change_password' => false,
        ]);

        $this->attorney = User::factory()->create([
            'role' => UserRole::UA,
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
            'student_number' => '2023-ICS-0001',
            'program' => 'BS Information Technology',
            'year_level' => 4,
            'section' => '4A',
            'school_year' => '2026-2027',
            'has_active_placement' => false,
            'consented_at' => now(),
            'privacy_notice_version' => 'v1.0',
        ]);

        $this->company = Company::create([
            'name' => 'Acme Cloud Technologies',
            'classification' => CompanyClassification::EXISTING_PARTNER,
            'verification_status' => CompanyVerificationStatus::VERIFIED,
            'is_active' => true,
            'industry' => 'Cloud Solutions',
            'city' => 'Malolos, Bulacan',
        ]);
    }

    public function test_coordinator_can_view_moas_index_with_filters(): void
    {
        Moa::create([
            'moa_number' => 'MOA-2026-101',
            'company_id' => $this->company->id,
            'type' => MoaType::UNIVERSITY_COMPANY,
            'status' => MoaStatus::ACTIVE,
            'current_custodian_party' => MoaCustodianParty::OC,
            'summary' => 'Active University-wide Partnership Agreement',
            'created_by' => $this->coordinator->id,
        ]);

        Moa::create([
            'moa_number' => 'MOA-2026-102',
            'company_id' => $this->company->id,
            'type' => MoaType::STUDENT_COMPANY,
            'status' => MoaStatus::UNDER_LEGAL_REVIEW,
            'current_custodian_party' => MoaCustodianParty::UA,
            'summary' => 'Student Cloud Engineering Agreement',
            'created_by' => $this->coordinator->id,
        ]);

        // Access index without filters
        $response = $this->actingAs($this->coordinator)->get(route('moas.index'));
        $response->assertStatus(200);
        $response->assertSee('MOA-2026-101');
        $response->assertSee('MOA-2026-102');

        // Filter by keyword search
        $searchResponse = $this->actingAs($this->coordinator)
            ->get(route('moas.index', ['search' => '101']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('MOA-2026-101');
        $searchResponse->assertDontSee('MOA-2026-102');

        // Filter by status
        $statusResponse = $this->actingAs($this->coordinator)
            ->get(route('moas.index', ['status' => MoaStatus::UNDER_LEGAL_REVIEW->value]));
        $statusResponse->assertStatus(200);
        $statusResponse->assertSee('MOA-2026-102');
        $statusResponse->assertDontSee('MOA-2026-101');

        // Filter by custodian party
        $custodianResponse = $this->actingAs($this->coordinator)
            ->get(route('moas.index', ['custodian_party' => MoaCustodianParty::UA->value]));
        $custodianResponse->assertStatus(200);
        $custodianResponse->assertSee('MOA-2026-102');
        $custodianResponse->assertDontSee('MOA-2026-101');
    }

    public function test_coordinator_can_create_moa_with_initial_custody(): void
    {
        $response = $this->actingAs($this->coordinator)->post(route('moas.store'), [
            'moa_number' => 'MOA-2026-777',
            'company_id' => $this->company->id,
            'type' => MoaType::UNIVERSITY_COMPANY->value,
            'status' => MoaStatus::DRAFT->value,
            'current_custodian_party' => MoaCustodianParty::OC->value,
            'effective_date' => '2026-10-01',
            'expiry_date' => '2027-10-01',
            'summary' => 'New institutional cooperation agreement for ICS internship deployments.',
            'school_year' => '2026-2027',
        ]);

        $moa = Moa::where('moa_number', 'MOA-2026-777')->first();
        $this->assertNotNull($moa);
        $response->assertRedirect(route('moas.show', $moa));

        $this->assertEquals(MoaType::UNIVERSITY_COMPANY, $moa->type);
        $this->assertEquals(MoaStatus::DRAFT, $moa->status);
        $this->assertEquals(MoaCustodianParty::OC, $moa->current_custodian_party);
        $this->assertEquals($this->coordinator->id, $moa->current_custodian_user_id);

        // Verify initial custody transfer record
        $this->assertDatabaseHas('moa_transfers', [
            'moa_id' => $moa->id,
            'from_party' => null,
            'to_party' => MoaCustodianParty::OC->value,
            'actor_id' => $this->coordinator->id,
        ]);

        // Audit log created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'moa.created',
            'subject_type' => Moa::class,
            'subject_id' => $moa->id,
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_moa_number_must_be_unique(): void
    {
        Moa::create([
            'moa_number' => 'MOA-DUPLICATE-01',
            'company_id' => $this->company->id,
            'type' => MoaType::UNIVERSITY_COMPANY,
            'status' => MoaStatus::DRAFT,
            'current_custodian_party' => MoaCustodianParty::OC,
            'created_by' => $this->coordinator->id,
        ]);

        $response = $this->actingAs($this->coordinator)->post(route('moas.store'), [
            'moa_number' => 'MOA-DUPLICATE-01',
            'company_id' => $this->company->id,
            'type' => MoaType::UNIVERSITY_COMPANY->value,
            'status' => MoaStatus::DRAFT->value,
            'current_custodian_party' => MoaCustodianParty::OC->value,
        ]);

        $response->assertSessionHasErrors('moa_number');
    }

    /**
     * Rule BR-16: Unconstrained custody transfer flow
     * Can transfer custody between OC -> UA -> Company -> OC -> Student in any order.
     * Custody transfer logs are strictly append-only.
     */
    public function test_unconstrained_custody_transfer_flow_br16(): void
    {
        $moa = Moa::create([
            'moa_number' => 'MOA-BR16-FLOW',
            'company_id' => $this->company->id,
            'student_profile_id' => $this->studentProfile->id,
            'type' => MoaType::STUDENT_COMPANY,
            'status' => MoaStatus::DRAFT,
            'current_custodian_party' => MoaCustodianParty::OC,
            'current_custodian_user_id' => $this->coordinator->id,
            'created_by' => $this->coordinator->id,
        ]);

        // Step 1: OC transfers to UA for legal check
        $res1 = $this->actingAs($this->coordinator)
            ->post(route('moas.custody.transfer', $moa), [
                'to_party' => MoaCustodianParty::UA->value,
                'to_user_id' => $this->attorney->id,
                'note' => 'Dispatched physical draft to legal counsel for review.',
            ]);
        $res1->assertRedirect(route('moas.show', $moa));
        $moa->refresh();
        $this->assertEquals(MoaCustodianParty::UA, $moa->current_custodian_party);

        // Step 2: UA transfers to Partner Company for signature
        $res2 = $this->actingAs($this->attorney)
            ->post(route('moas.custody.transfer', $moa), [
                'to_party' => MoaCustodianParty::COMPANY->value,
                'note' => 'Transmitted document package to company HR representative.',
            ]);
        $res2->assertRedirect(route('moas.show', $moa));
        $moa->refresh();
        $this->assertEquals(MoaCustodianParty::COMPANY, $moa->current_custodian_party);

        // Step 3: Company returns signed copies to OC
        $res3 = $this->actingAs($this->coordinator)
            ->post(route('moas.custody.transfer', $moa), [
                'to_party' => MoaCustodianParty::OC->value,
                'to_user_id' => $this->coordinator->id,
                'note' => 'Received signed copies by courier from company.',
            ]);
        $res3->assertRedirect(route('moas.show', $moa));
        $moa->refresh();
        $this->assertEquals(MoaCustodianParty::OC, $moa->current_custodian_party);

        // Step 4: OC releases student copy to Student
        $res4 = $this->actingAs($this->coordinator)
            ->post(route('moas.custody.transfer', $moa), [
                'to_party' => MoaCustodianParty::STUDENT->value,
                'to_user_id' => $this->studentUser->id,
                'note' => 'Student copy released for personal records.',
            ]);
        $res4->assertRedirect(route('moas.show', $moa));
        $moa->refresh();
        $this->assertEquals(MoaCustodianParty::STUDENT, $moa->current_custodian_party);

        // Verify total of 4 transfers recorded in append-only table
        $this->assertEquals(4, $moa->transfers()->count());

        // Test append-only integrity (Rule BR-15)
        $latestTransfer = $moa->transfers()->first();
        $this->expectException(\RuntimeException::class);
        $latestTransfer->update(['note' => 'Tampered note']);
    }

    public function test_custody_transfer_cannot_be_deleted(): void
    {
        $moa = Moa::create([
            'moa_number' => 'MOA-DEL-TEST',
            'company_id' => $this->company->id,
            'type' => MoaType::UNIVERSITY_COMPANY,
            'status' => MoaStatus::DRAFT,
            'current_custodian_party' => MoaCustodianParty::OC,
            'created_by' => $this->coordinator->id,
        ]);

        $transfer = MoaTransfer::create([
            'moa_id' => $moa->id,
            'from_party' => MoaCustodianParty::OC,
            'to_party' => MoaCustodianParty::UA,
            'actor_id' => $this->coordinator->id,
            'note' => 'Test custody transfer record.',
            'created_at' => now(),
        ]);

        $this->expectException(\RuntimeException::class);
        $transfer->delete();
    }

    public function test_uploading_new_document_version_increments_version_number(): void
    {
        $moa = Moa::create([
            'moa_number' => 'MOA-VER-TEST',
            'company_id' => $this->company->id,
            'type' => MoaType::UNIVERSITY_COMPANY,
            'status' => MoaStatus::DRAFT,
            'current_custodian_party' => MoaCustodianParty::OC,
            'created_by' => $this->coordinator->id,
        ]);

        $doc1 = Document::create([
            'owner_user_id' => $this->coordinator->id,
            'category' => 'moa',
            'original_name' => 'Draft-v1.pdf',
            'storage_key' => 'draft-v1-key',
            'stored_path' => 'moas/draft-v1.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'validation_status' => 'ok',
            'uploaded_by' => $this->coordinator->id,
            'school_year' => '2026-2027',
        ]);

        $doc2 = Document::create([
            'owner_user_id' => $this->coordinator->id,
            'category' => 'moa',
            'original_name' => 'Revision-v2.pdf',
            'storage_key' => 'revision-v2-key',
            'stored_path' => 'moas/revision-v2.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 2048,
            'validation_status' => 'ok',
            'uploaded_by' => $this->coordinator->id,
            'school_year' => '2026-2027',
        ]);

        // Attach initial version
        MoaVersion::create([
            'moa_id' => $moa->id,
            'version_no' => 1,
            'document_id' => $doc1->id,
            'uploaded_by' => $this->coordinator->id,
            'note' => 'Version 1 uploaded',
        ]);

        // Post new version via controller
        $response = $this->actingAs($this->coordinator)
            ->post(route('moas.versions.store', $moa), [
                'document_id' => $doc2->id,
                'note' => 'Added amended liability clauses per UA recommendation.',
            ]);

        $response->assertRedirect(route('moas.show', $moa));

        $moa->refresh();
        $this->assertEquals(2, $moa->versions()->count());
        $this->assertEquals(2, $moa->latestVersion->version_no);
        $this->assertEquals($doc2->id, $moa->latestVersion->document_id);
    }

    public function test_coordinator_can_update_moa_metadata(): void
    {
        $moa = Moa::create([
            'moa_number' => 'MOA-UPDATE-01',
            'company_id' => $this->company->id,
            'type' => MoaType::UNIVERSITY_COMPANY,
            'status' => MoaStatus::DRAFT,
            'current_custodian_party' => MoaCustodianParty::OC,
            'summary' => 'Initial summary',
            'created_by' => $this->coordinator->id,
        ]);

        $response = $this->actingAs($this->coordinator)
            ->put(route('moas.update', $moa), [
                'type' => MoaType::UNIVERSITY_COMPANY->value,
                'effective_date' => '2026-11-01',
                'expiry_date' => '2027-11-01',
                'school_year' => '2026-2027',
                'summary' => 'Updated institutional terms after board review.',
            ]);

        $response->assertRedirect(route('moas.show', $moa));
        $moa->refresh();
        $this->assertEquals('Updated institutional terms after board review.', $moa->summary);
        $this->assertEquals('2026-11-01', $moa->effective_date->format('Y-m-d'));
    }

    public function test_coordinator_can_soft_delete_moa(): void
    {
        $moa = Moa::create([
            'moa_number' => 'MOA-ARCHIVE-01',
            'company_id' => $this->company->id,
            'type' => MoaType::UNIVERSITY_COMPANY,
            'status' => MoaStatus::EXPIRED,
            'current_custodian_party' => MoaCustodianParty::OC,
            'created_by' => $this->coordinator->id,
        ]);

        $response = $this->actingAs($this->coordinator)
            ->delete(route('moas.destroy', $moa));

        $response->assertRedirect(route('moas.index'));
        $this->assertSoftDeleted('moas', ['id' => $moa->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'moa.archived',
            'subject_type' => Moa::class,
            'subject_id' => $moa->id,
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_student_and_non_coordinator_authorization_rules(): void
    {
        // Student cannot create MOA
        $resCreate = $this->actingAs($this->studentUser)
            ->post(route('moas.store'), [
                'moa_number' => 'MOA-STUDENT-UNAUTH',
                'company_id' => $this->company->id,
                'type' => MoaType::STUDENT_COMPANY->value,
                'status' => MoaStatus::DRAFT->value,
                'current_custodian_party' => MoaCustodianParty::STUDENT->value,
            ]);
        $resCreate->assertStatus(403);

        $moa = Moa::create([
            'moa_number' => 'MOA-TEST-AUTH',
            'company_id' => $this->company->id,
            'type' => MoaType::UNIVERSITY_COMPANY,
            'status' => MoaStatus::DRAFT,
            'current_custodian_party' => MoaCustodianParty::OC,
            'created_by' => $this->coordinator->id,
        ]);

        // Student cannot delete MOA
        $resDelete = $this->actingAs($this->studentUser)
            ->delete(route('moas.destroy', $moa));
        $resDelete->assertStatus(403);
    }
}
