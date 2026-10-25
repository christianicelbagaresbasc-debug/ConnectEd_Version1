<?php

namespace App\Http\Controllers\Student;

use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\SkillStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Opportunity;
use App\Models\Skill;
use App\Services\SkillMatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RecommendationController extends Controller
{
    /**
     * Transparent Skill-Based Recommendation Engine (F-013, Rule BR-20)
     * Matches open opportunities against the authenticated student's skills.
     * Scores are computed on the fly and visible ONLY to the concerned student.
     */
    public function index(Request $request, SkillMatcher $matcher): View
    {
        $user = Auth::user();

        if (! $user->isStudent()) {
            abort(403, 'Recommendations are reserved for ICS students.');
        }

        $studentProfile = $user->studentProfile;
        if (! $studentProfile) {
            abort(403, 'Student profile not initialized.');
        }

        $studentSkills = $studentProfile->studentSkills()->with('skill')->get();
        $studentSkillIds = $studentSkills->pluck('skill_id')->all();

        // Query open opportunities from active verified existing partners
        $query = Opportunity::with(['company', 'opportunitySkills.skill'])
            ->open()
            ->forVerifiedPartner();

        // Optional UI Filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('company', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('work_arrangement')) {
            $query->where('work_arrangement', $request->input('work_arrangement'));
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%'.$request->input('location').'%');
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        if ($request->filled('skill_id')) {
            $skillId = $request->input('skill_id');
            $query->whereHas('skills', fn ($sq) => $sq->where('skills.id', $skillId));
        }

        $allOpportunities = $query->get();

        // Match each opportunity against the student's skills
        $scoredRecommendations = $allOpportunities->map(function (Opportunity $opportunity) use ($matcher, $studentSkillIds) {
            $matchResult = $matcher->match($studentSkillIds, $opportunity);

            return [
                'opportunity' => $opportunity,
                'match' => $matchResult,
                'score' => $matchResult['score'] ?? -1, // Sort unranked at bottom
            ];
        });

        // Filter by match level if requested
        if ($request->filled('match_level')) {
            $level = $request->input('match_level');
            $scoredRecommendations = $scoredRecommendations->filter(function ($item) use ($level) {
                return strtolower($item['match']['label']) === strtolower($level);
            });
        }

        // Sort descending by match score for this student only
        $recommendations = $scoredRecommendations->sortByDesc('score')->values();

        $companies = Company::where('is_active', true)
            ->where('classification', CompanyClassification::EXISTING_PARTNER)
            ->where('verification_status', CompanyVerificationStatus::VERIFIED)
            ->orderBy('name')
            ->get();

        $skills = Skill::where('status', SkillStatus::ACTIVE)->orderBy('name')->get();

        return view('student.recommendations.index', compact('recommendations', 'studentProfile', 'studentSkills', 'companies', 'skills'));
    }
}
