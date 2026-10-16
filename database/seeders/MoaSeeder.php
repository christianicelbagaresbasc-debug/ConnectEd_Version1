<?php

namespace Database\Seeders;

use App\Enums\MoaCustodianParty;
use App\Enums\MoaNoteType;
use App\Enums\MoaRiskLevel;
use App\Enums\MoaStatus;
use App\Enums\MoaType;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Document;
use App\Models\Moa;
use App\Models\MoaNote;
use App\Models\MoaTransfer;
use App\Models\MoaVersion;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class MoaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds synthetic MOAs spanning:
     * - Active institutional partnership
     * - MOA under legal review with internal attorney notes (Rule BR-18)
     * - Student-specific MOA in signing custody (Rule BR-16)
     * - Expired / legacy MOA
     */
    public function run(): void
    {
        $ocUser = User::where('role', UserRole::OC)->first();
        $cocUser = User::where('role', UserRole::COC)->first();
        $uaUser = User::where('role', UserRole::UA)->first();
        $studentUser = User::where('role', UserRole::STUDENT)->first();

        // Ensure student profile exists for synthetic student
        $studentProfile = null;
        if ($studentUser) {
            $studentProfile = StudentProfile::firstOrCreate(
                ['user_id' => $studentUser->id],
                [
                    'student_number' => '2023-ICS-0042',
                    'program' => 'BS Information Technology',
                    'year_level' => 4,
                    'section' => '4A',
                    'contact_number' => '+63 917 123 4567',
                    'address' => 'San Ildefonso, Bulacan',
                    'bio' => 'Senior BSIT student concentrating on Web Engineering and Cloud Architectures.',
                    'school_year' => '2026-2027',
                    'assigned_coc_id' => $cocUser?->id,
                    'has_active_placement' => false,
                    'consented_at' => now(),
                    'privacy_notice_version' => 'v1.0-2026',
                ]
            );
        }

        $nextech = Company::where('name', 'NexTech Solutions Inc.')->first();
        $bulacanData = Company::where('name', 'Bulacan Data Analytics Corp.')->first();
        $apexCloud = Company::where('name', 'Apex Cloud Systems')->first();
        $innovatech = Company::where('name', 'Innovatech Digital Labs')->first();

        // 1. Active Institutional MOA with NexTech Solutions Inc.
        if ($nextech && $ocUser) {
            $moa1 = Moa::firstOrCreate(
                ['moa_number' => 'MOA-2026-001'],
                [
                    'company_id' => $nextech->id,
                    'student_profile_id' => null,
                    'type' => MoaType::UNIVERSITY_COMPANY,
                    'status' => MoaStatus::ACTIVE,
                    'current_custodian_party' => MoaCustodianParty::OC,
                    'current_custodian_user_id' => $ocUser->id,
                    'effective_date' => now()->subMonths(3)->toDateString(),
                    'expiry_date' => now()->addMonths(9)->toDateString(),
                    'summary' => 'Institutional Memorandum of Agreement between BSAU and NexTech Solutions Inc. for BS Information Technology internship placements.',
                    'school_year' => '2026-2027',
                    'created_by' => $ocUser->id,
                ]
            );

            $doc1 = Document::firstOrCreate(
                ['storage_key' => 'moa-nextech-2026-v1'],
                [
                    'owner_user_id' => $ocUser->id,
                    'category' => 'moa',
                    'original_name' => 'BSAU-NexTech-Notarized-MOA.pdf',
                    'stored_path' => 'moas/BSAU-NexTech-Notarized-MOA.pdf',
                    'disk' => 'documents',
                    'mime_type' => 'application/pdf',
                    'size_bytes' => 314572,
                    'validation_status' => 'ok',
                    'uploaded_by' => $ocUser->id,
                    'is_current' => true,
                    'review_status' => 'accepted',
                    'school_year' => '2026-2027',
                ]
            );

            MoaVersion::firstOrCreate(
                ['moa_id' => $moa1->id, 'version_no' => 1],
                [
                    'document_id' => $doc1->id,
                    'uploaded_by' => $ocUser->id,
                    'note' => 'Fully executed and notarized institutional MOA.',
                ]
            );

            // Custody transfers for MOA 1
            if ($moa1->transfers()->count() === 0) {
                MoaTransfer::create([
                    'moa_id' => $moa1->id,
                    'from_party' => MoaCustodianParty::OC,
                    'to_party' => MoaCustodianParty::UA,
                    'from_user_id' => $ocUser->id,
                    'to_user_id' => $uaUser?->id,
                    'actor_id' => $ocUser->id,
                    'note' => 'Submitted draft MOA for institutional legal clearance.',
                    'created_at' => now()->subMonths(4),
                ]);

                MoaTransfer::create([
                    'moa_id' => $moa1->id,
                    'from_party' => MoaCustodianParty::UA,
                    'to_party' => MoaCustodianParty::COMPANY,
                    'from_user_id' => $uaUser?->id,
                    'to_user_id' => null,
                    'actor_id' => $uaUser?->id ?? $ocUser->id,
                    'note' => 'Legal review approved without objection. Dispatched to company for corporate signature.',
                    'created_at' => now()->subMonths(3)->subWeeks(2),
                ]);

                MoaTransfer::create([
                    'moa_id' => $moa1->id,
                    'from_party' => MoaCustodianParty::COMPANY,
                    'to_party' => MoaCustodianParty::OC,
                    'from_user_id' => null,
                    'to_user_id' => $ocUser->id,
                    'actor_id' => $ocUser->id,
                    'note' => 'Received signed original copies from NexTech executive office; placed in ICS vault repository.',
                    'created_at' => now()->subMonths(3),
                ]);
            }
        }

        // 2. MOA Under Legal Review with Bulacan Data Analytics Corp.
        if ($bulacanData && $ocUser) {
            $moa2 = Moa::firstOrCreate(
                ['moa_number' => 'MOA-2026-002'],
                [
                    'company_id' => $bulacanData->id,
                    'student_profile_id' => null,
                    'type' => MoaType::UNIVERSITY_COMPANY,
                    'status' => MoaStatus::UNDER_LEGAL_REVIEW,
                    'current_custodian_party' => MoaCustodianParty::UA,
                    'current_custodian_user_id' => $uaUser?->id,
                    'effective_date' => null,
                    'expiry_date' => null,
                    'summary' => 'Proposed institutional partnership MOA with data privacy schedule and student intellectual property clauses under legal scrutiny.',
                    'school_year' => '2026-2027',
                    'created_by' => $ocUser->id,
                ]
            );

            $doc2 = Document::firstOrCreate(
                ['storage_key' => 'moa-bulacan-data-draft-v1'],
                [
                    'owner_user_id' => $ocUser->id,
                    'category' => 'moa',
                    'original_name' => 'BSAU-BDAC-Draft-Agreement-v1.pdf',
                    'stored_path' => 'moas/BSAU-BDAC-Draft-Agreement-v1.pdf',
                    'disk' => 'documents',
                    'mime_type' => 'application/pdf',
                    'size_bytes' => 198420,
                    'validation_status' => 'ok',
                    'uploaded_by' => $ocUser->id,
                    'is_current' => true,
                    'review_status' => 'pending_review',
                    'school_year' => '2026-2027',
                ]
            );

            MoaVersion::firstOrCreate(
                ['moa_id' => $moa2->id, 'version_no' => 1],
                [
                    'document_id' => $doc2->id,
                    'uploaded_by' => $ocUser->id,
                    'note' => 'Initial draft transmitted with company-requested proprietary data non-disclosure terms.',
                ]
            );

            // Attorney remarks (Rule BR-18: Confidential internal notes)
            if ($moa2->notes()->count() === 0 && $uaUser) {
                MoaNote::create([
                    'moa_id' => $moa2->id,
                    'author_id' => $uaUser->id,
                    'type' => MoaNoteType::LEGAL_REMARK,
                    'risk_level' => MoaRiskLevel::HIGH,
                    'body' => 'Section 6.3 asserts company ownership over student capstone code written during training. This directly conflicts with BSAU ICS IP policy and CHED CMO No. 104 s. 2017. Must be amended to retain student/university joint copyright before signing.',
                    'resolved_at' => null,
                    'resolved_by' => null,
                ]);

                MoaNote::create([
                    'moa_id' => $moa2->id,
                    'author_id' => $uaUser->id,
                    'type' => MoaNoteType::REVISION,
                    'risk_level' => MoaRiskLevel::MEDIUM,
                    'body' => 'Incorporate the standard BSAU Data Protection Agreement annex referencing the Data Privacy Act of 2012 (RA 10173).',
                    'resolved_at' => null,
                    'resolved_by' => null,
                ]);
            }

            if ($moa2->transfers()->count() === 0) {
                MoaTransfer::create([
                    'moa_id' => $moa2->id,
                    'from_party' => MoaCustodianParty::OC,
                    'to_party' => MoaCustodianParty::UA,
                    'from_user_id' => $ocUser->id,
                    'to_user_id' => $uaUser?->id,
                    'actor_id' => $ocUser->id,
                    'note' => 'Transferred draft contract to University Attorney for review of intellectual property terms.',
                    'created_at' => now()->subDays(5),
                ]);
            }
        }

        // 3. Student-Specific MOA in Signing School State (Rule BR-16 Unconstrained custody)
        if ($apexCloud && $ocUser && $studentProfile) {
            $moa3 = Moa::firstOrCreate(
                ['moa_number' => 'MOA-2026-003'],
                [
                    'company_id' => $apexCloud->id,
                    'student_profile_id' => $studentProfile->id,
                    'type' => MoaType::STUDENT_COMPANY,
                    'status' => MoaStatus::FOR_SIGNING_SCHOOL,
                    'current_custodian_party' => MoaCustodianParty::COC,
                    'current_custodian_user_id' => $cocUser?->id,
                    'effective_date' => now()->toDateString(),
                    'expiry_date' => now()->addMonths(5)->toDateString(),
                    'summary' => 'Individual Student Training Agreement for Juan Dela Cruz for 486-hour OJT placement at Apex Cloud Systems.',
                    'school_year' => '2026-2027',
                    'created_by' => $ocUser->id,
                ]
            );

            $doc3 = Document::firstOrCreate(
                ['storage_key' => 'moa-student-apex-v1'],
                [
                    'owner_user_id' => $studentUser->id,
                    'category' => 'moa',
                    'original_name' => 'MOA-JuanDelaCruz-ApexCloud-SignedCompany.pdf',
                    'stored_path' => 'moas/MOA-JuanDelaCruz-ApexCloud-SignedCompany.pdf',
                    'disk' => 'documents',
                    'mime_type' => 'application/pdf',
                    'size_bytes' => 241088,
                    'validation_status' => 'ok',
                    'uploaded_by' => $ocUser->id,
                    'is_current' => true,
                    'review_status' => 'accepted',
                    'school_year' => '2026-2027',
                ]
            );

            MoaVersion::firstOrCreate(
                ['moa_id' => $moa3->id, 'version_no' => 1],
                [
                    'document_id' => $doc3->id,
                    'uploaded_by' => $ocUser->id,
                    'note' => 'Student training agreement pre-signed by Apex Cloud HR Manager.',
                ]
            );

            if ($moa3->transfers()->count() === 0) {
                MoaTransfer::create([
                    'moa_id' => $moa3->id,
                    'from_party' => MoaCustodianParty::STUDENT,
                    'to_party' => MoaCustodianParty::OC,
                    'from_user_id' => $studentUser->id,
                    'to_user_id' => $ocUser->id,
                    'actor_id' => $studentUser->id,
                    'note' => 'Student hand-delivered signed company internship agreement to ICS Coordinator.',
                    'created_at' => now()->subDays(2),
                ]);

                MoaTransfer::create([
                    'moa_id' => $moa3->id,
                    'from_party' => MoaCustodianParty::OC,
                    'to_party' => MoaCustodianParty::COC,
                    'from_user_id' => $ocUser->id,
                    'to_user_id' => $cocUser?->id,
                    'actor_id' => $ocUser->id,
                    'note' => 'Handed over to Co-Coordinator for Academic Dean and Institute Director endorsement signatures.',
                    'created_at' => now()->subDay(),
                ]);
            }
        }

        // 4. Expired Legacy MOA
        if ($innovatech && $ocUser) {
            $moa4 = Moa::firstOrCreate(
                ['moa_number' => 'MOA-2024-009'],
                [
                    'company_id' => $innovatech->id,
                    'student_profile_id' => null,
                    'type' => MoaType::UNIVERSITY_COMPANY,
                    'status' => MoaStatus::EXPIRED,
                    'current_custodian_party' => MoaCustodianParty::OC,
                    'current_custodian_user_id' => $ocUser->id,
                    'effective_date' => now()->subYears(2)->toDateString(),
                    'expiry_date' => now()->subMonths(2)->toDateString(),
                    'summary' => 'Archived AY 2024-2025 pilot digital agency partnership agreement (Expired).',
                    'school_year' => '2024-2025',
                    'created_by' => $ocUser->id,
                ]
            );

            $doc4 = Document::firstOrCreate(
                ['storage_key' => 'moa-innovatech-2024-archive'],
                [
                    'owner_user_id' => $ocUser->id,
                    'category' => 'moa',
                    'original_name' => 'BSAU-Innovatech-MOA-2024.pdf',
                    'stored_path' => 'moas/BSAU-Innovatech-MOA-2024.pdf',
                    'disk' => 'documents',
                    'mime_type' => 'application/pdf',
                    'size_bytes' => 158220,
                    'validation_status' => 'ok',
                    'uploaded_by' => $ocUser->id,
                    'is_current' => true,
                    'review_status' => 'accepted',
                    'school_year' => '2024-2025',
                ]
            );

            MoaVersion::firstOrCreate(
                ['moa_id' => $moa4->id, 'version_no' => 1],
                [
                    'document_id' => $doc4->id,
                    'uploaded_by' => $ocUser->id,
                    'note' => 'Legacy 2024 term MOA, archived upon expiration.',
                ]
            );
        }
    }
}
