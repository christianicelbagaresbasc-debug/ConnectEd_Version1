<?php

namespace App\Http\Controllers\PartnerCompany;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\AuditService;
use App\Services\PlacementService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CompanyApplicationController extends Controller
{
    public function __construct(
        protected PlacementService $placementService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Application::class);

        $user = Auth::user();
        $companyId = $user->partnerAccount?->company_id;

        // Rule BR-20: Applicants are NOT ranked or scored. Ordered strictly by submission date.
        $query = Application::with([
            'studentProfile.user',
            'opportunity',
        ])
            ->whereHas('opportunity', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->latest('submitted_at');

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('opportunity_id')) {
            $query->where('opportunity_id', $request->input('opportunity_id'));
        }

        $applications = $query->paginate(15)->withQueryString();

        return view('company.applications.index', compact('applications'));
    }

    public function show(Application $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'studentProfile.user',
            'studentProfile.skills',
            'studentProfile.certifications',
            'studentProfile.resume',
            'opportunity',
            'statusLogs.actor',
        ]);

        return view('company.applications.show', compact('application'));
    }

    public function markUnderReview(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('view', $application);

        try {
            $this->placementService->markUnderReview($application, Auth::user(), $request->input('remark'));

            AuditService::log(
                'company_application_marked_under_review',
                $application,
                ['status' => ApplicationStatus::UNDER_REVIEW->value]
            );

            return back()->with('success', 'Application marked as under review.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function decide(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('view', $application);

        $request->validate([
            'decision' => ['required', 'in:accepted,rejected'],
            'remark' => ['nullable', 'string', 'max:1000'],
        ]);

        $decision = ApplicationStatus::from($request->input('decision'));

        try {
            $this->placementService->decideApplication(
                application: $application,
                decision: $decision,
                remark: $request->input('remark'),
                actor: Auth::user()
            );

            AuditService::log(
                "company_application_{$decision->value}",
                $application,
                ['decision' => $decision->value, 'remark' => $request->input('remark')]
            );

            return back()->with('success', 'Application decision recorded successfully.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
