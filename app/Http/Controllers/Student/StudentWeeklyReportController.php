<?php

namespace App\Http\Controllers\Student;

use App\Enums\UserRole;
use App\Enums\WeeklyReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Placement;
use App\Models\User;
use App\Models\WeeklyReport;
use App\Notifications\ConnectEdNotification;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentWeeklyReportController extends Controller
{
    public function store(Request $request, Placement $placement): RedirectResponse
    {
        $this->authorize('create', [WeeklyReport::class, $placement]);

        $request->validate([
            'week_start' => ['required', 'date'],
            'document_id' => ['nullable', 'exists:documents,id'],
            'report_file' => ['nullable', 'file', 'mimes:pdf,docx,png,jpg,jpeg', 'max:10240'],
        ]);

        $documentId = $request->input('document_id');

        if ($request->hasFile('report_file')) {
            $file = $request->file('report_file');
            $extension = $file->getClientOriginalExtension();
            $storageKey = 'weekly_reports/'.Str::uuid().'.'.$extension;
            Storage::disk('documents')->put($storageKey, file_get_contents($file->getRealPath()));

            $doc = Document::create([
                'category' => 'weekly_report',
                'disk' => 'documents',
                'storage_key' => $storageKey,
                'stored_path' => $storageKey,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size_bytes' => $file->getSize() ?: 0,
                'checksum_sha256' => hash_file('sha256', $file->getRealPath()),
                'owner_user_id' => Auth::id(),
                'uploaded_by' => Auth::id(),
                'documentable_type' => Placement::class,
                'documentable_id' => $placement->id,
                'validation_status' => 'ok',
            ]);
            $documentId = $doc->id;
        }

        if (! $documentId) {
            return back()->with('error', 'Please attach a valid accomplishment report document.');
        }

        $weekStart = Carbon::parse($request->input('week_start'))->startOfWeek()->toDateString();

        $report = WeeklyReport::create([
            'placement_id' => $placement->id,
            'week_start' => $weekStart,
            'document_id' => $documentId,
            'status' => WeeklyReportStatus::SUBMITTED,
        ]);

        AuditService::log(
            'weekly_report_submitted',
            $report,
            [
                'placement_id' => $placement->id,
                'week_start' => $weekStart,
            ]
        );

        // Notify coordinators
        $coordinators = User::whereIn('role', [UserRole::OC, UserRole::COC])->get();
        foreach ($coordinators as $coord) {
            $coord->notify(new ConnectEdNotification(
                title: 'Weekly Report Submitted',
                body: "Student {$placement->studentProfile->user->name} submitted a weekly report for week of {$weekStart}.",
                actionUrl: route('coordinator.placements.show', $placement),
                type: 'weekly_report_submitted'
            ));
        }

        return back()->with('success', 'Weekly report submitted successfully.');
    }
}
