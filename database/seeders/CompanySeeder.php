<?php

namespace Database\Seeders;

use App\Enums\CompanyClassification;
use App\Enums\CompanySource;
use App\Enums\CompanyVerificationStatus;
use App\Enums\VerificationItemStatus;
use App\Models\Company;
use App\Models\CompanyVerificationItem;
use App\Models\CompanyVerificationLog;
use App\Models\User;
use App\Models\VerificationRequirement;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds 6 synthetic companies in mixed states (as specified in references/data-model.md line 94):
     * - Existing partners (verified and promoted)
     * - New companies (pending verification or under review)
     * - Rejected / Revoked company
     */
    public function run(): void
    {
        $ocUser = User::where('role', 'oc')->first();
        $requirements = VerificationRequirement::where('is_active', true)->get();

        $companies = [
            [
                'name' => 'NexTech Solutions Inc.',
                'classification' => CompanyClassification::EXISTING_PARTNER,
                'verification_status' => CompanyVerificationStatus::VERIFIED,
                'is_active' => true,
                'industry' => 'Software Development & Cloud Services',
                'address' => '4th Floor, Tech Hub Center, MacArthur Highway',
                'city' => 'Malolos, Bulacan',
                'contact_person' => 'Engr. Carlos Mendoza',
                'contact_email' => 'partnerships@nextech.example.test',
                'contact_phone' => '+63 44 791 2000',
                'website' => 'https://nextech.example.test',
                'description' => 'Enterprise full-stack web and mobile application engineering specializing in Laravel, React, and AWS cloud architectures.',
                'bsit_relevance_note' => 'High relevance for Web and Mobile Development track interns; active mentorship team with senior software engineers.',
                'source' => CompanySource::OC_CREATED,
                'promoted_by' => $ocUser?->id,
                'promoted_at' => now()->subMonths(6),
                'items_status' => VerificationItemStatus::ACCEPTED,
            ],
            [
                'name' => 'Bulacan Data Analytics Corp.',
                'classification' => CompanyClassification::EXISTING_PARTNER,
                'verification_status' => CompanyVerificationStatus::VERIFIED,
                'is_active' => true,
                'industry' => 'Data Analytics & Business Intelligence',
                'address' => 'Unit 12, Agri-Business Park',
                'city' => 'San Ildefonso, Bulacan',
                'contact_person' => 'Ms. Patricia Reyes',
                'contact_email' => 'careers@bulacandata.example.test',
                'contact_phone' => '+63 44 812 3456',
                'website' => 'https://bulacandata.example.test',
                'description' => 'Agricultural and commercial data intelligence consultancy providing predictive modeling, database administration, and reporting pipelines.',
                'bsit_relevance_note' => 'Direct match for Database Systems and Data Analytics students.',
                'source' => CompanySource::OC_CREATED,
                'promoted_by' => $ocUser?->id,
                'promoted_at' => now()->subMonths(3),
                'items_status' => VerificationItemStatus::ACCEPTED,
            ],
            [
                'name' => 'Apex Cloud Systems',
                'classification' => CompanyClassification::EXISTING_PARTNER,
                'verification_status' => CompanyVerificationStatus::VERIFIED,
                'is_active' => true,
                'industry' => 'IT Infrastructure & Cyber Security',
                'address' => 'CyberTower 2, Commercial Center',
                'city' => 'Baliuag, Bulacan',
                'contact_person' => 'Mr. David Tan',
                'contact_email' => 'interns@apexcloud.example.test',
                'contact_phone' => '+63 44 923 8899',
                'website' => 'https://apexcloud.example.test',
                'description' => 'Managed cloud operations, container orchestration, Linux systems administration, and firewall security solutions.',
                'bsit_relevance_note' => 'Exceptional environment for Network Administration and Systems Architecture majors.',
                'source' => CompanySource::OC_CREATED,
                'promoted_by' => $ocUser?->id,
                'promoted_at' => now()->subMonths(1),
                'items_status' => VerificationItemStatus::ACCEPTED,
            ],
            [
                'name' => 'Innovatech Digital Labs',
                'classification' => CompanyClassification::NEW_COMPANY,
                'verification_status' => CompanyVerificationStatus::UNDER_REVIEW,
                'is_active' => true,
                'industry' => 'UI/UX Design & Frontend Development',
                'address' => 'Innovation Hub, Poblacion',
                'city' => 'Pulilan, Bulacan',
                'contact_person' => 'Ms. Karen Santos',
                'contact_email' => 'hr@innovatechlabs.example.test',
                'contact_phone' => '+63 44 711 4455',
                'website' => 'https://innovatechlabs.example.test',
                'description' => 'Creative agency building modern digital interfaces, web portals, and branding assets for Central Luzon SMEs.',
                'bsit_relevance_note' => 'Strong fit for Multimedia and Frontend Web Development students.',
                'source' => CompanySource::STUDENT_SUGGESTION,
                'promoted_by' => null,
                'promoted_at' => null,
                'items_status' => VerificationItemStatus::SUBMITTED,
            ],
            [
                'name' => 'San Ildefonso Web Services',
                'classification' => CompanyClassification::NEW_COMPANY,
                'verification_status' => CompanyVerificationStatus::PENDING,
                'is_active' => true,
                'industry' => 'Web Design & Local Business Systems',
                'address' => 'Main Street Crossing',
                'city' => 'San Ildefonso, Bulacan',
                'contact_person' => 'Mr. Roberto Cruz',
                'contact_email' => 'contact@siwebservices.example.test',
                'contact_phone' => '+63 44 602 1122',
                'website' => 'https://siwebservices.example.test',
                'description' => 'Local web developer provider serving rural cooperatives and retail stores.',
                'bsit_relevance_note' => 'Practical for general IT technical support and WordPress/PHP site customization.',
                'source' => CompanySource::STUDENT_SUGGESTION,
                'promoted_by' => null,
                'promoted_at' => null,
                'items_status' => VerificationItemStatus::MISSING,
            ],
            [
                'name' => 'Vague Tech Ventures',
                'classification' => CompanyClassification::NEW_COMPANY,
                'verification_status' => CompanyVerificationStatus::REJECTED,
                'is_active' => false,
                'industry' => 'Unverified Telemarketing',
                'address' => 'Unregistered Office Suite',
                'city' => 'Unknown, Bulacan',
                'contact_person' => 'Unknown Representative',
                'contact_email' => 'info@vaguetech.example.test',
                'contact_phone' => '+63 900 000 0000',
                'website' => null,
                'description' => 'Failed business legitimacy check during coordinator verification.',
                'bsit_relevance_note' => 'Does not meet BSAU ICS technical curriculum standards for supervised internship.',
                'source' => CompanySource::OC_CREATED,
                'promoted_by' => null,
                'promoted_at' => null,
                'items_status' => VerificationItemStatus::REJECTED,
            ],
        ];

        foreach ($companies as $compData) {
            $itemsStatus = $compData['items_status'];
            unset($compData['items_status']);

            $company = Company::firstOrCreate(
                ['name' => $compData['name']],
                $compData
            );

            // Populate checklist items for all active requirements
            foreach ($requirements as $req) {
                CompanyVerificationItem::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'verification_requirement_id' => $req->id,
                    ],
                    [
                        'status' => $itemsStatus,
                        'remark' => $itemsStatus === VerificationItemStatus::ACCEPTED ? 'Verified compliance document on file.' : null,
                        'reviewed_by' => $itemsStatus === VerificationItemStatus::ACCEPTED ? $ocUser?->id : null,
                        'reviewed_at' => $itemsStatus === VerificationItemStatus::ACCEPTED ? now() : null,
                    ]
                );
            }

            // Create initial verification log entry
            CompanyVerificationLog::create([
                'company_id' => $company->id,
                'from_status' => null,
                'to_status' => $company->verification_status->value,
                'remark' => 'Initial record creation in seed batch.',
                'actor_id' => $ocUser?->id,
                'created_at' => now(),
            ]);
        }
    }
}
