<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Document;
use App\Models\StudentProfile;
use App\Services\AuditService;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class DocumentLibraryController extends Controller
{
    /**
     * Centralized Document Library with Role-Scoped Filtering (FR-27).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = Document::with(['owner', 'uploader'])->latest();

        // Role-based document scoping
        if ($user->role === UserRole::STUDENT) {
            $query->where(function ($q) use ($user) {
                $q->where('owner_user_id', $user->id)
                    ->orWhere('uploaded_by', $user->id)
                    ->orWhere('category', 'template');
            });
        } elseif ($user->role === UserRole::COC) {
            $assignedStudentUserIds = $user->assignedStudents()->pluck('user_id')->all();
            $query->where(function ($q) use ($user, $assignedStudentUserIds) {
                $q->where('owner_user_id', $user->id)
                    ->orWhere('uploaded_by', $user->id)
                    ->orWhereIn('owner_user_id', $assignedStudentUserIds)
                    ->orWhereIn('category', ['verification', 'template', 'authorization']);
            });
        } elseif ($user->role === UserRole::UA) {
            $query->whereIn('category', ['moa', 'verification', 'template', 'authorization']);
        } elseif ($user->role === UserRole::PC) {
            $companyId = $user->partnerAccount?->company_id;
            $query->where(function ($q) use ($user, $companyId) {
                $q->where('owner_user_id', $user->id)
                    ->orWhere('uploaded_by', $user->id)
                    ->orWhere(function ($cq) use ($companyId) {
                        $cq->where('category', 'verification')
                            ->where('documentable_id', $companyId);
                    })
                    ->orWhereIn('category', ['resume', 'certification', 'moa', 'weekly_report', 'template']);
            });
        }
        // OC sees all documents

        // Category filter
        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        // Validation status filter
        if ($request->filled('validation_status') && $request->input('validation_status') !== 'all') {
            $query->where('validation_status', $request->input('validation_status'));
        }

        // Review status filter
        if ($request->filled('review_status') && $request->input('review_status') !== 'all') {
            $query->where('review_status', $request->input('review_status'));
        }

        // Search original name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('original_name', 'like', "%{$search}%");
        }

        $documents = $query->paginate(15)->withQueryString();

        // Role-scoped base query for category counts
        $countQuery = Document::query();
        if ($user->role === UserRole::STUDENT) {
            $countQuery->where(function ($q) use ($user) {
                $q->where('owner_user_id', $user->id)
                    ->orWhere('uploaded_by', $user->id)
                    ->orWhere('category', 'template');
            });
        }

        $counts = [
            'all' => (clone $countQuery)->count(),
            'resume' => (clone $countQuery)->where('category', 'resume')->count(),
            'certification' => (clone $countQuery)->where('category', 'certification')->count(),
            'moa' => (clone $countQuery)->where('category', 'moa')->count(),
            'weekly_report' => (clone $countQuery)->where('category', 'weekly_report')->count(),
            'verification' => (clone $countQuery)->where('category', 'verification')->count(),
            'template' => (clone $countQuery)->where('category', 'template')->count(),
        ];

        return view('documents.library', compact('documents', 'counts'));
    }

    /**
     * Document Completeness Matrix for Students and Partner Companies (FR-27).
     */
    public function completeness(Request $request): View
    {
        $user = Auth::user();
        if (! in_array($user->role, [UserRole::OC, UserRole::COC])) {
            abort(403, 'Institutional document completeness is reserved for coordinators.');
        }

        // Student compliance matrix
        $studentQuery = StudentProfile::with(['user', 'resume', 'assignedCoc'])
            ->whereHas('user', fn ($q) => $q->where('status', 'active'));

        if ($user->role === UserRole::COC) {
            $studentQuery->where('assigned_coc_id', $user->id);
        }

        $students = $studentQuery->orderBy('student_number')->paginate(20, ['*'], 'students_page');

        // Company compliance matrix
        $companies = Company::with(['verificationItems.requirement', 'activeMoa'])
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(15, ['*'], 'companies_page');

        return view('documents.completeness', compact('students', 'companies'));
    }

    /**
     * Authorized ZIP Bundle Download (FR-28, Assumption A19).
     * Enforces 50 files and 100 MB limits, streams ZIP, and logs audit event.
     */
    public function downloadBundle(Request $request): BinaryFileResponse|RedirectResponse
    {
        $user = Auth::user();

        $rawIds = $request->input('document_ids');
        if (is_array($rawIds) && count($rawIds) > 50) {
            return back()->withErrors([
                'bundle' => 'ZIP bundle download is limited to a maximum of 50 files per batch. Please filter your selection.',
            ]);
        }

        $validated = $request->validate([
            'document_ids' => ['required', 'array', 'min:1'],
            'document_ids.*' => ['required', 'exists:documents,id'],
        ]);

        $docIds = $validated['document_ids'];

        $documents = Document::whereIn('id', $docIds)->get();

        // Verify authorization for every document in selection
        $totalBytes = 0;
        foreach ($documents as $doc) {
            if (! DocumentService::canDownload($user, $doc)) {
                abort(403, "Unauthorized to download file: {$doc->original_name}");
            }
            $totalBytes += $doc->size_bytes;
        }

        // Enforce max 100 MB limit (Assumption A19)
        $maxBytes = 100 * 1024 * 1024;
        if ($totalBytes > $maxBytes) {
            return back()->withErrors([
                'bundle' => 'The selected files total '.round($totalBytes / (1024 * 1024), 1).' MB, exceeding the 100 MB batch limit. Please reduce your selection.',
            ]);
        }

        // Prepare temporary directory
        $tempDir = storage_path('app/temp');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipFileName = 'connected_bundle_'.now()->format('Ymd_His').'_'.Str::random(6).'.zip';
        $zipPath = $tempDir.'/'.$zipFileName;

        $zip = new ZipArchive;
        $openResult = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($openResult !== true) {
            return back()->withErrors(['bundle' => 'Failed to initialize ZIP archive container.']);
        }

        $addedCount = 0;
        foreach ($documents as $doc) {
            $disk = Storage::disk($doc->disk);
            if ($disk->exists($doc->stored_path)) {
                $contents = $disk->get($doc->stored_path);
                $cleanBase = Str::slug(pathinfo($doc->original_name, PATHINFO_FILENAME));
                $ext = pathinfo($doc->original_name, PATHINFO_EXTENSION);
                $entryName = "{$doc->id}_{$cleanBase}.{$ext}";
                $zip->addFromString($entryName, $contents);
                $addedCount++;
            }
        }

        $zip->close();

        if ($addedCount === 0) {
            if (file_exists($zipPath)) {
                @unlink($zipPath);
            }

            return back()->withErrors(['bundle' => 'Selected files were not located in storage.']);
        }

        AuditService::log('document.bundle_downloaded', null, [
            'document_count' => $addedCount,
            'total_bytes' => $totalBytes,
            'document_ids' => $documents->pluck('id')->all(),
        ]);

        $downloadName = 'ConnectEd_Documents_Bundle_'.now()->format('Ymd_His').'.zip';

        return response()->download($zipPath, $downloadName)->deleteFileAfterSend(true);
    }
}
