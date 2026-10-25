<?php

namespace Database\Seeders;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\StudentProfile;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oc = User::where('role', UserRole::OC)->first();
        $coc = User::where('role', UserRole::COC)->first();

        if (! $oc || ! $coc) {
            return;
        }

        $students = StudentProfile::with('user')->take(3)->get();
        $companies = Company::take(2)->get();

        // Assign first student to COC
        if ($students->isNotEmpty()) {
            $students[0]->update(['assigned_coc_id' => $coc->id]);
        }

        Task::firstOrCreate([
            'title' => 'Audit MOA submission for BSIT Section 4A cohort',
        ], [
            'description' => 'Verify signed tripartite MOA copies and notarization for students placed at Cloud Solutions.',
            'created_by' => $oc->id,
            'assigned_to' => $coc->id,
            'due_date' => now()->addDays(2),
            'priority' => TaskPriority::HIGH,
            'status' => TaskStatus::IN_PROGRESS,
            'related_company_id' => $companies->first()?->id,
        ]);

        Task::firstOrCreate([
            'title' => 'Follow up on missing resume document',
        ], [
            'description' => 'Contact the student to ensure an updated PDF resume is uploaded into the Document Library.',
            'created_by' => $oc->id,
            'assigned_to' => $coc->id,
            'due_date' => now()->addDays(5),
            'priority' => TaskPriority::MEDIUM,
            'status' => TaskStatus::TODO,
            'related_student_profile_id' => $students->first()?->id,
        ]);

        Task::firstOrCreate([
            'title' => 'Complete orientation checklist for partner company site visit',
        ], [
            'description' => 'Coordinate with HR representative regarding on-site orientation schedule.',
            'created_by' => $oc->id,
            'assigned_to' => $coc->id,
            'due_date' => now()->subDay(),
            'priority' => TaskPriority::LOW,
            'status' => TaskStatus::DONE,
            'completed_at' => now()->subHours(6),
        ]);
    }
}
