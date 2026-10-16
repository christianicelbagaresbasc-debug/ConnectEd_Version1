<?php

namespace App\Http\Controllers;

use App\Enums\CompanyClassification;
use App\Enums\MoaCustodianParty;
use App\Enums\MoaStatus;
use App\Enums\MoaType;
use App\Models\Company;
use App\Models\Moa;
use App\Models\MoaTransfer;
use App\Models\MoaVersion;
use App\Models\StudentProfile;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class MoaController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Moa::class);

        $user = Auth::user();
        $query = Moa::with([
            'company',
            'studentProfile.user',
            'currentCustodianUser',
            'latestVersion.document',
        ])->latest();

        // Scope for Student (their own student-specific or active umbrella MOAs)
        if ($user->isStudent()) {
            $studentProfileId = $user->studentProfile?->id;
            $query->where(function ($q) use ($studentProfileId) {
                if ($studentProfileId) {
                    $q->where('student_profile_id', $studentProfileId);
                }
                $q->orWhere(function ($sub) {
                    $sub->where('type', MoaType::UNIVERSITY_COMPANY)
                        ->where('status', MoaStatus::ACTIVE);
                });
            });
        }

        // Scope for Partner Company
        if ($user->isPc()) {
            $companyId = $user->partnerAccount?->company_id;
            $query->where('company_id', $companyId);
        }

        // Search Filter (Company name, MOA number, summary)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('moa_number', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhereHas('company', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Type Filter
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Custodian Party Filter
        if ($request->filled('custodian_party')) {
            $query->where('current_custodian_party', $request->input('custodian_party'));
        }

        $moas = $query->paginate(12)->withQueryString();

        return view('moas.index', compact('moas'));
    }

    public function create(): View
    {
        $this->authorize('create', Moa::class);

        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $students = StudentProfile::with('user')->get();

        return view('moas.create', compact('companies', 'students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Moa::class);

        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'type' => ['required', new Enum(MoaType::class)],
            'student_profile_id' => ['nullable', 'required_if:type,student_company', 'exists:student_profiles,id'],
            'moa_number' => ['nullable', 'string', 'max:100', 'unique:moas,moa_number'],
            'effective_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:effective_date'],
            'summary' => ['nullable', 'string'],
            'initial_custodian_party' => ['nullable', new Enum(MoaCustodianParty::class)],
            'current_custodian_party' => ['nullable', new Enum(MoaCustodianParty::class)],
            'initial_custodian_user_id' => ['nullable', 'exists:users,id'],
            'document_id' => ['nullable', 'exists:documents,id'],
            'custody_note' => ['nullable', 'string', 'max:500'],
        ]);

        $initialParty = $validated['initial_custodian_party'] 
            ?? $validated['current_custodian_party'] 
            ?? MoaCustodianParty::OC->value;

        $moa = DB::transaction(function () use ($validated, $initialParty) {
            $moa = Moa::create([
                'company_id' => $validated['company_id'],
                'student_profile_id' => $validated['type'] === MoaType::STUDENT_COMPANY->value ? ($validated['student_profile_id'] ?? null) : null,
                'moa_number' => $validated['moa_number'] ?? null,
                'type' => $validated['type'],
                'status' => MoaStatus::DRAFT,
                'current_custodian_party' => $initialParty,
                'current_custodian_user_id' => $validated['initial_custodian_user_id'] ?? Auth::id(),
                'effective_date' => $validated['effective_date'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'summary' => $validated['summary'] ?? null,
                'school_year' => config('connected.current_school_year', '2026-2027'),
                'created_by' => Auth::id(),
            ]);

            // Append Initial Custody Log (BR-16)
            MoaTransfer::create([
                'moa_id' => $moa->id,
                'from_party' => null,
                'to_party' => $initialParty,
                'from_user_id' => null,
                'to_user_id' => $validated['initial_custodian_user_id'] ?? Auth::id(),
                'actor_id' => Auth::id(),
                'note' => $validated['custody_note'] ?? 'Initial MOA record drafted and custody assigned.',
                'created_at' => now(),
            ]);

            // Optional: attach initial document version
            if (!empty($validated['document_id'])) {
                MoaVersion::create([
                    'moa_id' => $moa->id,
                    'version_no' => 1,
                    'document_id' => $validated['document_id'],
                    'uploaded_by' => Auth::id(),
                    'note' => 'Initial draft uploaded at record creation.',
                ]);
            }

            AuditService::log('moa.created', $moa, [
                'company_id' => $moa->company_id,
                'type' => $moa->type->value,
                'status' => $moa->status->value,
                'initial_custodian' => $moa->current_custodian_party->value,
            ]);

            return $moa;
        });

        return redirect()->route('moas.show', $moa)->with(
            'status',
            "MOA record created successfully with initial custody assigned to {$moa->current_custodian_party->label()}."
        );
    }

    public function show(Moa $moa): View
    {
        $this->authorize('view', $moa);

        $relations = [
            'company',
            'studentProfile.user',
            'currentCustodianUser',
            'creator',
            'versions.document',
            'versions.uploader',
            'transfers.actor',
            'transfers.fromUser',
            'transfers.toUser',
        ];

        // Rule BR-18: Legal review notes internal to university staff (OC, COC, UA)
        if (Auth::user()->can('viewInternalNotes', $moa)) {
            $relations[] = 'notes.author';
            $relations[] = 'notes.resolver';
        }

        $moa->load($relations);

        return view('moas.show', compact('moa'));
    }

    public function edit(Moa $moa): View
    {
        $this->authorize('update', $moa);

        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('moas.edit', compact('moa', 'companies'));
    }

    public function update(Request $request, Moa $moa): RedirectResponse
    {
        $this->authorize('update', $moa);

        $validated = $request->validate([
            'moa_number' => ['nullable', 'string', 'max:100'],
            'effective_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:effective_date'],
            'summary' => ['nullable', 'string'],
        ]);

        $moa->update($validated);

        AuditService::log('moa.updated', $moa, $validated);

        return redirect()->route('moas.show', $moa)->with('status', 'MOA details updated successfully.');
    }

    public function updateStatus(Request $request, Moa $moa): RedirectResponse
    {
        $this->authorize('update', $moa);

        $validated = $request->validate([
            'status' => ['required', new Enum(MoaStatus::class)],
            'remark' => ['nullable', 'string', 'max:500'],
        ]);

        $fromStatus = $moa->status->value;
        $toStatus = $validated['status'];

        $moa->update(['status' => $toStatus]);

        AuditService::log('moa.status_changed', $moa, [
            'from_status' => $fromStatus,
            'to_status' => $toStatus instanceof MoaStatus ? $toStatus->value : $toStatus,
            'remark' => $validated['remark'] ?? null,
        ]);

        return redirect()->route('moas.show', $moa)->with(
            'status',
            "MOA status changed to " . ($toStatus instanceof MoaStatus ? $toStatus->label() : $toStatus) . "."
        );
    }

    public function destroy(Moa $moa): RedirectResponse
    {
        $this->authorize('delete', $moa);

        $moaNumber = $moa->moa_number ?: "MOA #{$moa->id}";
        $moa->delete();

        AuditService::log('moa.archived', $moa, ['moa_number' => $moaNumber]);

        return redirect()->route('moas.index')->with(
            'status',
            "MOA '{$moaNumber}' has been soft-deleted and moved to the Archive Center."
        );
    }
}
