<x-app-layout>
    @section('title', 'OJT Opportunities')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">OJT Opportunities Directory</h1>
                <p class="text-xs text-text-muted mt-0.5">Explore skill-aligned internship openings from verified industry partners (FR-08, FR-10).</p>
            </div>
            @can('create', App\Models\Opportunity::class)
                <div class="flex items-center gap-3">
                    <a href="{{ route('opportunities.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Post Opportunity</span>
                    </a>
                </div>
            @endcan
        </div>
    </x-slot>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-6 p-4 rounded-xl bg-success/10 border border-success/30 text-success text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    <!-- Multi-Criteria Search & Filter Card -->
    <div class="bg-surface border border-border rounded-xl p-4 sm:p-5 mb-6 shadow-sm">
        <form method="GET" action="{{ route('opportunities.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Search Query -->
                <div class="lg:col-span-2">
                    <label for="search" class="block text-xs font-semibold text-text-muted mb-1">Search Openings</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="search" 
                            id="search" 
                            value="{{ request('search') }}" 
                            placeholder="Search by role title, description, or partner company..." 
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                        />
                    </div>
                </div>

                <!-- Work Arrangement -->
                <div>
                    <label for="work_arrangement" class="block text-xs font-semibold text-text-muted mb-1">Work Arrangement</label>
                    <select 
                        name="work_arrangement" 
                        id="work_arrangement" 
                        class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    >
                        <option value="">All Arrangements</option>
                        <option value="onsite" {{ request('work_arrangement') === 'onsite' ? 'selected' : '' }}>Onsite</option>
                        <option value="remote" {{ request('work_arrangement') === 'remote' ? 'selected' : '' }}>Remote</option>
                        <option value="hybrid" {{ request('work_arrangement') === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                    </select>
                </div>

                <!-- Skill Filter -->
                <div>
                    <label for="skill_id" class="block text-xs font-semibold text-text-muted mb-1">Skill Filter</label>
                    <select 
                        name="skill_id" 
                        id="skill_id" 
                        class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    >
                        <option value="">All Skills</option>
                        @foreach ($skills as $skill)
                            <option value="{{ $skill->id }}" {{ (string) request('skill_id') === (string) $skill->id ? 'selected' : '' }}>
                                {{ $skill->name }} ({{ $skill->category }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Company Filter (for students/coordinators) -->
                @unless(Auth::user()->isPc())
                    <div>
                        <label for="company_id" class="block text-xs font-semibold text-text-muted mb-1">Partner Organization</label>
                        <select 
                            name="company_id" 
                            id="company_id" 
                            class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                        >
                            <option value="">All Partner Companies</option>
                            @foreach ($companies as $comp)
                                <option value="{{ $comp->id }}" {{ (string) request('company_id') === (string) $comp->id ? 'selected' : '' }}>
                                    {{ $comp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endunless

                <!-- Location -->
                <div>
                    <label for="location" class="block text-xs font-semibold text-text-muted mb-1">Location / City</label>
                    <input 
                        type="text" 
                        name="location" 
                        id="location" 
                        value="{{ request('location') }}" 
                        placeholder="e.g. San Ildefonso, Malolos, Manila" 
                        class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    />
                </div>

                <!-- Status Filter (for staff and PC) -->
                @unless(Auth::user()->isStudent())
                    <div>
                        <label for="status" class="block text-xs font-semibold text-text-muted mb-1">Publication Status</label>
                        <select 
                            name="status" 
                            id="status" 
                            class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                        >
                            <option value="">All Statuses</option>
                            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                @endunless
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-border">
                @if (request()->hasAny(['search', 'work_arrangement', 'skill_id', 'company_id', 'location', 'status']))
                    <a href="{{ route('opportunities.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium text-text-muted hover:text-text transition">
                        Reset Filters
                    </a>
                @endif
                <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                    Search Opportunities
                </button>
            </div>
        </form>
    </div>

    <!-- Opportunity Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        @forelse ($opportunities as $opp)
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm hover:border-primary/50 transition flex flex-col justify-between">
                <div>
                    <!-- Header with Company and Status Badge -->
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-semibold text-primary truncate block">
                                {{ $opp->company->name }}
                            </span>
                            <h2 class="text-sm font-bold text-text truncate mt-0.5">
                                {{ $opp->title }}
                            </h2>
                        </div>
                        @if ($opp->status->value === 'open')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-success/10 text-success border border-success/20 flex-shrink-0">
                                Open
                            </span>
                        @elseif ($opp->status->value === 'pending_approval')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-warning/10 text-warning border border-warning/20 flex-shrink-0">
                                Pending Approval
                            </span>
                        @elseif ($opp->status->value === 'draft')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-surface-2 text-text-muted border border-border flex-shrink-0">
                                Draft
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-surface-2 text-text-muted border border-border flex-shrink-0">
                                {{ $opp->status->value }}
                            </span>
                        @endif
                    </div>

                    <!-- Meta Tags (Arrangement, Location, Slots) -->
                    <div class="flex flex-wrap items-center gap-1.5 text-[11px] text-text-muted mb-3">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-surface-2 border border-border text-text">
                            <svg class="w-3 h-3 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            {{ ucfirst($opp->work_arrangement->value) }}
                        </span>
                        @if ($opp->location)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-surface-2 border border-border">
                                <svg class="w-3 h-3 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                                {{ $opp->location }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-surface-2 border border-border font-medium">
                            {{ $opp->slotsAvailable() }} / {{ $opp->slots_total }} slots available
                        </span>
                    </div>

                    <!-- Brief Description -->
                    <p class="text-xs text-text-muted line-clamp-2 mb-4 leading-relaxed">
                        {{ $opp->description }}
                    </p>

                    <!-- Skills Chips Preview -->
                    <div class="mb-4">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-text-muted mb-1.5">Required & Preferred Skills</div>
                        <div class="flex flex-wrap gap-1">
                            @foreach ($opp->requiredSkills->take(4) as $reqSkill)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-primary/10 text-primary border border-primary/20">
                                    {{ $reqSkill->name }} *
                                </span>
                            @endforeach
                            @foreach ($opp->preferredSkills->take(3) as $prefSkill)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-surface-2 text-text border border-border">
                                    {{ $prefSkill->name }}
                                </span>
                            @endforeach
                            @if (($opp->requiredSkills->count() + $opp->preferredSkills->count()) > 7)
                                <span class="text-[10px] text-text-muted self-center ml-1">
                                    +{{ ($opp->requiredSkills->count() + $opp->preferredSkills->count()) - 7 }} more
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer with Deadline & CTA -->
                <div class="pt-3 border-t border-border flex items-center justify-between text-xs">
                    <div class="text-[11px] text-text-muted">
                        @if ($opp->deadline)
                            <span>Deadline: <strong class="text-text">{{ $opp->deadline->format('M d, Y') }}</strong></span>
                        @else
                            <span class="text-text-muted">Open until filled</span>
                        @endif
                    </div>
                    <a href="{{ route('opportunities.show', $opp) }}" class="inline-flex items-center gap-1 font-semibold text-primary hover:underline">
                        <span>Details</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-surface border border-border rounded-xl p-12 text-center shadow-sm">
                <svg class="w-12 h-12 text-text-muted mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
                <h3 class="text-sm font-bold text-text">No opportunities found</h3>
                <p class="text-xs text-text-muted mt-1 max-w-sm mx-auto">
                    There are no open internship opportunities matching your filter criteria right now.
                </p>
            </div>
        @endforelse
    </div>

    @if ($opportunities->hasPages())
        <div class="mb-6">
            {{ $opportunities->links() }}
        </div>
    @endif
</x-app-layout>
