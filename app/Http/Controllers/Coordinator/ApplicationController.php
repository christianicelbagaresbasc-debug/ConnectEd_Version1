<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Company;
use App\Models\Opportunity;
use App\Services\AuditService;
use App\Services\PlacementService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function __construct(
        protected PlacementService $placementService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Application::class);

        // Rule BR-20: Applicants are NOT ranked or scored. Ordered strictly by submission date.
        $query = Application::with([
            'studentProfile.user',
            'opportunity.company',
            'decidedBy',
        ])->latest('submitted_at');

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('company_id')) {
            $query->whereHas('opportunity', function ($q) use ($request) {
                $q->where('company_id', $request->input('company_id'));
            });
        }

        if ($request->filled('opportunity_id')) {
            $query->where('opportunity_id', $request->input('opportunity_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('studentProfile.user', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('studentProfile', function ($sq) use ($search) {
                    $sq->where('student_number', 'like', "%{$search}%");
                })->orWhereHas('opportunity', function ($oq) use ($search) {
                    $oq->where('title', 'like', "%{$search}%");
                });
            });
        }

        $applications = $query->paginate(15)->withQueryString();

        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $opportunities = Opportunity::orderBy('title')->get();

        $counts = [
            'all' => Application::count(),
            'pending' => Application::where('status', ApplicationStatus::PENDING)->count(),
            'under_review' => Application::where('status', ApplicationStatus::UNDER_REVIEW)->count(),
            'accepted' => Application::where('status', ApplicationStatus::ACCEPTED)->count(),
            'placed' => Application::where('status', ApplicationStatus::PLACED)->count(),
            'rejected' => Application::where('status', ApplicationStatus::REJECTED)->count(),
            'withdrawn' => Application::where('status', ApplicationStatus::WITHDRAWN)->count(),
            'declined' => Application::where('status', ApplicationStatus::DECLINED)->count(),
        ];

        return view('coordinator.applications.index', compact('applications', 'companies', 'opportunities', 'counts'));
    }

    public function show(Application $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'studentProfile.user',
            'studentProfile.skills',
            'studentProfile.certifications',
            'studentProfile.resume',
            'opportunity.company',
            'opportunity.skills',
            'decidedBy',
            'statusLogs.actor',
            'placement',
        ]);

        return view('coordinator.applications.show', compact('application'));
    }

    public function markUnderReview(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('decide', $application);

        try {
            $this->placementService->markUnderReview($application, Auth::user(), $request->input('remark'));

            AuditService::log(
                'application_marked_under_review',
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
        $this->authorize('decide', $application);

        $request->validate([
            'decision' => ['required', 'in:accepted,rejected'],
            'remark' => ['nullable', 'string', 'max:1000'],
            'auto_confirm' => ['nullable', 'boolean'],
        ]);

        $decision = ApplicationStatus::from($request->input('decision'));
        $autoConfirm = $request->boolean('auto_confirm');

        try {
            $this->placementService->decideApplication(
                application: $application,
                decision: $decision,
                remark: $request->input('remark'),
                actor: Auth::user(),
                autoConfirmOc: $autoConfirm
            );

            AuditService::log(
                "application_{$decision->value}",
                $application,
                ['status' => $decision->value, 'remark' => $request->input('remark')]
            );

            $msg = $decision === ApplicationStatus::ACCEPTED
                ? 'Offer extended to student successfully.'
                : 'Application rejected.';

            return back()->with('success', $msg);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function confirm(Application $application): RedirectResponse
    {
        $this->authorize('coordinatorConfirm', $application);

        try {
            $this->placementService->confirmByCoordinator($application, Auth::user());

            AuditService::log(
                'application_coordinator_confirmed',
                $application,
                ['oc_confirmed_at' => (string) now()]
            );

            return back()->with('success', 'Coordinator placement confirmation recorded.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
