<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Opportunity;
use App\Services\AuditService;
use App\Services\PlacementService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentApplicationController extends Controller
{
    public function __construct(
        protected PlacementService $placementService
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Application::class);

        $user = Auth::user();
        $studentProfile = $user->studentProfile;

        $applications = Application::with([
            'opportunity.company',
            'statusLogs',
            'placement',
        ])
            ->where('student_profile_id', $studentProfile->id)
            ->latest('submitted_at')
            ->paginate(10);

        return view('student.applications.index', compact('applications', 'studentProfile'));
    }

    public function store(Request $request, Opportunity $opportunity): RedirectResponse
    {
        $this->authorize('create', Application::class);

        $request->validate([
            'cover_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $studentProfile = Auth::user()->studentProfile;

        try {
            $application = $this->placementService->submitApplication(
                studentProfile: $studentProfile,
                opportunity: $opportunity,
                coverNote: $request->input('cover_note')
            );

            AuditService::log(
                'application_submitted',
                $application,
                [
                    'opportunity_id' => $opportunity->id,
                    'company_id' => $opportunity->company_id,
                ]
            );

            return redirect()->route('student.applications.index')
                ->with('success', 'Your application has been submitted successfully.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(Application $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'opportunity.company',
            'opportunity.skills',
            'statusLogs.actor',
            'decidedBy',
            'placement',
        ]);

        return view('student.applications.show', compact('application'));
    }

    public function confirm(Application $application): RedirectResponse
    {
        $this->authorize('studentConfirm', $application);

        try {
            $this->placementService->confirmByStudent($application, Auth::user());

            AuditService::log(
                'application_student_confirmed',
                $application,
                ['student_confirmed_at' => (string) now()]
            );

            return back()->with('success', 'You have confirmed your acceptance of this offer.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function decline(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('studentDecline', $application);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->placementService->declineByStudent($application, Auth::user(), $request->input('reason'));

            AuditService::log(
                'application_student_declined',
                $application,
                ['reason' => $request->input('reason')]
            );

            return redirect()->route('student.applications.index')
                ->with('info', 'You have declined this offer.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function withdraw(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('withdraw', $application);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->placementService->withdrawByStudent($application, Auth::user(), $request->input('reason'));

            AuditService::log(
                'application_student_withdrawn',
                $application,
                ['reason' => $request->input('reason')]
            );

            return redirect()->route('student.applications.index')
                ->with('info', 'Your application has been withdrawn.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
