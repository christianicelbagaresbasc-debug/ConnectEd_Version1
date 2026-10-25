<?php

namespace App\Http\Controllers\Student;

use App\Enums\SkillProficiency;
use App\Enums\SkillStatus;
use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\StudentSkill;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class StudentSkillController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $studentProfile = $user->studentProfile;

        if (! $studentProfile) {
            abort(403, 'Student profile not initialized.');
        }

        $studentSkills = $studentProfile->studentSkills()->with('skill')->get();
        $mySkillIds = $studentSkills->pluck('skill_id')->all();

        // Catalog skills available to add
        $availableSkills = Skill::where('status', SkillStatus::ACTIVE)
            ->whereNotIn('id', $mySkillIds)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        $certifications = $studentProfile->certifications()->with('document')->latest()->get();

        return view('student.profile.skills', compact('studentProfile', 'studentSkills', 'availableSkills', 'certifications'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $studentProfile = $user->studentProfile;

        if (! $studentProfile) {
            abort(403, 'Student profile not initialized.');
        }

        $validated = $request->validate([
            'skill_id' => ['required', 'exists:skills,id'],
            'proficiency' => ['nullable', new Enum(SkillProficiency::class)],
        ]);

        $studentSkill = StudentSkill::updateOrCreate(
            [
                'student_profile_id' => $studentProfile->id,
                'skill_id' => $validated['skill_id'],
            ],
            [
                'proficiency' => $validated['proficiency'] ?? SkillProficiency::BEGINNER->value,
            ]
        );

        AuditService::log('student_skill.added', $studentProfile, [
            'skill_id' => $studentSkill->skill_id,
            'proficiency' => $studentSkill->proficiency?->value,
        ]);

        return redirect()->route('student.profile.skills')->with('status', 'Skill added to your profile.');
    }

    public function update(Request $request, StudentSkill $studentSkill): RedirectResponse
    {
        $user = Auth::user();
        $studentProfile = $user->studentProfile;

        if (! $studentProfile || $studentSkill->student_profile_id !== $studentProfile->id) {
            abort(403);
        }

        $validated = $request->validate([
            'proficiency' => ['required', new Enum(SkillProficiency::class)],
        ]);

        $studentSkill->update($validated);

        return redirect()->route('student.profile.skills')->with('status', 'Proficiency level updated.');
    }

    public function destroy(StudentSkill $studentSkill): RedirectResponse
    {
        $user = Auth::user();
        $studentProfile = $user->studentProfile;

        if (! $studentProfile || $studentSkill->student_profile_id !== $studentProfile->id) {
            abort(403);
        }

        $skillName = $studentSkill->skill?->name ?? 'Skill';
        $studentSkill->delete();

        AuditService::log('student_skill.removed', $studentProfile, [
            'skill_id' => $studentSkill->skill_id,
        ]);

        return redirect()->route('student.profile.skills')->with('status', "Skill '{$skillName}' removed from profile.");
    }

    public function suggest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:skills,name'],
            'category' => ['required', 'string', 'max:100'],
        ]);

        $skill = Skill::create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'status' => SkillStatus::PENDING,
            'suggested_by' => Auth::id(),
        ]);

        AuditService::log('skill.suggested', $skill, [
            'suggested_by' => Auth::id(),
            'name' => $skill->name,
        ]);

        return redirect()->route('student.profile.skills')->with(
            'status',
            "Skill '{$skill->name}' submitted for coordinator catalog review."
        );
    }
}
