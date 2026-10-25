<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\MoaStatus;
use App\Enums\OpportunityStatus;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Enums\WeeklyReportStatus;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Moa;
use App\Models\Opportunity;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\Task;
use App\Models\User;
use App\Models\WeeklyReport;
use App\Services\SkillMatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, SkillMatcher $matcher): View
    {
        $user = Auth::user();

        return match ($user->role) {
            UserRole::OC => $this->ocDashboard($request),
            UserRole::COC => $this->cocDashboard($request),
            UserRole::UA => $this->uaDashboard($request),
            UserRole::PC => $this->pcDashboard($request),
            UserRole::STUDENT => $this->studentDashboard($request, $matcher),
        };
    }

    protected function ocDashboard(Request $request): View
    {
        $totalStudentsCount = StudentProfile::whereHas('user', fn ($q) => $q->where('status', 'active'))->count();
        $placedStudentsCount = StudentProfile::where('has_active_placement', true)->whereHas('user', fn ($q) => $q->where('status', 'active'))->count();

        // Primary widget: Students without a company (unplaced)
        $unplacedQuery = StudentProfile::with(['user', 'assignedCoc'])
            ->where('has_active_placement', false)
            ->whereHas('user', fn ($q) => $q->where('status', 'active'));

        if ($request->filled('program')) {
            $unplacedQuery->where('program', $request->input('program'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $unplacedQuery->where(function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $unplacedStudents = $unplacedQuery->orderBy('student_number')->paginate(8, ['*'], 'unplaced_page');
        $unplacedCount = StudentProfile::where('has_active_placement', false)->whereHas('user', fn ($q) => $q->where('status', 'active'))->count();

        $pendingRegistrationsCount = User::where('role', UserRole::STUDENT)->where('status', 'pending_approval')->count();
        $pendingVerificationsCount = Company::where('classification', CompanyClassification::NEW_COMPANY)
            ->whereIn('verification_status', [CompanyVerificationStatus::PENDING, CompanyVerificationStatus::UNDER_REVIEW])
            ->count();

        $moaBreakdown = [
            'draft' => Moa::where('status', MoaStatus::DRAFT)->count(),
            'under_review' => Moa::whereIn('status', [MoaStatus::DRAFT, MoaStatus::FOR_SIGNING_SCHOOL, MoaStatus::FOR_SIGNING_COMPANY])->count(),
            'under_legal_review' => Moa::where('status', MoaStatus::UNDER_LEGAL_REVIEW)->count(),
            'signing' => Moa::whereIn('status', [MoaStatus::FOR_SIGNING_SCHOOL, MoaStatus::FOR_SIGNING_COMPANY, MoaStatus::NOTARIZED])->count(),
            'executed' => Moa::where('status', MoaStatus::ACTIVE)->count(),
            'expired' => Moa::where('status', MoaStatus::EXPIRED)->count(),
        ];

        $pendingApplicationsCount = Application::where('status', ApplicationStatus::PENDING)->count();
        $activePlacementsCount = Placement::where('is_active', true)->count();

        $taskStats = [
            'total' => Task::count(),
            'todo' => Task::where('status', TaskStatus::TODO)->count(),
            'in_progress' => Task::where('status', TaskStatus::IN_PROGRESS)->count(),
            'done' => Task::where('status', TaskStatus::DONE)->count(),
        ];

        $recentTasks = Task::with(['assignee', 'studentProfile.user'])
            ->pending()
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        $recentAudits = AuditLog::with('actor')
            ->latest()
            ->limit(8)
            ->get();

        $allowedPrograms = config('connected.allowed_programs', ['BSIT']);

        return view('dashboard.oc', compact(
            'totalStudentsCount',
            'placedStudentsCount',
            'unplacedCount',
            'unplacedStudents',
            'pendingRegistrationsCount',
            'pendingVerificationsCount',
            'moaBreakdown',
            'pendingApplicationsCount',
            'activePlacementsCount',
            'taskStats',
            'recentTasks',
            'recentAudits',
            'allowedPrograms'
        ));
    }

    protected function cocDashboard(Request $request): View
    {
        $user = Auth::user();

        $assignedStudentsQuery = StudentProfile::with('user')
            ->where('assigned_coc_id', $user->id)
            ->whereHas('user', fn ($q) => $q->where('status', 'active'));

        $assignedStudentsCount = (clone $assignedStudentsQuery)->count();
        $assignedPlacedCount = (clone $assignedStudentsQuery)->where('has_active_placement', true)->count();
        $assignedUnplacedCount = (clone $assignedStudentsQuery)->where('has_active_placement', false)->count();

        $assignedStudents = (clone $assignedStudentsQuery)
            ->orderBy('student_number')
            ->limit(8)
            ->get();

        $myTasks = Task::with(['creator', 'studentProfile.user', 'company'])
            ->where('assigned_to', $user->id)
            ->orderByRaw('CASE WHEN status = \'done\' THEN 1 ELSE 0 END')
            ->orderBy('due_date', 'asc')
            ->limit(8)
            ->get();

        $myTaskCounts = [
            'total' => Task::where('assigned_to', $user->id)->count(),
            'todo' => Task::where('assigned_to', $user->id)->where('status', TaskStatus::TODO)->count(),
            'in_progress' => Task::where('assigned_to', $user->id)->where('status', TaskStatus::IN_PROGRESS)->count(),
            'done' => Task::where('assigned_to', $user->id)->where('status', TaskStatus::DONE)->count(),
        ];

        $pendingWeeklyReports = WeeklyReport::with([
            'placement.studentProfile.user',
            'placement.company',
            'document',
        ])->where('status', WeeklyReportStatus::SUBMITTED)
            ->whereHas('placement.studentProfile', fn ($q) => $q->where('assigned_coc_id', $user->id))
            ->latest()
            ->limit(6)
            ->get();

        $assignedMoas = Moa::with(['company', 'studentProfile.user'])
            ->whereHas('studentProfile', fn ($q) => $q->where('assigned_coc_id', $user->id))
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard.coc', compact(
            'assignedStudentsCount',
            'assignedPlacedCount',
            'assignedUnplacedCount',
            'assignedStudents',
            'myTasks',
            'myTaskCounts',
            'pendingWeeklyReports',
            'assignedMoas'
        ));
    }

    protected function uaDashboard(Request $request): View
    {
        $legalReviewQueue = Moa::with(['company', 'studentProfile.user', 'notes.author', 'latestVersion'])
            ->where('status', MoaStatus::UNDER_LEGAL_REVIEW)
            ->latest()
            ->get();

        $executedMoasCount = Moa::where('status', MoaStatus::ACTIVE)->count();
        $totalMoasCount = Moa::count();
        $expiredMoasCount = Moa::where('status', MoaStatus::EXPIRED)->count();

        $recentAgreements = Moa::with(['company', 'studentProfile.user'])
            ->whereIn('status', [MoaStatus::FOR_SIGNING_SCHOOL, MoaStatus::FOR_SIGNING_COMPANY, MoaStatus::NOTARIZED, MoaStatus::ACTIVE])
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard.ua', compact(
            'legalReviewQueue',
            'executedMoasCount',
            'totalMoasCount',
            'expiredMoasCount',
            'recentAgreements'
        ));
    }

    protected function pcDashboard(Request $request): View
    {
        $user = Auth::user();
        $partnerAccount = $user->partnerAccount;
        $company = $partnerAccount?->company;

        $opportunities = Opportunity::where('company_id', $company?->id)
            ->withCount('applications')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeOpeningsCount = $opportunities->where('status', OpportunityStatus::OPEN)->count();
        $totalApplicationsCount = Application::whereHas('opportunity', fn ($q) => $q->where('company_id', $company?->id))->count();

        // Applicants list - strictly ordered by submission time, NO score, NO ranking (BR-20)
        $pendingApplicants = Application::with(['studentProfile.user', 'opportunity'])
            ->whereHas('opportunity', fn ($q) => $q->where('company_id', $company?->id))
            ->where('status', ApplicationStatus::PENDING)
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        $activeInterns = Placement::with(['studentProfile.user', 'opportunity'])
            ->where('company_id', $company?->id)
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('dashboard.pc', compact(
            'company',
            'opportunities',
            'activeOpeningsCount',
            'totalApplicationsCount',
            'pendingApplicants',
            'activeInterns'
        ));
    }

    protected function studentDashboard(Request $request, SkillMatcher $matcher): View
    {
        $user = Auth::user();
        $studentProfile = $user->studentProfile;

        // Profile completeness computation
        $completenessScore = 0;
        $completenessItems = [];

        if ($studentProfile) {
            $hasBasic = ! empty($studentProfile->student_number) && ! empty($studentProfile->program);
            $completenessScore += $hasBasic ? 25 : 0;
            $completenessItems['Basic Academic Profile'] = $hasBasic;

            $hasContact = ! empty($studentProfile->contact_number) && ! empty($studentProfile->address);
            $completenessScore += $hasContact ? 20 : 0;
            $completenessItems['Contact & Address Details'] = $hasContact;

            $skillsCount = $studentProfile->skills()->count();
            $hasSkills = $skillsCount >= 1;
            $completenessScore += $hasSkills ? 25 : 0;
            $completenessItems['Key Skills Registered'] = $hasSkills;

            $hasResume = ! empty($studentProfile->resume_document_id);
            $completenessScore += $hasResume ? 20 : 0;
            $completenessItems['Curriculum Vitae / Resume'] = $hasResume;

            $hasConsent = ! empty($studentProfile->consented_at);
            $completenessScore += $hasConsent ? 10 : 0;
            $completenessItems['Privacy Policy Consent'] = $hasConsent;
        }

        $activePlacement = null;
        $recommendations = collect();

        if ($studentProfile) {
            $activePlacement = Placement::with([
                'company',
                'opportunity',
                'weeklyReports.document',
            ])->where('student_profile_id', $studentProfile->id)
                ->where('is_active', true)
                ->first();

            if (! $activePlacement) {
                // If unplaced, find top match recommendations
                $studentSkillIds = $studentProfile->studentSkills()->pluck('skill_id')->all();

                $opportunities = Opportunity::with(['company', 'opportunitySkills.skill'])
                    ->open()
                    ->forVerifiedPartner()
                    ->limit(15)
                    ->get();

                $recommendations = $opportunities->map(function ($opp) use ($studentSkillIds, $matcher) {
                    $matchResult = $matcher->match($studentSkillIds, $opp);

                    return [
                        'opportunity' => $opp,
                        'match' => $matchResult,
                    ];
                })->sortByDesc(fn ($item) => $item['match']['score'] ?? 0)
                    ->take(3)
                    ->values();
            }
        }

        $myApplications = Application::with(['opportunity.company'])
            ->where('student_profile_id', $studentProfile?->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.student', compact(
            'studentProfile',
            'completenessScore',
            'completenessItems',
            'activePlacement',
            'recommendations',
            'myApplications'
        ));
    }
}
