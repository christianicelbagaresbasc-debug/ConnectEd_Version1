<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\CompanyClassification;
use App\Enums\CompanySource;
use App\Enums\CompanySuggestionStatus;
use App\Enums\CompanyVerificationStatus;
use App\Enums\VerificationItemStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySuggestion;
use App\Models\CompanyVerificationItem;
use App\Models\CompanyVerificationLog;
use App\Models\VerificationRequirement;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompanySuggestionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CompanySuggestion::class);

        $query = CompanySuggestion::with(['studentProfile.user', 'company', 'handler'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_details', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $suggestions = $query->paginate(15)->withQueryString();

        return view('coordinator.suggestions.index', compact('suggestions'));
    }

    /**
     * Convert student suggestion into a New Company record
     */
    public function convert(Request $request, CompanySuggestion $suggestion): RedirectResponse
    {
        $this->authorize('handle', $suggestion);

        if ($suggestion->status !== CompanySuggestionStatus::SUBMITTED) {
            return back()->with('error', 'Only submitted suggestions can be converted.');
        }

        $validated = $request->validate([
            'industry' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'string', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'bsit_relevance_note' => ['nullable', 'string'],
        ]);

        $company = DB::transaction(function () use ($suggestion, $validated) {
            $company = Company::create([
                'name' => $suggestion->company_name,
                'classification' => CompanyClassification::NEW_COMPANY,
                'verification_status' => CompanyVerificationStatus::PENDING,
                'is_active' => true,
                'industry' => $validated['industry'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'contact_person' => $validated['contact_person'] ?? null,
                'contact_email' => $validated['contact_email'] ?? null,
                'contact_phone' => $validated['contact_phone'] ?? null,
                'website' => $validated['website'] ?? null,
                'description' => $suggestion->notes,
                'bsit_relevance_note' => $validated['bsit_relevance_note'] ?? null,
                'source' => CompanySource::STUDENT_SUGGESTION,
            ]);

            // Auto-populate checklist items
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
                'remark' => "Converted from student suggestion #{$suggestion->id} submitted by student {$suggestion->studentProfile->user->name}.",
                'actor_id' => Auth::id(),
                'created_at' => now(),
            ]);

            $suggestion->update([
                'status' => CompanySuggestionStatus::CONVERTED,
                'company_id' => $company->id,
                'handled_by' => Auth::id(),
                'handled_at' => now(),
            ]);

            AuditService::log('company_suggestion.converted', $suggestion, [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'converted_by' => Auth::id(),
            ]);

            return $company;
        });

        return redirect()->route('coordinator.companies.show', $company)->with(
            'status',
            "Suggestion converted into New Company record for '{$company->name}'."
        );
    }

    /**
     * Dismiss student suggestion with remark
     */
    public function dismiss(Request $request, CompanySuggestion $suggestion): RedirectResponse
    {
        $this->authorize('handle', $suggestion);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $suggestion->update([
            'status' => CompanySuggestionStatus::DISMISSED,
            'notes' => $suggestion->notes.($validated['reason'] ? "\nDismissal Reason: ".$validated['reason'] : ''),
            'handled_by' => Auth::id(),
            'handled_at' => now(),
        ]);

        AuditService::log('company_suggestion.dismissed', $suggestion, [
            'reason' => $validated['reason'] ?? 'Not specified',
            'handled_by' => Auth::id(),
        ]);

        return back()->with('status', "Suggestion for '{$suggestion->company_name}' has been dismissed.");
    }
}
