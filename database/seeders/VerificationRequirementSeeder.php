<?php

namespace Database\Seeders;

use App\Models\VerificationRequirement;
use Illuminate\Database\Seeder;

class VerificationRequirementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds the 5 standard verification checklist requirements specified by the BSAU OJT Coordinator.
     * Stage is left null (unknown) per Institutional Assumption A11.
     */
    public function run(): void
    {
        $requirements = [
            [
                'label' => 'Proof of Business Registration',
                'description' => 'Valid SEC Registration, DTI Business Permit, or Municipal Business Certificate establishing legal entity operation in the Philippines.',
                'stage' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'label' => 'Memorandum of Agreement (MOA)',
                'description' => 'Executed or draft partnership Memorandum of Agreement conforming to university legal standards.',
                'stage' => null,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'label' => 'Internship Training Plan & Contract',
                'description' => 'Detailed curriculum training plan specifying technical IT/CS duties, assigned mentor, and workstation provisions.',
                'stage' => null,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'label' => 'Certificate of Completion Format',
                'description' => 'Standard template commitment confirming provision of formal certificate of completion upon required hours.',
                'stage' => null,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'label' => 'Signed Accomplishment Report Commitment',
                'description' => 'Supervisor agreement to review and endorse student weekly task accomplishment reports.',
                'stage' => null,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($requirements as $req) {
            VerificationRequirement::firstOrCreate(
                ['label' => $req['label']],
                $req
            );
        }
    }
}
