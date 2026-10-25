<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\OpportunityRequirementLevel;
use App\Enums\OpportunityStatus;
use App\Enums\OpportunityWorkArrangement;
use App\Enums\PlacementStatus;
use App\Enums\SkillProficiency;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\WeeklyReportStatus;
use App\Models\Application;
use App\Models\Company;
use App\Models\Document;
use App\Models\Opportunity;
use App\Models\OpportunitySkill;
use App\Models\Placement;
use App\Models\Skill;
use App\Models\StudentCertification;
use App\Models\StudentProfile;
use App\Models\StudentSkill;
use App\Models\User;
use App\Models\WeeklyReport;
use App\Notifications\ConnectEdNotification;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OpportunityPlacementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds synthetic opportunities, student skills, applications, placements,
     * weekly accomplishment reports, and in-system notifications.
     */
    public function run(): void
    {
        $ocUser = User::where('role', UserRole::OC)->first();
        $studentUser = User::where('email', 'student@bsau.edu.ph')->first();
        $pcUser = User::where('role', UserRole::PC)->first();

        $nextech = Company::where('name', 'NexTech Solutions Inc.')->first();
        $dataCorp = Company::where('name', 'Bulacan Data Analytics Corp.')->first();
        $apex = Company::where('name', 'Apex Cloud Systems')->first();

        if (! $nextech || ! $dataCorp || ! $apex) {
            return;
        }

        // 1. Seed Student Profile & Technical Skills for Juan Dela Cruz
        if ($studentUser) {
            $juanProfile = StudentProfile::firstOrCreate(
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
                    'has_active_placement' => false,
                    'consented_at' => now(),
                    'privacy_notice_version' => 'v1.0-2026',
                ]
            );

            $skillsMap = [
                'PHP' => SkillProficiency::ADVANCED,
                'Laravel' => SkillProficiency::ADVANCED,
                'JavaScript' => SkillProficiency::INTERMEDIATE,
                'Tailwind CSS' => SkillProficiency::ADVANCED,
                'PostgreSQL' => SkillProficiency::INTERMEDIATE,
                'Git & Version Control' => SkillProficiency::ADVANCED,
                'HTML5 & CSS3' => SkillProficiency::ADVANCED,
            ];

            foreach ($skillsMap as $name => $proficiency) {
                $skill = Skill::where('name', $name)->first();
                if ($skill) {
                    StudentSkill::firstOrCreate(
                        [
                            'student_profile_id' => $juanProfile->id,
                            'skill_id' => $skill->id,
                        ],
                        [
                            'proficiency' => $proficiency,
                        ]
                    );
                }
            }

            StudentCertification::firstOrCreate(
                [
                    'student_profile_id' => $juanProfile->id,
                    'title' => 'Oracle Certified Foundations Associate, Java',
                ],
                [
                    'issuer' => 'Oracle University',
                    'issued_at' => '2025-06-15',
                ]
            );
        }

        // 2. Seed Realistic Opportunities with Required and Preferred Skills
        $opp1 = Opportunity::firstOrCreate(
            ['title' => 'Junior Full-Stack Web Developer (PHP / Laravel)', 'company_id' => $nextech->id],
            [
                'description' => 'Work directly with our senior development squad building cloud-backed enterprise web applications. Interns will write unit tests, contribute to Blade/Tailwind frontend views, and implement RESTful API services in Laravel 10.',
                'qualifications' => 'Candidate must be a graduating BSIT student with foundational grasp of object-oriented PHP, MVC architectures, relational databases (PostgreSQL or MySQL), and Git workflow.',
                'location' => 'Malolos, Bulacan',
                'work_arrangement' => OpportunityWorkArrangement::HYBRID,
                'slots_total' => 3,
                'slots_filled' => 1,
                'status' => OpportunityStatus::OPEN,
                'deadline' => now()->addMonths(2)->toDateString(),
                'created_by' => $ocUser?->id,
                'approved_by' => $ocUser?->id,
                'approved_at' => now()->subWeeks(2),
                'school_year' => '2026-2027',
            ]
        );

        $this->attachSkills($opp1, [
            ['name' => 'PHP', 'level' => OpportunityRequirementLevel::REQUIRED],
            ['name' => 'Laravel', 'level' => OpportunityRequirementLevel::REQUIRED],
            ['name' => 'PostgreSQL', 'level' => OpportunityRequirementLevel::REQUIRED],
            ['name' => 'Tailwind CSS', 'level' => OpportunityRequirementLevel::PREFERRED],
            ['name' => 'Git & Version Control', 'level' => OpportunityRequirementLevel::PREFERRED],
        ]);

        $opp2 = Opportunity::firstOrCreate(
            ['title' => 'Frontend & UI/UX Engineering Intern', 'company_id' => $nextech->id],
            [
                'description' => 'Collaborate with UI/UX designers to translate Figma prototypes into accessible, responsive web interfaces using modern CSS, Tailwind, and JavaScript.',
                'qualifications' => 'Proficiency with modern CSS layouts (Flexbox, Grid), semantic HTML5, and basic component-driven development.',
                'location' => 'Malolos, Bulacan',
                'work_arrangement' => OpportunityWorkArrangement::ONSITE,
                'slots_total' => 2,
                'slots_filled' => 0,
                'status' => OpportunityStatus::OPEN,
                'deadline' => now()->addMonths(2)->toDateString(),
                'created_by' => $ocUser?->id,
                'approved_by' => $ocUser?->id,
                'approved_at' => now()->subWeeks(1),
                'school_year' => '2026-2027',
            ]
        );

        $this->attachSkills($opp2, [
            ['name' => 'JavaScript', 'level' => OpportunityRequirementLevel::REQUIRED],
            ['name' => 'HTML5 & CSS3', 'level' => OpportunityRequirementLevel::REQUIRED],
            ['name' => 'Tailwind CSS', 'level' => OpportunityRequirementLevel::PREFERRED],
            ['name' => 'UI/UX Design (Figma)', 'level' => OpportunityRequirementLevel::PREFERRED],
        ]);

        $opp3 = Opportunity::firstOrCreate(
            ['title' => 'Database & Business Intelligence Intern', 'company_id' => $dataCorp->id],
            [
                'description' => 'Assist data analysts in designing normalized SQL schemas, extracting data pipelines with PostgreSQL and Python, and producing executive summary dashboards.',
                'qualifications' => 'Strong analytical skills, understanding of relational database normalization, SQL queries, and data visualization principles.',
                'location' => 'San Ildefonso, Bulacan',
                'work_arrangement' => OpportunityWorkArrangement::REMOTE,
                'slots_total' => 2,
                'slots_filled' => 0,
                'status' => OpportunityStatus::OPEN,
                'deadline' => now()->addMonths(2)->toDateString(),
                'created_by' => $ocUser?->id,
                'approved_by' => $ocUser?->id,
                'approved_at' => now()->subDays(5),
                'school_year' => '2026-2027',
            ]
        );

        $this->attachSkills($opp3, [
            ['name' => 'Database Design & Normalization', 'level' => OpportunityRequirementLevel::REQUIRED],
            ['name' => 'PostgreSQL', 'level' => OpportunityRequirementLevel::REQUIRED],
            ['name' => 'Python', 'level' => OpportunityRequirementLevel::PREFERRED],
            ['name' => 'RESTful API Design', 'level' => OpportunityRequirementLevel::PREFERRED],
        ]);

        $opp4 = Opportunity::firstOrCreate(
            ['title' => 'Systems Infrastructure & Cloud Intern', 'company_id' => $apex->id],
            [
                'description' => 'Support our cloud engineering team in configuring Linux server nodes, provisioning container clusters with Docker, and maintaining network routers.',
                'qualifications' => 'Basic knowledge of TCP/IP networking, Linux terminal commands, and system diagnostics.',
                'location' => 'Baliuag, Bulacan',
                'work_arrangement' => OpportunityWorkArrangement::HYBRID,
                'slots_total' => 2,
                'slots_filled' => 0,
                'status' => OpportunityStatus::OPEN,
                'deadline' => now()->addMonths(2)->toDateString(),
                'created_by' => $ocUser?->id,
                'approved_by' => $ocUser?->id,
                'approved_at' => now()->subDays(3),
                'school_year' => '2026-2027',
            ]
        );

        $this->attachSkills($opp4, [
            ['name' => 'Linux System Administration', 'level' => OpportunityRequirementLevel::REQUIRED],
            ['name' => 'Computer Networking (TCP/IP, DNS, DHCP)', 'level' => OpportunityRequirementLevel::REQUIRED],
            ['name' => 'Docker & Containerization', 'level' => OpportunityRequirementLevel::PREFERRED],
            ['name' => 'AWS Cloud Fundamentals', 'level' => OpportunityRequirementLevel::PREFERRED],
        ]);

        // 3. Seed Placed Student (Ana Marie Santos) with Placement & Weekly Reports
        $passwordHash = Hash::make(env('SEED_DEFAULT_PASSWORD', 'ConnectEd2026!'));
        $placedUser = User::firstOrCreate(
            ['email' => 'placed.student@bsau.edu.ph'],
            [
                'name' => 'Ana Marie Santos (Placed Student)',
                'password' => $passwordHash,
                'role' => UserRole::STUDENT,
                'status' => UserStatus::ACTIVE,
                'email_verified_at' => now(),
                'approved_at' => now()->subMonths(1),
                'must_change_password' => false,
            ]
        );

        $anaProfile = StudentProfile::firstOrCreate(
            ['user_id' => $placedUser->id],
            [
                'student_number' => '2023-ICS-0089',
                'program' => 'BS Information Technology',
                'year_level' => 4,
                'section' => '4A',
                'contact_number' => '+63 918 987 6543',
                'address' => 'Malolos, Bulacan',
                'bio' => 'Graduating BSIT intern deployed at NexTech Solutions Inc.',
                'school_year' => '2026-2027',
                'has_active_placement' => true,
                'consented_at' => now(),
                'privacy_notice_version' => 'v1.0-2026',
            ]
        );

        $anaApp = Application::firstOrCreate(
            [
                'student_profile_id' => $anaProfile->id,
                'opportunity_id' => $opp1->id,
            ],
            [
                'status' => ApplicationStatus::PLACED,
                'cover_note' => 'I am enthusiastic about joining NexTech Solutions as a full-stack intern.',
                'submitted_at' => now()->subDays(15),
                'decided_by' => $ocUser?->id,
                'decided_at' => now()->subDays(10),
                'decision_remark' => 'Candidate accepted with strong skill credentials.',
                'student_confirmed_at' => now()->subDays(9),
                'oc_confirmed_at' => now()->subDays(9),
                'school_year' => '2026-2027',
            ]
        );

        $anaPlacement = Placement::firstOrCreate(
            [
                'student_profile_id' => $anaProfile->id,
                'opportunity_id' => $opp1->id,
            ],
            [
                'company_id' => $nextech->id,
                'application_id' => $anaApp->id,
                'status' => PlacementStatus::ONGOING,
                'is_active' => true,
                'start_authorized_at' => now()->subDays(7),
                'start_authorization_note' => 'Formal start authorization letter issued and verified with host supervisor.',
                'start_date' => now()->subDays(7)->toDateString(),
                'end_date' => now()->addMonths(3)->toDateString(),
                'confirmed_by' => $ocUser?->id,
                'school_year' => '2026-2027',
            ]
        );

        // Seed 2 Weekly Reports for Ana Marie
        // Dummy document for accomplishment report
        $reportDoc1 = Document::firstOrCreate(
            ['stored_path' => 'demo/weekly_report_week_1.pdf'],
            [
                'category' => 'weekly_report',
                'disk' => 'documents',
                'storage_key' => 'demo/weekly_report_week_1.pdf',
                'original_name' => 'Accomplishment_Report_Week1_AnaSantos.pdf',
                'mime_type' => 'application/pdf',
                'size_bytes' => 124500,
                'checksum_sha256' => hash('sha256', 'demo-report-1'),
                'owner_user_id' => $placedUser->id,
                'uploaded_by' => $placedUser->id,
                'documentable_type' => Placement::class,
                'documentable_id' => $anaPlacement->id,
                'validation_status' => 'ok',
            ]
        );

        WeeklyReport::firstOrCreate(
            [
                'placement_id' => $anaPlacement->id,
                'week_start' => Carbon::now()->subWeek()->startOfWeek()->toDateString(),
            ],
            [
                'document_id' => $reportDoc1->id,
                'status' => WeeklyReportStatus::REVIEWED,
                'reviewed_by' => $ocUser?->id,
                'remark' => 'Excellent progress on onboarding and environment setup.',
            ]
        );

        $reportDoc2 = Document::firstOrCreate(
            ['stored_path' => 'demo/weekly_report_week_2.pdf'],
            [
                'category' => 'weekly_report',
                'disk' => 'documents',
                'storage_key' => 'demo/weekly_report_week_2.pdf',
                'original_name' => 'Accomplishment_Report_Week2_AnaSantos.pdf',
                'mime_type' => 'application/pdf',
                'size_bytes' => 135800,
                'checksum_sha256' => hash('sha256', 'demo-report-2'),
                'owner_user_id' => $placedUser->id,
                'uploaded_by' => $placedUser->id,
                'documentable_type' => Placement::class,
                'documentable_id' => $anaPlacement->id,
                'validation_status' => 'ok',
            ]
        );

        WeeklyReport::firstOrCreate(
            [
                'placement_id' => $anaPlacement->id,
                'week_start' => Carbon::now()->startOfWeek()->toDateString(),
            ],
            [
                'document_id' => $reportDoc2->id,
                'status' => WeeklyReportStatus::SUBMITTED,
                'reviewed_by' => null,
                'remark' => null,
            ]
        );

        // 4. Seed Active Application for Juan Dela Cruz (Offer Extended, Awaiting Student Confirm)
        if ($studentUser && isset($juanProfile)) {
            $juanApp = Application::firstOrCreate(
                [
                    'student_profile_id' => $juanProfile->id,
                    'opportunity_id' => $opp1->id,
                ],
                [
                    'status' => ApplicationStatus::ACCEPTED,
                    'cover_note' => 'I would love to apply my PHP and Laravel skills at NexTech Solutions.',
                    'submitted_at' => now()->subDays(3),
                    'decided_by' => $ocUser?->id,
                    'decided_at' => now()->subDay(),
                    'decision_remark' => 'Candidate accepted with strong skill credentials.',
                    'student_confirmed_at' => null,
                    'oc_confirmed_at' => now()->subDay(),
                    'school_year' => '2026-2027',
                ]
            );

            // In-system notifications
            $studentUser->notify(new ConnectEdNotification(
                title: 'Placement Endorsed by Coordinator',
                body: "Coordinator has endorsed your placement offer for {$opp1->title}. Please confirm to finalize your placement.",
                actionUrl: route('student.applications.show', $juanApp),
                type: 'offer_endorsed_by_coordinator'
            ));
        }

        // Notify OC about Ana's second report submission
        if ($ocUser) {
            $ocUser->notify(new ConnectEdNotification(
                title: 'Weekly Report Submitted',
                body: 'Student Ana Marie Santos submitted a weekly accomplishment report for review.',
                actionUrl: route('coordinator.placements.show', $anaPlacement),
                type: 'weekly_report_submitted'
            ));
        }
    }

    /**
     * Helper to attach skills to an opportunity.
     *
     * @param  array<int, array{name: string, level: OpportunityRequirementLevel}>  $skillsList
     */
    private function attachSkills(Opportunity $opportunity, array $skillsList): void
    {
        foreach ($skillsList as $item) {
            $skill = Skill::where('name', $item['name'])->first();
            if ($skill) {
                OpportunitySkill::firstOrCreate(
                    [
                        'opportunity_id' => $opportunity->id,
                        'skill_id' => $skill->id,
                    ],
                    [
                        'requirement_level' => $item['level'],
                    ]
                );
            }
        }
    }
}
