<?php

namespace App\Http\Controllers;

use App\Enums\CompanyClassification;
use App\Enums\OpportunityRequirementLevel;
use App\Enums\PlacementStatus;
use App\Enums\UserRole;
use App\Enums\VerificationItemStatus;
use App\Models\Company;
use App\Models\Placement;
use App\Models\Skill;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Report Hub Dashboard (F-021).
     */
    public function index(): View
    {
        $this->authorizeCoordinator();

        $totalStudents = StudentProfile::whereHas('user', fn ($q) => $q->where('status', 'active'))->count();
        $placedStudents = StudentProfile::where('has_active_placement', true)->whereHas('user', fn ($q) => $q->where('status', 'active'))->count();
        $totalCompanies = Company::count();
        $existingPartners = Company::where('classification', CompanyClassification::EXISTING_PARTNER)->count();
        $totalSkills = Skill::count();

        return view('reports.index', compact(
            'totalStudents',
            'placedStudents',
            'totalCompanies',
            'existingPartners',
            'totalSkills'
        ));
    }

    /**
     * Placement Summary Report View (F-021).
     */
    public function placements(Request $request): View
    {
        $this->authorizeCoordinator();

        $query = Placement::with([
            'studentProfile.user',
            'company',
            'opportunity',
            'confirmedBy',
        ])->latest();

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('school_year') && $request->input('school_year') !== 'all') {
            $query->where('school_year', $request->input('school_year'));
        }

        $placements = $query->paginate(20)->withQueryString();

        $schoolYears = Placement::distinct()->whereNotNull('school_year')->pluck('school_year');

        return view('reports.placements', compact('placements', 'schoolYears'));
    }

    /**
     * Streaming RFC 4180 CSV Export for Placements.
     */
    public function exportPlacementsCsv(Request $request): StreamedResponse
    {
        $this->authorizeCoordinator();

        $filename = 'connected_placements_report_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($request) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($file, [
                'Student Number',
                'Student Name',
                'Degree Program',
                'Year Level',
                'Section',
                'Host Company',
                'Opportunity Title',
                'Placement Status',
                'Start Date',
                'End Date',
                'Authorized At',
                'Confirmed By',
                'School Year',
            ]);

            $query = Placement::with([
                'studentProfile.user',
                'company',
                'opportunity',
                'confirmedBy',
            ])->orderBy('id', 'desc');

            if ($request->filled('status') && $request->input('status') !== 'all') {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('school_year') && $request->input('school_year') !== 'all') {
                $query->where('school_year', $request->input('school_year'));
            }

            $query->chunk(100, function ($placements) use ($file) {
                foreach ($placements as $p) {
                    fputcsv($file, [
                        $p->studentProfile?->student_number ?? 'N/A',
                        $p->studentProfile?->user?->name ?? 'N/A',
                        $p->studentProfile?->program ?? 'N/A',
                        $p->studentProfile?->year_level ?? 'N/A',
                        $p->studentProfile?->section ?? 'N/A',
                        $p->company?->name ?? 'N/A',
                        $p->opportunity?->title ?? 'Manual Placement',
                        $p->status instanceof PlacementStatus ? $p->status->label() : ucfirst((string) $p->status),
                        $p->start_date ? $p->start_date->toDateString() : '',
                        $p->end_date ? $p->end_date->toDateString() : '',
                        $p->start_authorized_at ? $p->start_authorized_at->toDateTimeString() : '',
                        $p->confirmedBy?->name ?? 'System',
                        $p->school_year ?? '',
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Partner Companies Accreditation Report View (F-021).
     */
    public function companies(Request $request): View
    {
        $this->authorizeCoordinator();

        $query = Company::with(['verificationItems', 'activeMoa'])
            ->withCount(['placements' => fn ($q) => $q->where('is_active', true)]);

        if ($request->filled('classification') && $request->input('classification') !== 'all') {
            $query->where('classification', $request->input('classification'));
        }

        if ($request->filled('verification_status') && $request->input('verification_status') !== 'all') {
            $query->where('verification_status', $request->input('verification_status'));
        }

        $companies = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('reports.companies', compact('companies'));
    }

    /**
     * Streaming RFC 4180 CSV Export for Partner Companies.
     */
    public function exportCompaniesCsv(Request $request): StreamedResponse
    {
        $this->authorizeCoordinator();

        $filename = 'connected_companies_report_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($request) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'Company Name',
                'Classification',
                'Verification Status',
                'Industry Sector',
                'Contact Person',
                'Contact Email',
                'Contact Number',
                'Verified Checklist Items',
                'Total Checklist Items',
                'Active MOA Status',
                'Active Interns Placed',
            ]);

            $query = Company::with(['verificationItems', 'activeMoa'])
                ->withCount(['placements' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('name');

            if ($request->filled('classification') && $request->input('classification') !== 'all') {
                $query->where('classification', $request->input('classification'));
            }

            if ($request->filled('verification_status') && $request->input('verification_status') !== 'all') {
                $query->where('verification_status', $request->input('verification_status'));
            }

            $query->chunk(100, function ($companies) use ($file) {
                foreach ($companies as $c) {
                    $verifiedItems = $c->verificationItems->where('status', VerificationItemStatus::ACCEPTED)->count();
                    $totalItems = $c->verificationItems->count();

                    fputcsv($file, [
                        $c->name,
                        $c->classification->label(),
                        $c->verification_status->label(),
                        $c->industry_sector ?? 'Technology',
                        $c->contact_person ?? 'N/A',
                        $c->contact_email ?? 'N/A',
                        $c->contact_number ?? 'N/A',
                        $verifiedItems,
                        $totalItems,
                        $c->activeMoa->isNotEmpty() ? 'Active Executed MOA' : 'None',
                        $c->placements_count ?? 0,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Skill Distribution & Industry Demand Report View (F-021).
     */
    public function skills(Request $request): View
    {
        $this->authorizeCoordinator();

        $query = Skill::withCount([
            'studentSkills',
            'opportunitySkills as required_count' => fn ($q) => $q->where('requirement_level', OpportunityRequirementLevel::REQUIRED),
            'opportunitySkills as preferred_count' => fn ($q) => $q->where('requirement_level', OpportunityRequirementLevel::PREFERRED),
        ]);

        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        $skills = $query->orderBy('name')->paginate(20)->withQueryString();

        $skillCategories = Skill::distinct()->whereNotNull('category')->pluck('category');

        return view('reports.skills', compact('skills', 'skillCategories'));
    }

    /**
     * Streaming RFC 4180 CSV Export for Skill Distribution.
     */
    public function exportSkillsCsv(Request $request): StreamedResponse
    {
        $this->authorizeCoordinator();

        $filename = 'connected_skills_distribution_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($request) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'Skill Name',
                'Category',
                'Status',
                'Acquired by Students Count',
                'Demanded in Opportunities (Required)',
                'Demanded in Opportunities (Preferred)',
                'Total Industry Demand',
            ]);

            $query = Skill::withCount([
                'studentSkills',
                'opportunitySkills as required_count' => fn ($q) => $q->where('requirement_level', OpportunityRequirementLevel::REQUIRED),
                'opportunitySkills as preferred_count' => fn ($q) => $q->where('requirement_level', OpportunityRequirementLevel::PREFERRED),
            ])->orderBy('name');

            if ($request->filled('category') && $request->input('category') !== 'all') {
                $query->where('category', $request->input('category'));
            }

            $query->chunk(100, function ($skills) use ($file) {
                foreach ($skills as $s) {
                    $totalDemand = ($s->required_count ?? 0) + ($s->preferred_count ?? 0);
                    fputcsv($file, [
                        $s->name,
                        $s->category ?? 'Technical',
                        $s->status->label(),
                        $s->student_skills_count ?? 0,
                        $s->required_count ?? 0,
                        $s->preferred_count ?? 0,
                        $totalDemand,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function authorizeCoordinator(): void
    {
        $user = Auth::user();
        if (! in_array($user->role, [UserRole::OC, UserRole::COC])) {
            abort(403, 'Institutional OJT reports are restricted to OJT Coordinators.');
        }
    }
}
