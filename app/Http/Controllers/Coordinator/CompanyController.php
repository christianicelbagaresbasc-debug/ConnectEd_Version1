<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\CompanyClassification;
use App\Enums\CompanySource;
use App\Enums\CompanyVerificationStatus;
use App\Enums\VerificationItemStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyVerificationItem;
use App\Models\CompanyVerificationLog;
use App\Models\VerificationRequirement;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Company::class);

        $query = Company::with(['promotedBy'])->latest();

        // Search Keyword (Name, Industry, City)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('industry', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Classification Filter
        if ($request->filled('classification')) {
            $query->where('classification', $request->input('classification'));
        }

        // Verification Status Filter
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->input('verification_status'));
        }

        // Active State Filter
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $companies = $query->paginate(12)->withQueryString();

        return view('coordinator.companies.index', compact('companies'));
    }

    public function create(): View
    {
        $this->authorize('create', Company::class);

        return view('coordinator.companies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Company::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:companies,name'],
            'industry' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'string', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string'],
            'bsit_relevance_note' => ['nullable', 'string'],
            'classification' => ['nullable', new Enum(CompanyClassification::class)],
        ]);

        $company = DB::transaction(function () use ($validated) {
            $company = Company::create([
                'name' => $validated['name'],
                'classification' => $validated['classification'] ?? CompanyClassification::NEW_COMPANY,
                'verification_status' => CompanyVerificationStatus::PENDING,
                'is_active' => true,
                'industry' => $validated['industry'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'contact_person' => $validated['contact_person'] ?? null,
                'contact_email' => $validated['contact_email'] ?? null,
                'contact_phone' => $validated['contact_phone'] ?? null,
                'website' => $validated['website'] ?? null,
                'description' => $validated['description'] ?? null,
                'bsit_relevance_note' => $validated['bsit_relevance_note'] ?? null,
                'source' => CompanySource::OC_CREATED,
            ]);

            // Auto-populate checklist items for all active requirements
            $requirements = VerificationRequirement::where('is_active', true)->get();
            foreach ($requirements as $req) {
                CompanyVerificationItem::create([
                    'company_id' => $company->id,
                    'verification_requirement_id' => $req->id,
                    'status' => VerificationItemStatus::MISSING,
                ]);
            }

            CompanyVerificationLog::create([
                'company_id' => $company->id,
                'from_status' => null,
                'to_status' => CompanyVerificationStatus::PENDING->value,
                'remark' => 'Initial record created by OJT Coordinator.',
                'actor_id' => Auth::id(),
                'created_at' => now(),
            ]);

            AuditService::log('company.created', $company, [
                'name' => $company->name,
                'classification' => $company->classification->value,
                'verification_status' => $company->verification_status->value,
            ]);

            return $company;
        });

        return redirect()->route('coordinator.companies.show', $company)->with(
            'status',
            "Company Record for '{$company->name}' created successfully with verification checklist initialized."
        );
    }

    public function show(Company $company): View
    {
        $this->authorize('view', $company);

        $company->load([
            'promotedBy',
            'partnerAccounts.user',
            'verificationItems.requirement',
            'verificationItems.document',
            'verificationLogs.actor',
        ]);

        return view('coordinator.companies.show', compact('company'));
    }

    public function edit(Company $company): View
    {
        $this->authorize('update', $company);

        return view('coordinator.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:companies,name,'.$company->id],
            'industry' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'string', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string'],
            'bsit_relevance_note' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $company->update($validated);

        AuditService::log('company.updated', $company, $validated);

        return redirect()->route('coordinator.companies.show', $company)->with(
            'status',
            "Company '{$company->name}' updated successfully."
        );
    }

    public function destroy(Company $company): RedirectResponse
    {
        $this->authorize('delete', $company);

        $name = $company->name;
        $company->delete(); // Soft delete

        AuditService::log('company.archived', $company, ['name' => $name]);

        return redirect()->route('coordinator.companies.index')->with(
            'status',
            "Company '{$name}' has been soft-deleted and moved to the Archive Center."
        );
    }

    /**
     * Promote a New Company to Existing Partner (BR-17)
     * Strictly human OC decision; requires verified status.
     */
    public function promote(Company $company): RedirectResponse
    {
        $this->authorize('promote', $company);

        if (! $company->canBePromoted()) {
            return back()->with('error', 'Only verified New Companies can be promoted to Existing Partner.');
        }

        $company->update([
            'classification' => CompanyClassification::EXISTING_PARTNER,
            'promoted_by' => Auth::id(),
            'promoted_at' => now(),
        ]);

        AuditService::log('company.promoted', $company, [
            'from_classification' => CompanyClassification::NEW_COMPANY->value,
            'to_classification' => CompanyClassification::EXISTING_PARTNER->value,
            'promoted_by' => Auth::id(),
        ]);

        return back()->with(
            'status',
            "Company '{$company->name}' has been promoted to Existing Partner status."
        );
    }
}
