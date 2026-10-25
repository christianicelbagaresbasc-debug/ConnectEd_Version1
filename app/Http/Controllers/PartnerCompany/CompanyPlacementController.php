<?php

namespace App\Http\Controllers\PartnerCompany;

use App\Http\Controllers\Controller;
use App\Models\Placement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CompanyPlacementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Placement::class);

        $user = Auth::user();
        $companyId = $user->partnerAccount?->company_id;

        $query = Placement::with([
            'studentProfile.user',
            'opportunity',
        ])
            ->where('company_id', $companyId)
            ->latest();

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $placements = $query->paginate(15)->withQueryString();

        return view('company.placements.index', compact('placements'));
    }

    public function show(Placement $placement): View
    {
        $this->authorize('view', $placement);

        $placement->load([
            'studentProfile.user',
            'opportunity',
            'weeklyReports.document',
        ]);

        return view('company.placements.show', compact('placement'));
    }
}
