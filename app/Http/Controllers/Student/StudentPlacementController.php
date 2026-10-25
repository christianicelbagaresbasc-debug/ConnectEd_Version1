<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Placement;
use Illuminate\View\View;

class StudentPlacementController extends Controller
{
    public function show(Placement $placement): View
    {
        $this->authorize('view', $placement);

        $placement->load([
            'company',
            'opportunity',
            'startAuthorizationDocument',
            'weeklyReports.document',
            'weeklyReports.reviewedBy',
        ]);

        return view('student.placements.show', compact('placement'));
    }
}
