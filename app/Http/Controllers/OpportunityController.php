<?php

namespace App\Http\Controllers;

use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\OpportunityRequirementLevel;
use App\Enums\OpportunityStatus;
use App\Enums\OpportunityWorkArrangement;
use App\Enums\SkillStatus;
use App\Models\Company;
use App\Models\Opportunity;
use App\Models\OpportunitySkill;
use App\Models\Skill;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class OpportunityController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Opportunity::class);

        $user = Auth::user();
        $query = Opportunity::with(['company', 'skills'])->latest();

        // Role-based visibility scoping
        if ($user->isStudent()) {
            $query->open()->forVerifiedPartner();
        } elseif ($user->isPc()) {
            $companyId = $user->partnerAccount?->company_id;
            $query->where('company_id', $companyId);
        }

        // Multi-Criteria Filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('company', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('work_arrangement')) {
            $query->where('work_arrangement', $request->input('work_arrangement'));
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%'.$request->input('location').'%');
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        if ($request->filled('status') && ! $user->isStudent()) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('skill_id')) {
            $skillId = $request->input('skill_id');
            $query->whereHas('skills', fn ($sq) => $sq->where('skills.id', $skillId));
        }

        $opportunities = $query->paginate(12)->withQueryString();

        $companies = Company::where('is_active', true)
            ->where('classification', CompanyClassification::EXISTING_PARTNER)
            ->where('verification_status', CompanyVerificationStatus::VERIFIED)
            ->orderBy('name')
            ->get();

        $skills = Skill::where('status', SkillStatus::ACTIVE)->orderBy('name')->get();

        return view('opportunities.index', compact('opportunities', 'companies', 'skills'));
    }

    public function create(): View
    {
        $this->authorize('create', Opportunity::class);

        $user = Auth::user();
        $companies = collect();

        if ($user->isPc()) {
            $comp = $user->partnerAccount?->company;
            if ($comp) {
                $companies = collect([$comp]);
            }
        } else {
            $companies = Company::where('is_active', true)
                ->where('classification', CompanyClassification::EXISTING_PARTNER)
                ->where('verification_status', CompanyVerificationStatus::VERIFIED)
                ->orderBy('name')
                ->get();
        }

        $skills = Skill::where('status', SkillStatus::ACTIVE)->orderBy('category')->orderBy('name')->get();

        return view('opportunities.create', compact('companies', 'skills'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Opportunity::class);

        $user = Auth::user();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'qualifications' => ['nullable', 'string'],
            'slots_total' => ['required', 'integer', 'min:1', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'work_arrangement' => ['required', new Enum(OpportunityWorkArrangement::class)],
            'deadline' => ['nullable', 'date', 'after_or_equal:today'],
            'required_skills' => ['nullable', 'array'],
            'required_skills.*' => ['exists:skills,id'],
            'preferred_skills' => ['nullable', 'array'],
            'preferred_skills.*' => ['exists:skills,id'],
        ];

        if ($user->isPc()) {
            $companyId = $user->partnerAccount?->company_id;
            if (! $companyId) {
                abort(403, 'Partner company not linked.');
            }
        } else {
            $rules['company_id'] = ['required', 'exists:companies,id'];
            $companyId = $request->input('company_id');
        }

        $validated = $request->validate($rules);

        // Status determination (PC requires approval per Assumption A5)
        $requiresApproval = config('connected.opportunity_requires_approval', true);
        if ($user->isPc()) {
            $initialStatus = $requiresApproval ? OpportunityStatus::PENDING_APPROVAL : OpportunityStatus::OPEN;
        } else {
            $initialStatus = $request->input('status') === OpportunityStatus::DRAFT->value
                ? OpportunityStatus::DRAFT
                : OpportunityStatus::OPEN;
        }

        $opportunity = DB::transaction(function () use ($validated, $companyId, $initialStatus, $user) {
            $opp = Opportunity::create([
                'company_id' => $companyId,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'qualifications' => $validated['qualifications'] ?? null,
                'slots_total' => $validated['slots_total'],
                'slots_filled' => 0,
                'location' => $validated['location'] ?? null,
                'work_arrangement' => $validated['work_arrangement'],
                'deadline' => $validated['deadline'] ?? null,
                'status' => $initialStatus,
                'created_by' => $user->id,
                'approved_by' => $initialStatus === OpportunityStatus::OPEN && $user->isOc() ? $user->id : null,
                'approved_at' => $initialStatus === OpportunityStatus::OPEN && $user->isOc() ? now() : null,
                'school_year' => config('connected.current_school_year', '2026-2027'),
            ]);

            // Attach required skills
            if (! empty($validated['required_skills'])) {
                foreach (array_unique($validated['required_skills']) as $skillId) {
                    OpportunitySkill::create([
                        'opportunity_id' => $opp->id,
                        'skill_id' => $skillId,
                        'requirement_level' => OpportunityRequirementLevel::REQUIRED,
                    ]);
                }
            }

            // Attach preferred skills
            if (! empty($validated['preferred_skills'])) {
                $existing = $opp->opportunitySkills()->pluck('skill_id')->all();
                foreach (array_unique($validated['preferred_skills']) as $skillId) {
                    if (! in_array($skillId, $existing)) {
                        OpportunitySkill::create([
                            'opportunity_id' => $opp->id,
                            'skill_id' => $skillId,
                            'requirement_level' => OpportunityRequirementLevel::PREFERRED,
                        ]);
                    }
                }
            }

            AuditService::log('opportunity.created', $opp, [
                'title' => $opp->title,
                'company_id' => $opp->company_id,
                'status' => $opp->status->value,
                'slots_total' => $opp->slots_total,
            ]);

            return $opp;
        });

        $msg = $opportunity->status === OpportunityStatus::PENDING_APPROVAL
            ? "Opportunity '{$opportunity->title}' submitted for coordinator verification."
            : "Opportunity '{$opportunity->title}' published successfully.";

        return redirect()->route('opportunities.show', $opportunity)->with('status', $msg);
    }

    public function show(Opportunity $opportunity): View
    {
        $this->authorize('view', $opportunity);

        $opportunity->load([
            'company',
            'creator',
            'approver',
            'opportunitySkills.skill',
        ]);

        return view('opportunities.show', compact('opportunity'));
    }

    public function edit(Opportunity $opportunity): View
    {
        $this->authorize('update', $opportunity);

        $user = Auth::user();
        $companies = $user->isPc()
            ? collect([$opportunity->company])
            : Company::where('is_active', true)->orderBy('name')->get();

        $skills = Skill::where('status', SkillStatus::ACTIVE)->orderBy('category')->orderBy('name')->get();
        $currentRequired = $opportunity->requiredSkills()->pluck('skills.id')->all();
        $currentPreferred = $opportunity->preferredSkills()->pluck('skills.id')->all();

        return view('opportunities.edit', compact('opportunity', 'companies', 'skills', 'currentRequired', 'currentPreferred'));
    }

    public function update(Request $request, Opportunity $opportunity): RedirectResponse
    {
        $this->authorize('update', $opportunity);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'qualifications' => ['nullable', 'string'],
            'slots_total' => ['required', 'integer', 'min:1', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'work_arrangement' => ['required', new Enum(OpportunityWorkArrangement::class)],
            'deadline' => ['nullable', 'date'],
            'status' => ['nullable', new Enum(OpportunityStatus::class)],
            'required_skills' => ['nullable', 'array'],
            'required_skills.*' => ['exists:skills,id'],
            'preferred_skills' => ['nullable', 'array'],
            'preferred_skills.*' => ['exists:skills,id'],
        ]);

        DB::transaction(function () use ($opportunity, $validated) {
            $opportunity->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'qualifications' => $validated['qualifications'] ?? null,
                'slots_total' => $validated['slots_total'],
                'location' => $validated['location'] ?? null,
                'work_arrangement' => $validated['work_arrangement'],
                'deadline' => $validated['deadline'] ?? null,
                'status' => $validated['status'] ?? $opportunity->status,
            ]);

            // Sync skills
            $opportunity->opportunitySkills()->delete();

            if (! empty($validated['required_skills'])) {
                foreach (array_unique($validated['required_skills']) as $skillId) {
                    OpportunitySkill::create([
                        'opportunity_id' => $opportunity->id,
                        'skill_id' => $skillId,
                        'requirement_level' => OpportunityRequirementLevel::REQUIRED,
                    ]);
                }
            }

            if (! empty($validated['preferred_skills'])) {
                $existing = $opportunity->opportunitySkills()->pluck('skill_id')->all();
                foreach (array_unique($validated['preferred_skills']) as $skillId) {
                    if (! in_array($skillId, $existing)) {
                        OpportunitySkill::create([
                            'opportunity_id' => $opportunity->id,
                            'skill_id' => $skillId,
                            'requirement_level' => OpportunityRequirementLevel::PREFERRED,
                        ]);
                    }
                }
            }

            AuditService::log('opportunity.updated', $opportunity, [
                'title' => $opportunity->title,
                'slots_total' => $opportunity->slots_total,
            ]);
        });

        return redirect()->route('opportunities.show', $opportunity)->with('status', 'Opportunity details updated successfully.');
    }

    public function approve(Opportunity $opportunity): RedirectResponse
    {
        $this->authorize('approve', $opportunity);

        $opportunity->update([
            'status' => OpportunityStatus::OPEN,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        AuditService::log('opportunity.approved', $opportunity, [
            'approved_by' => Auth::id(),
        ]);

        return redirect()->route('opportunities.show', $opportunity)->with(
            'status',
            "Opportunity '{$opportunity->title}' approved and published to students."
        );
    }

    public function destroy(Opportunity $opportunity): RedirectResponse
    {
        $this->authorize('delete', $opportunity);

        $title = $opportunity->title;
        $opportunity->delete();

        AuditService::log('opportunity.archived', $opportunity, ['title' => $title]);

        return redirect()->route('opportunities.index')->with(
            'status',
            "Opportunity '{$title}' moved to Archive Center."
        );
    }
}
