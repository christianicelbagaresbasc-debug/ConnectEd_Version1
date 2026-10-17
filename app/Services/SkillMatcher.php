<?php

namespace App\Services;

use App\Enums\OpportunityRequirementLevel;
use App\Enums\SkillStatus;
use App\Models\Opportunity;
use App\Models\OpportunitySkill;
use App\Models\Skill;
use Illuminate\Support\Collection;

class SkillMatcher
{
    /**
     * Compute the transparent match score and explanation for a student against an opportunity.
     *
     * @param iterable<int|string>|Collection $studentSkillIds Collection or array of student skill IDs
     * @param Opportunity $opportunity Opportunity model with opportunitySkills and skills
     * @return array{
     *     score: int|null,
     *     label: string,
     *     badge_classes: string,
     *     matched_skills: Collection<int, Skill>,
     *     missing_required: Collection<int, Skill>,
     *     missing_preferred: Collection<int, Skill>,
     *     matched_required_count: int,
     *     total_required_count: int,
     *     matched_preferred_count: int,
     *     total_preferred_count: int,
     * }
     */
    public function match(iterable $studentSkillIds, Opportunity $opportunity): array
    {
        // Normalize student skill IDs to a unique array of integers
        $studentIds = collect($studentSkillIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $studentIdLookup = array_flip($studentIds);

        // Fetch or use loaded opportunitySkills, filtering for active and non-archived skills
        $opportunitySkills = $opportunity->relationLoaded('opportunitySkills')
            ? $opportunity->opportunitySkills
            : $opportunity->opportunitySkills()->with('skill')->get();

        $activeOppSkills = $opportunitySkills->filter(function (OpportunitySkill $oppSkill) {
            $skill = $oppSkill->skill;
            if (!$skill) {
                return false;
            }

            return $skill->status === SkillStatus::ACTIVE && is_null($skill->deleted_at);
        });

        // Deduplicate skills by skill_id in case of redundant entries
        $uniqueOppSkills = $activeOppSkills->unique('skill_id');

        $matchedSkills = collect();
        $missingRequired = collect();
        $missingPreferred = collect();

        $matchedRequiredCount = 0;
        $totalRequiredCount = 0;
        $matchedPreferredCount = 0;
        $totalPreferredCount = 0;

        foreach ($uniqueOppSkills as $oppSkill) {
            $skillId = (int) $oppSkill->skill_id;
            $skill = $oppSkill->skill;
            $hasSkill = isset($studentIdLookup[$skillId]);

            $isReq = $oppSkill->requirement_level === OpportunityRequirementLevel::REQUIRED;

            if ($isReq) {
                $totalRequiredCount++;
                if ($hasSkill) {
                    $matchedRequiredCount++;
                    $matchedSkills->push($skill);
                } else {
                    $missingRequired->push($skill);
                }
            } else {
                $totalPreferredCount++;
                if ($hasSkill) {
                    $matchedPreferredCount++;
                    $matchedSkills->push($skill);
                } else {
                    $missingPreferred->push($skill);
                }
            }
        }

        $weightedMatched = (2 * $matchedRequiredCount) + (1 * $matchedPreferredCount);
        $weightedTotal = (2 * $totalRequiredCount) + (1 * $totalPreferredCount);

        if ($weightedTotal === 0) {
            $score = null;
            $label = 'No skill data';
            $badgeClasses = 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20';
        } else {
            $score = (int) round((100 * $weightedMatched) / $weightedTotal);

            if ($score >= 80) {
                $label = 'Strong match';
                $badgeClasses = 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20';
            } elseif ($score >= 50) {
                $label = 'Good match';
                $badgeClasses = 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20';
            } elseif ($score >= 1) {
                $label = 'Partial match';
                $badgeClasses = 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20';
            } else {
                $label = 'Low match';
                $badgeClasses = 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20';
            }
        }

        return [
            'score' => $score,
            'label' => $label,
            'badge_classes' => $badgeClasses,
            'matched_skills' => $matchedSkills,
            'missing_required' => $missingRequired,
            'missing_preferred' => $missingPreferred,
            'matched_required_count' => $matchedRequiredCount,
            'total_required_count' => $totalRequiredCount,
            'matched_preferred_count' => $matchedPreferredCount,
            'total_preferred_count' => $totalPreferredCount,
        ];
    }
}
