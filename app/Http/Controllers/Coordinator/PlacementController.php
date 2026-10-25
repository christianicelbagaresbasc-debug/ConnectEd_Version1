<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\PlacementStatus;
use App\Http\Controllers\Controller;
use App\Models\Placement;
use App\Services\AuditService;
use App\Services\PlacementService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class PlacementController extends Controller
{
    public function __construct(
        protected PlacementService $placementService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Placement::class);

        $query = Placement::with([
            'studentProfile.user',
            'company',
            'opportunity',
            'confirmedBy',
        ])->latest();

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('studentProfile.user', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })->orWhereHas('studentProfile', function ($sq) use ($search) {
                    $sq->where('student_number', 'like', "%{$search}%");
                })->orWhereHas('company', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%");
                })->orWhereHas('opportunity', function ($oq) use ($search) {
                    $oq->where('title', 'like', "%{$search}%");
                });
            });
        }

        $placements = $query->paginate(15)->withQueryString();

        $counts = [
            'all' => Placement::count(),
            'placed' => Placement::where('status', PlacementStatus::PLACED)->count(),
            'authorized' => Placement::where('status', PlacementStatus::AUTHORIZED)->count(),
            'ongoing' => Placement::where('status', PlacementStatus::ONGOING)->count(),
            'completed' => Placement::where('status', PlacementStatus::COMPLETED)->count(),
            'terminated' => Placement::where('status', PlacementStatus::TERMINATED)->count(),
        ];

        return view('coordinator.placements.index', compact('placements', 'counts'));
    }

    public function show(Placement $placement): View
    {
        $this->authorize('view', $placement);

        $placement->load([
            'studentProfile.user',
            'studentProfile.resume',
            'company',
            'opportunity',
            'application',
            'confirmedBy',
            'startAuthorizationDocument',
            'weeklyReports.document',
            'weeklyReports.reviewedBy',
        ]);

        return view('coordinator.placements.show', compact('placement'));
    }

    public function authorizeStart(Request $request, Placement $placement): RedirectResponse
    {
        $this->authorize('authorize', $placement);

        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'note' => ['nullable', 'string', 'max:1000'],
            'document_id' => ['nullable', 'exists:documents,id'],
        ]);

        try {
            $this->placementService->authorizeStart($placement, Auth::user(), $data);

            AuditService::log(
                'placement_start_authorized',
                $placement,
                $data
            );

            return back()->with('success', 'Internship start successfully authorized.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Placement $placement): RedirectResponse
    {
        $this->authorize('updateStatus', $placement);

        $request->validate([
            'status' => ['required', new Enum(PlacementStatus::class)],
            'termination_reason' => ['required_if:status,terminated', 'nullable', 'string', 'max:1000'],
            'remark' => ['nullable', 'string', 'max:1000'],
        ]);

        $newStatus = PlacementStatus::from($request->input('status'));
        $reason = $newStatus === PlacementStatus::TERMINATED
            ? $request->input('termination_reason')
            : $request->input('remark');

        try {
            $oldStatus = $placement->status->value;
            $this->placementService->updatePlacementStatus(
                placement: $placement,
                newStatus: $newStatus,
                reason: $reason,
                actor: Auth::user()
            );

            AuditService::log(
                "placement_status_{$newStatus->value}",
                $placement,
                ['status' => $newStatus->value, 'reason' => $reason]
            );

            return back()->with('success', "Placement status updated to {$newStatus->label()}.");
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
