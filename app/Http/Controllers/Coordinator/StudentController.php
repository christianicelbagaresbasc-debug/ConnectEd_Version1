<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\SkillStatus;
use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Coordinator Student Directory (FR-03, FR-18).
     * Strictly alphabetical / chronological listing.
     * Rule BR-20: Student match scores are NEVER calculated, displayed, or used to sort students here.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', StudentProfile::class);

        $query = StudentProfile::with(['user', 'skills', 'certifications'])
            ->whereHas('user', function ($q) {
                $q->where('status', 'active');
            });

        // Search by name, student number, or email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Program filter
        if ($request->filled('program')) {
            $query->where('program', $request->input('program'));
        }

        // Year level filter
        if ($request->filled('year_level')) {
            $query->where('year_level', $request->input('year_level'));
        }

        // Placement Status filter (placed vs looking)
        if ($request->filled('placement_status')) {
            $status = $request->input('placement_status');
            if ($status === 'placed') {
                $query->where('has_active_placement', true);
            } elseif ($status === 'unplaced') {
                $query->where('has_active_placement', false);
            }
        }

        // Skill filter
        if ($request->filled('skill_id')) {
            $skillId = $request->input('skill_id');
            $query->whereHas('skills', function ($sq) use ($skillId) {
                $sq->where('skills.id', $skillId);
            });
        }

        // Sorting: Name or Student Number. Strictly NO match score ranking (BR-20).
        $students = $query->join('users', 'student_profiles.user_id', '=', 'users.id')
            ->select('student_profiles.*')
            ->orderBy('users.name')
            ->paginate(15)
            ->withQueryString();

        $allowedPrograms = config('connected.allowed_programs', ['BSIT']);
        $skills = Skill::where('status', SkillStatus::ACTIVE)->orderBy('name')->get();

        return view('coordinator.students.index', compact('students', 'allowedPrograms', 'skills'));
    }

    /**
     * View detailed student profile.
     */
    public function show(StudentProfile $studentProfile): View
    {
        $this->authorize('view', $studentProfile);

        $studentProfile->load([
            'user',
            'studentSkills.skill',
            'certifications.document',
            'assignedCoc',
        ]);

        return view('coordinator.students.show', compact('studentProfile'));
    }
}
