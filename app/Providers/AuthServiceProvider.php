<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Application;
use App\Models\Company;
use App\Models\CompanySuggestion;
use App\Models\Document;
use App\Models\Moa;
use App\Models\Opportunity;
use App\Models\Placement;
use App\Models\Skill;
use App\Models\StudentProfile;
use App\Models\Task;
use App\Models\User;
use App\Models\WeeklyReport;
use App\Policies\ApplicationPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\CompanySuggestionPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\MoaPolicy;
use App\Policies\OpportunityPolicy;
use App\Policies\PlacementPolicy;
use App\Policies\SkillPolicy;
use App\Policies\StudentProfilePolicy;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use App\Policies\WeeklyReportPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Document::class => DocumentPolicy::class,
        Company::class => CompanyPolicy::class,
        CompanySuggestion::class => CompanySuggestionPolicy::class,
        Moa::class => MoaPolicy::class,
        Skill::class => SkillPolicy::class,
        Opportunity::class => OpportunityPolicy::class,
        StudentProfile::class => StudentProfilePolicy::class,
        Application::class => ApplicationPolicy::class,
        Placement::class => PlacementPolicy::class,
        WeeklyReport::class => WeeklyReportPolicy::class,
        Task::class => TaskPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
