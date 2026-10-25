<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\WeeklyReportStatus;
use App\Http\Controllers\Controller;
use App\Models\WeeklyReport;
use App\Notifications\ConnectEdNotification;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeeklyReportReviewController extends Controller
{
    public function review(Request $request, WeeklyReport $weeklyReport): RedirectResponse
    {
        $this->authorize('review', $weeklyReport);

        $request->validate([
            'status' => ['required', 'in:reviewed,flagged'],
            'remark' => ['required_if:status,flagged', 'nullable', 'string', 'max:1000'],
        ]);

        $status = WeeklyReportStatus::from($request->input('status'));
        $oldStatus = $weeklyReport->status->value;

        $weeklyReport->update([
            'status' => $status,
            'reviewed_by' => Auth::id(),
            'remark' => $request->input('remark'),
        ]);

        AuditService::log(
            "weekly_report_{$status->value}",
            $weeklyReport,
            ['status' => $status->value, 'remark' => $request->input('remark')]
        );

        // Notify student
        $studentUser = $weeklyReport->placement->studentProfile->user;
        $studentUser->notify(new ConnectEdNotification(
            title: 'Weekly Report '.($status === WeeklyReportStatus::REVIEWED ? 'Approved' : 'Flagged'),
            body: "Your weekly report for week of {$weeklyReport->week_start->format('M d, Y')} was {$status->label()}.".($request->input('remark') ? " Remark: {$request->input('remark')}" : ''),
            actionUrl: route('student.placements.show', $weeklyReport->placement_id),
            type: 'weekly_report_reviewed'
        ));

        return back()->with('success', "Weekly report {$status->label()} successfully.");
    }
}
