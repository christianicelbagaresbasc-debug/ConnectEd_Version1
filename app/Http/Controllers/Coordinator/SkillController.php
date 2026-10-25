<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\SkillStatus;
use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class SkillController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Skill::class);

        $query = Skill::with('suggester')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $skills = $query->paginate(20)->withQueryString();
        $categories = Skill::select('category')->distinct()->pluck('category');

        $pendingCount = Skill::where('status', SkillStatus::PENDING)->count();

        return view('coordinator.skills.index', compact('skills', 'categories', 'pendingCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Skill::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:skills,name'],
            'category' => ['required', 'string', 'max:100'],
            'status' => ['required', new Enum(SkillStatus::class)],
        ]);

        $skill = Skill::create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'status' => $validated['status'],
            'suggested_by' => Auth::id(),
        ]);

        AuditService::log('skill.created', $skill, [
            'name' => $skill->name,
            'category' => $skill->category,
            'status' => $skill->status->value,
        ]);

        return redirect()->route('coordinator.skills.index')->with(
            'status',
            "Skill '{$skill->name}' added to catalog successfully."
        );
    }

    public function update(Request $request, Skill $skill): RedirectResponse
    {
        $this->authorize('update', $skill);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:skills,name,'.$skill->id],
            'category' => ['required', 'string', 'max:100'],
            'status' => ['required', new Enum(SkillStatus::class)],
        ]);

        $skill->update($validated);

        AuditService::log('skill.updated', $skill, $validated);

        return redirect()->route('coordinator.skills.index')->with(
            'status',
            "Skill '{$skill->name}' updated successfully."
        );
    }

    public function approve(Skill $skill): RedirectResponse
    {
        $this->authorize('update', $skill);

        $skill->update(['status' => SkillStatus::ACTIVE]);

        AuditService::log('skill.approved', $skill, [
            'approved_by' => Auth::id(),
        ]);

        return redirect()->route('coordinator.skills.index')->with(
            'status',
            "Skill '{$skill->name}' approved and activated in catalog."
        );
    }

    public function destroy(Skill $skill): RedirectResponse
    {
        $this->authorize('delete', $skill);

        $skillName = $skill->name;
        $skill->delete();

        AuditService::log('skill.archived', $skill, ['name' => $skillName]);

        return redirect()->route('coordinator.skills.index')->with(
            'status',
            "Skill '{$skillName}' archived."
        );
    }
}
