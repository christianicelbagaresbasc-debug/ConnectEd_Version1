<?php

namespace Database\Seeders;

use App\Enums\SkillStatus;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            // Programming & Scripting
            ['name' => 'PHP', 'category' => 'Programming'],
            ['name' => 'JavaScript', 'category' => 'Programming'],
            ['name' => 'TypeScript', 'category' => 'Programming'],
            ['name' => 'Python', 'category' => 'Programming'],
            ['name' => 'Java', 'category' => 'Programming'],
            ['name' => 'C# / .NET', 'category' => 'Programming'],
            ['name' => 'C++', 'category' => 'Programming'],
            ['name' => 'Dart', 'category' => 'Programming'],

            // Web & Mobile Development
            ['name' => 'Laravel', 'category' => 'Web Development'],
            ['name' => 'React.js', 'category' => 'Web Development'],
            ['name' => 'Vue.js', 'category' => 'Web Development'],
            ['name' => 'Node.js / Express', 'category' => 'Web Development'],
            ['name' => 'Tailwind CSS', 'category' => 'Web Development'],
            ['name' => 'HTML5 & CSS3', 'category' => 'Web Development'],
            ['name' => 'Flutter / Mobile Dev', 'category' => 'Mobile Development'],
            ['name' => 'RESTful API Design', 'category' => 'Web Development'],

            // Database & Data Management
            ['name' => 'PostgreSQL', 'category' => 'Database'],
            ['name' => 'MySQL / MariaDB', 'category' => 'Database'],
            ['name' => 'MongoDB', 'category' => 'Database'],
            ['name' => 'Redis', 'category' => 'Database'],
            ['name' => 'Database Design & Normalization', 'category' => 'Database'],

            // Networking & Systems
            ['name' => 'Computer Networking (TCP/IP, DNS, DHCP)', 'category' => 'Networking'],
            ['name' => 'Cisco Routing & Switching', 'category' => 'Networking'],
            ['name' => 'Linux System Administration', 'category' => 'Systems & Infrastructure'],
            ['name' => 'Windows Server Administration', 'category' => 'Systems & Infrastructure'],
            ['name' => 'Virtualization (VMware, VirtualBox)', 'category' => 'Systems & Infrastructure'],

            // Cloud & DevOps
            ['name' => 'Git & Version Control', 'category' => 'DevOps'],
            ['name' => 'Docker & Containerization', 'category' => 'DevOps'],
            ['name' => 'CI/CD Pipelines (GitHub Actions)', 'category' => 'DevOps'],
            ['name' => 'AWS Cloud Fundamentals', 'category' => 'Cloud'],
            ['name' => 'Vercel / Cloud Deployment', 'category' => 'Cloud'],

            // Cybersecurity & Quality Assurance
            ['name' => 'Cybersecurity Fundamentals', 'category' => 'Security'],
            ['name' => 'Web Application Security (OWASP)', 'category' => 'Security'],
            ['name' => 'Software Testing & QA (PHPUnit, Jest)', 'category' => 'Quality Assurance'],

            // Design & Multimedia
            ['name' => 'UI/UX Design (Figma)', 'category' => 'Design'],
            ['name' => 'Graphic Design (Photoshop / Illustrator)', 'category' => 'Design'],
            ['name' => 'Technical Documentation', 'category' => 'Technical Writing'],

            // Professional & Soft Skills
            ['name' => 'Technical Problem Solving', 'category' => 'Soft Skills'],
            ['name' => 'Agile / Scrum Methodology', 'category' => 'Project Management'],
            ['name' => 'Interpersonal Communication', 'category' => 'Soft Skills'],
        ];

        foreach ($skills as $item) {
            Skill::firstOrCreate(
                ['name' => $item['name']],
                [
                    'category' => $item['category'],
                    'status' => SkillStatus::ACTIVE,
                    'suggested_by' => null,
                ]
            );
        }
    }
}
