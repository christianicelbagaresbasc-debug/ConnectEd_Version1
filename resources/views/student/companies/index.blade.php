<x-app-layout>
    @section('title', 'Partner Companies Directory')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">Partner Companies Directory</h1>
                <p class="text-xs text-text-muted mt-0.5">Explore officially verified Host Training Establishments (HTEs) accredited for BSAU ICS internships.</p>
            </div>
            <a href="{{ route('student.suggestions.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Suggest a Company</span>
            </a>
        </div>
    </x-slot>

    <!-- Suggestion Callout Banner -->
    <div class="mb-6 p-4 rounded-xl bg-surface border border-border flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.516 0c.85.493 1.509 1.333 1.509 2.316V18" />
                </svg>
            </div>
            <div>
                <h3 class="text-xs font-bold text-text">Looking for an unlisted company?</h3>
                <p class="text-[11px] text-text-muted">If you have an employer willing to host your internship, submit a suggestion for verification.</p>
            </div>
        </div>
        <a href="{{ route('student.suggestions.create') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition whitespace-nowrap">
            Submit Suggestion &rarr;
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-surface border border-border rounded-xl p-4 sm:p-5 mb-6 shadow-sm">
        <form method="GET" action="{{ route('student.companies.index') }}" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search partner companies by name, industry, or location..." 
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    />
                </div>

                <div>
                    <select 
                        name="industry" 
                        onchange="this.form.submit()" 
                        class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    >
                        <option value="">All Industry Sectors</option>
                        @foreach ($industries as $ind)
                            <option value="{{ $ind }}" {{ request('industry') === $ind ? 'selected' : '' }}>{{ $ind }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if(request()->hasAny(['search', 'industry']))
                <div class="flex items-center justify-between pt-2 border-t border-border text-xs">
                    <span class="text-text-muted">Filtering active results</span>
                    <a href="{{ route('student.companies.index') }}" class="font-medium text-primary hover:underline">
                        Reset Filters
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Verified Companies Grid -->
    @if ($companies->isEmpty())
        <div class="bg-surface border border-border rounded-xl p-12 text-center">
            <div class="w-12 h-12 mx-auto rounded-full bg-surface-2 flex items-center justify-center text-text-muted mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-text">No verified partner companies found</h3>
            <p class="text-xs text-text-muted mt-1 max-w-sm mx-auto">Try clearing your search query or suggest an employer to start the verification process.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($companies as $company)
                <div class="bg-surface border border-border rounded-xl p-5 flex flex-col justify-between hover:border-primary/40 hover:shadow-sm transition">
                    <div>
                        <!-- Header & Logo -->
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-10 h-10 rounded-lg bg-surface-2 border border-border flex items-center justify-center font-bold text-primary text-sm flex-shrink-0">
                                {{ strtoupper(substr($company->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-text truncate">
                                    <a href="{{ route('student.companies.show', $company) }}" class="hover:text-primary transition">
                                        {{ $company->name }}
                                    </a>
                                </h3>
                                <p class="text-[11px] text-text-muted truncate">{{ $company->industry ?? 'IT & Computing' }}</p>
                            </div>
                        </div>

                        <!-- Verification Seal Badge -->
                        <div class="flex items-center gap-1.5 mb-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-success/10 text-success border border-success/20">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                Verified Partner
                            </span>
                        </div>

                        <!-- Description excerpt -->
                        <p class="text-xs text-text-muted line-clamp-3 mb-4 leading-relaxed">
                            {{ $company->description ?: ($company->bsit_relevance_note ?: 'Accredited Host Training Establishment for BSAU ICS internships.') }}
                        </p>

                        <!-- Location & Website -->
                        <div class="space-y-1 text-xs text-text-muted">
                            @if ($company->city)
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                    <span class="truncate">{{ $company->city }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="mt-4 pt-3 border-t border-border flex items-center justify-between">
                        @if ($company->website)
                            <a href="{{ $company->website }}" target="_blank" rel="noopener noreferrer" class="text-xs text-text-muted hover:text-text truncate max-w-[150px]">
                                Visit Website &rarr;
                            </a>
                        @else
                            <span></span>
                        @endif

                        <a href="{{ route('student.companies.show', $company) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                            View Profile
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $companies->links() }}
        </div>
    @endif
</x-app-layout>
