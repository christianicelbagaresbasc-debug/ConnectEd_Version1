<?php

namespace Tests\Feature\Moa;

use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\MoaCustodianParty;
use App\Enums\MoaNoteType;
use App\Enums\MoaRiskLevel;
use App\Enums\MoaStatus;
use App\Enums\MoaType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Moa;
use App\Models\MoaNote;
use App\Models\PartnerAccount;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoaLegalReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $coordinator;

    protected User $attorney;

    protected User $studentUser;

    protected User $pcUser;

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

        $this->company = Company::create([
            'name' => 'DataCore Philippines Inc.',
            'classification' => CompanyClassification::EXISTING_PARTNER,
            'verification_status' => CompanyVerificationStatus::VERIFIED,
            'is_active' => true,
            'industry' => 'Data Engineering',
            'city' => 'Baliuag, Bulacan',
        ]);

        $this->studentProfile = StudentProfile::create([
            'user_id' => $this->studentUser->id,
            'student_number' => '2023-ICS-0099',
            'program' => 'BS Information Technology',
            'year_level' => 4,
            'section' => '4B',
            'school_year' => '2026-2027',
            'has_active_placement' => false,
            'consented_at' => now(),
            'privacy_notice_version' => 'v1.0',
        ]);

        $this->pcUser = User::factory()->create([
            'role' => UserRole::PC,
            'status' => UserStatus::ACTIVE,
            'must_change_password' => false,
        ]);

        PartnerAccount::create([
            'user_id' => $this->pcUser->id,
            'company_id' => $this->company->id,
            'designation' => 'HR Director',
            'department' => 'People Operations',
            'contact_number' => '+63 918 000 1122',
            'can_manage_company_profile' => true,
            'can_submit_opportunities' => true,
            'can_view_matched_applicants' => true,
            'can_manage_supervisors' => true,
        ]);
    }

    public function test_attorney_can_view_legal_review_queue(): void
    {
        $moa1 = Moa::create([
            'moa_number' => 'MOA-LEGAL-QUEUE-1',
            'company_id' => $this->company->id,
            'type' => MoaType::UNIVERSITY_COMPANY,
            'status' => MoaStatus::UNDER_LEGAL_REVIEW,
            'current_custodian_party' => MoaCustodianParty::UA,
            'summary' => 'Pending legal advice on student safety liability',
            'created_by' => $this->coordinator->id,
        ]);

        $moa2 = Moa::create([
            'moa_number' => 'MOA-ACTIVE-CLEARED',
            'company_id' => $this->company->id,
            'type' => MoaType::UNIVERSITY_COMPANY,
            'status' => MoaStatus::ACTIVE,
            'current_custodian_party' => MoaCustodianParty::OC,
            'summary' => 'Active agreement with no unresolved notes',
            'created_by' => $this->coordinator->id,
        ]);

        // Default queue shows under_legal_review
        $response = $this->actingAs($this->attorney)->get(route('attorney.moas.index'));
        $response->assertStatus(200);
        $response->assertSee('MOA-LEGAL-QUEUE-1');
        $response->assertDontSee('MOA-ACTIVE-CLEARED');

        // Queue with all parameter shows both
        $allResponse = $this->actingAs($this->attorney)->get(route('attorney.moas.index', ['queue' => 'all']));
        $allResponse->assertStatus(200);
        $allResponse->assertSee('MOA-LEGAL-QUEUE-1');
        $allResponse->assertSee('MOA-ACTIVE-CLEARED');
    }

    public function test_non_legal_roles_cannot_access_attorney_queue(): void
    {
        $resStudent = $this->actingAs($this->studentUser)->get(route('attorney.moas.index'));
        $resStudent->assertStatus(403);

        $resPc = $this->actingAs($this->pcUser)->get(route('attorney.moas.index'));
        $resPc->assertStatus(403);
    }

    public function test_attorney_and_coordinator_can_store_internal_notes(): void
    {
        $moa = Moa::create([
            'moa_number' => 'MOA-NOTE-TEST',
            'company_id' => $this->company->id,
            'type' => MoaType::UNIVERSITY_COMPANY,
            'status' => MoaStatus::UNDER_LEGAL_REVIEW,
            'current_custodian_party' => MoaCustodianParty::UA,
            'created_by' => $this->coordinator->id,
        ]);

        // Attorney logs a high-risk legal remark
        $response = $this->actingAs($this->attorney)->post(route('attorney.moas.notes.store', $moa), [
            'type' => MoaNoteType::LEGAL_REMARK->value,
            'risk_level' => MoaRiskLevel::HIGH->value,
            'body' => 'Indemnification clause disproportionately burdens the university.',
        ]);

        $response->assertRedirect(route('moas.show', $moa));

        $this->assertDatabaseHas('moa_notes', [
            'moa_id' => $moa->id,
            'author_id' => $this->attorney->id,
            'type' => MoaNoteType::LEGAL_REMARK->value,
            'risk_level' => MoaRiskLevel::HIGH->value,
            'body' => 'Indemnification clause disproportionately burdens the university.',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'moa_note.created',
            'subject_type' => Moa::class,
            'subject_id' => $moa->id,
            'actor_id' => $this->attorney->id,
        ]);
    }

    /**
     * Rule BR-18: Confidentiality of Legal Notes
     * UA legal remarks, risk levels, and revision directives are strictly internal
     * to university personnel (OC, COC, UA) and must never leak to Partner Company (PC)
     * or Student views.
     */
    public function test_confidentiality_of_legal_notes_br18(): void
    {
        $moa = Moa::create([
            'moa_number' => 'MOA-CONFIDENTIAL-TEST',
            'company_id' => $this->company->id,
            'student_profile_id' => $this->studentProfile->id,
            'type' => MoaType::STUDENT_COMPANY,
            'status' => MoaStatus::UNDER_LEGAL_REVIEW,
            'current_custodian_party' => MoaCustodianParty::UA,
            'created_by' => $this->coordinator->id,
        ]);

        $secretComment = 'CONFIDENTIAL_ATTORNEY_ASSESSMENT: Company indemnification terms expose BSAU to statutory liability.';

        MoaNote::create([
            'moa_id' => $moa->id,
            'author_id' => $this->attorney->id,
            'type' => MoaNoteType::LEGAL_REMARK,
            'risk_level' => MoaRiskLevel::HIGH,
            'body' => $secretComment,
        ]);

        // University Attorney sees the note
        $uaView = $this->actingAs($this->attorney)->get(route('moas.show', $moa));
        $uaView->assertStatus(200);
        $uaView->assertSee($secretComment);
        $uaView->assertSee('High Risk');

        // Coordinator sees the note
        $ocView = $this->actingAs($this->coordinator)->get(route('moas.show', $moa));
        $ocView->assertStatus(200);
        $ocView->assertSee($secretComment);

        // Student CANNOT see the note
        $studentView = $this->actingAs($this->studentUser)->get(route('moas.show', $moa));
        $studentView->assertStatus(200);
        $studentView->assertDontSee($secretComment);
        $studentView->assertDontSee('High Risk');
        $studentView->assertDontSee('Legal & Internal Notes');

        // Partner Company CANNOT see the note
        $pcView = $this->actingAs($this->pcUser)->get(route('moas.show', $moa));
        $pcView->assertStatus(200);
        $pcView->assertDontSee($secretComment);
        $pcView->assertDontSee('High Risk');
        $pcView->assertDontSee('Legal & Internal Notes');

        // Student cannot post internal note
        $resStudentPost = $this->actingAs($this->studentUser)
            ->post(route('attorney.moas.notes.store', $moa), [
                'type' => MoaNoteType::LEGAL_REMARK->value,
                'body' => 'Illegal student remark',
            ]);
        $resStudentPost->assertStatus(403);

        // PC cannot post internal note
        $resPcPost = $this->actingAs($this->pcUser)
            ->post(route('attorney.moas.notes.store', $moa), [
                'type' => MoaNoteType::LEGAL_REMARK->value,
                'body' => 'Illegal partner remark',
            ]);
        $resPcPost->assertStatus(403);
    }

    public function test_attorney_and_coordinator_can_resolve_notes(): void
    {
        $moa = Moa::create([
            'moa_number' => 'MOA-RESOLVE-TEST',
            'company_id' => $this->company->id,
            'type' => MoaType::UNIVERSITY_COMPANY,
            'status' => MoaStatus::UNDER_LEGAL_REVIEW,
            'current_custodian_party' => MoaCustodianParty::UA,
            'created_by' => $this->coordinator->id,
        ]);

        $note = MoaNote::create([
            'moa_id' => $moa->id,
            'author_id' => $this->attorney->id,
            'type' => MoaNoteType::REVISION,
            'risk_level' => MoaRiskLevel::MEDIUM,
            'body' => 'Fix typographical errors in annex.',
        ]);

        $this->assertFalse($note->isResolved());

        $response = $this->actingAs($this->attorney)
            ->patch(route('attorney.moas.notes.resolve', [$moa, $note]));

        $response->assertRedirect(route('moas.show', $moa));

        $note->refresh();
        $this->assertTrue($note->isResolved());
        $this->assertEquals($this->attorney->id, $note->resolved_by);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'moa_note.resolved',
            'subject_type' => Moa::class,
            'subject_id' => $moa->id,
            'actor_id' => $this->attorney->id,
        ]);
    }

    public function test_attorney_can_formally_clear_legal_review(): void
    {
        $moa = Moa::create([
            'moa_number' => 'MOA-CLEARANCE-TEST',
            'company_id' => $this->company->id,
            'type' => MoaType::UNIVERSITY_COMPANY,
            'status' => MoaStatus::UNDER_LEGAL_REVIEW,
            'current_custodian_party' => MoaCustodianParty::UA,
            'created_by' => $this->coordinator->id,
        ]);

        $response = $this->actingAs($this->attorney)
            ->post(route('attorney.moas.clear', $moa), [
                'target_status' => MoaStatus::FOR_SIGNING_SCHOOL->value,
                'clearance_remark' => 'All legal reservations satisfied. Approved for institutional signature by University President.',
            ]);

        $response->assertRedirect(route('moas.show', $moa));

        $moa->refresh();
        $this->assertEquals(MoaStatus::FOR_SIGNING_SCHOOL, $moa->status);

        // Clearance note created
        $this->assertDatabaseHas('moa_notes', [
            'moa_id' => $moa->id,
            'author_id' => $this->attorney->id,
            'type' => MoaNoteType::LEGAL_REMARK->value,
            'risk_level' => MoaRiskLevel::LOW->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'moa.legal_cleared',
            'subject_type' => Moa::class,
            'subject_id' => $moa->id,
            'actor_id' => $this->attorney->id,
        ]);
    }
}
