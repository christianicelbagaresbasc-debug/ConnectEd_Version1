<?php

namespace App\Http\Controllers\Student;

use App\Enums\CompanySuggestionStatus;
use App\Http\Controllers\Controller;
use App\Models\CompanySuggestion;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentCompanySuggestionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', CompanySuggestion::class);

        $studentProfile = Auth::user()->studentProfile;
        if (! $studentProfile) {
            abort(403, 'Student profile not found.');
        }

        $suggestions = CompanySuggestion::where('student_profile_id', $studentProfile->id)
            ->with(['company'])
            ->latest()
            ->paginate(10);

        return view('student.suggestions.index', compact('suggestions'));
    }

    public function create(): View
    {
        $this->authorize('create', CompanySuggestion::class);

        return view('student.suggestions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CompanySuggestion::class);

        $studentProfile = Auth::user()->studentProfile;
        if (! $studentProfile) {
            abort(403, 'Student profile not found.');
        }

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_details' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $suggestion = CompanySuggestion::create([
            'student_profile_id' => $studentProfile->id,
            'company_name' => $validated['company_name'],
            'contact_details' => $validated['contact_details'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => CompanySuggestionStatus::SUBMITTED,
        ]);

        AuditService::log('company_suggestion.submitted', $suggestion, [
            'company_name' => $suggestion->company_name,
            'student_id' => Auth::id(),
        ]);

        return redirect()->route('student.suggestions.index')->with(
            'status',
            "Thank you! Your company suggestion for '{$suggestion->company_name}' has been submitted for OJT Coordinator review."
        );
    }
}
