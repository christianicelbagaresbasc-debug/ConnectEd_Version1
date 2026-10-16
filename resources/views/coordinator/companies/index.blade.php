<x-app-layout>
    @section('title', 'Company Records')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">Company Records Management</h1>
                <p class="text-xs text-text-muted mt-0.5">Directory of partner organizations and prospective internship host training establishments (HTEs).</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('coordinator.suggestions.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                    <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>Suggestions Queue</span>
                </a>
                <a href="{{ route('coordinator.companies.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Add Company Record</span>
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Session Feedback -->
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

    <!-- LinkedIn-Style Filter Card -->
    <div class="bg-surface border border-border rounded-xl p-4 sm:p-5 mb-6 shadow-sm">
        <form method="GET" action="{{ route('coordinator.companies.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Search Query -->
                <div class="lg:col-span-2">
                    <label for="search" class="block text-xs font-semibold text-text-muted mb-1">Search Directory</label>
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
                            placeholder="Search by company name, industry, or city..." 
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                        />
                    </div>
                </div>

                <!-- Classification Filter -->
                <div>
                    <label for="classification" class="block text-xs font-semibold text-text-muted mb-1">Classification</label>
                    <select 
                        name="classification" 
                        id="classification" 
                        class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    >
                        <option value="">All Classifications</option>
                        <option value="new_company" {{ request('classification') === 'new_company' ? 'selected' : '' }}>New Company</option>
                        <option value="existing_partner" {{ request('classification') === 'existing_partner' ? 'selected' : '' }}>Existing Partner</option>
                    </select>
                </div>

                <!-- Verification Status Filter -->
                <div>
                    <label for="verification_status" class="block text-xs font-semibold text-text-muted mb-1">Verification Status</label>
                    <select 
                        name="verification_status" 
                        id="verification_status" 
                        class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    >
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('verification_status') === 'pending' ? 'selected' : '' }}>Pending Review</option>
                        <option value="under_review" {{ request('verification_status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="verified" {{ request('verification_status') === 'verified' ? 'selected' : '' }}>Verified (Approved)</option>
                        <option value="rejected" {{ request('verification_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="revoked" {{ request('verification_status') === 'revoked' ? 'selected' : '' }}>Revoked</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-border">
                <div class="flex items-center gap-4 text-xs">
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ request('is_active') === '1' ? 'checked' : '' }} class="rounded border-border text-primary focus:ring-primary" />
                        <span class="text-text-muted">Active HTEs Only</span>
                    </label>
                </div>
                <div class="flex items-center gap-2">
                    @if(request()->hasAny(['search', 'classification', 'verification_status', 'is_active']))
                        <a href="{{ route('coordinator.companies.index') }}" class="text-xs font-medium text-text-muted hover:text-text px-3 py-1.5 rounded-lg transition">
                            Reset Filters
                        </a>
                    @endif
                    <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                        Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Company Directory Grid -->
    @if ($companies->isEmpty())
        <div class="bg-surface border border-border rounded-xl p-12 text-center">
            <div class="w-12 h-12 mx-auto rounded-full bg-surface-2 flex items-center justify-center text-text-muted mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-text">No company records found</h3>
            <p class="text-xs text-text-muted mt-1 max-w-sm mx-auto">No records match your selected criteria. Try adjusting your filters or register a new company.</p>
            <div class="mt-4">
                <a href="{{ route('coordinator.companies.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary text-primary-contrast hover:opacity-90 transition">
                    Add New Company
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($companies as $company)
                <div class="bg-surface border border-border rounded-xl p-5 flex flex-col justify-between hover:border-primary/40 hover:shadow-sm transition">
                    <div>
                        <!-- Header with Badges -->
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-surface-2 border border-border flex items-center justify-center font-bold text-primary text-sm flex-shrink-0">
                                    {{ strtoupper(substr($company->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-bold text-text truncate leading-snug">
                                        <a href="{{ route('coordinator.companies.show', $company) }}" class="hover:text-primary transition">
                                            {{ $company->name }}
                                        </a>
                                    </h3>
                                    <p class="text-[11px] text-text-muted truncate">{{ $company->industry ?? 'Industry not specified' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Classification & Status Badges -->
                        <div class="flex flex-wrap items-center gap-1.5 mb-3">
                            <!-- Classification -->
                            @if ($company->classification->value === 'existing_partner')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-success/10 text-success border border-success/20">
                                    Existing Partner
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                    New Company
                                </span>
                            @endif

                            <!-- Verification Status -->
                            @php
                                $statusClasses = match($company->verification_status->value) {
                                    'verified' => 'bg-success/10 text-success border-success/20',
                                    'under_review' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20',
                                    'pending' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                    'rejected', 'revoked' => 'bg-danger/10 text-danger border-danger/20',
                                    default => 'bg-surface-2 text-text-muted border-border',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold border {{ $statusClasses }}">
                                {{ $company->verification_status->label() }}
                            </span>

                            @if(!$company->is_active)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-surface-2 text-text-muted border border-border">
                                    Inactive
                                </span>
                            @endif
                        </div>

                        <!-- Details snippet -->
                        <div class="space-y-1 text-xs text-text-muted mb-4">
                            @if ($company->city)
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                    <span class="truncate">{{ $company->city }}</span>
                                </div>
                            @endif
                            @if ($company->contact_person)
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                    <span class="truncate">{{ $company->contact_person }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Checklist Progress -->
                        @php
                            $progress = $company->verificationProgressPercentage();
                        @endphp
                        <div class="pt-3 border-t border-border">
                            <div class="flex items-center justify-between text-[11px] mb-1">
                                <span class="font-medium text-text-muted">Verification Checklist</span>
                                <span class="font-bold text-text">{{ $progress }}%</span>
                            </div>
                            <div class="w-full h-1.5 bg-surface-2 rounded-full overflow-hidden border border-border">
                                <div class="h-full bg-primary rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer actions -->
                    <div class="mt-4 pt-3 border-t border-border flex items-center justify-between">
                        <a href="{{ route('coordinator.companies.verification', $company) }}" class="text-xs font-semibold text-primary hover:underline inline-flex items-center gap-1">
                            Checklist
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('coordinator.companies.edit', $company) }}" class="p-1.5 rounded-lg text-text-muted hover:text-text hover:bg-surface-2 transition" title="Edit company details">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                </svg>
                            </a>
                            <a href="{{ route('coordinator.companies.show', $company) }}" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                                Profile
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $companies->links() }}
        </div>
    @endif
</x-app-layout>
