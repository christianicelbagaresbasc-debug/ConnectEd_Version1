<x-app-layout>
    @section('title', $company->name . ' — Company Profile')

    <x-slot name="header">
        <div class="flex items-center gap-2 text-xs text-text-muted mb-1">
            <a href="{{ route('coordinator.companies.index') }}" class="hover:text-primary transition">Companies</a>
            <span>/</span>
            <span class="text-text font-medium">{{ $company->name }}</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-xl font-bold text-text truncate">{{ $company->name }}</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('coordinator.companies.verification', $company) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>6-Point Checklist</span>
                </a>
                <a href="{{ route('coordinator.companies.edit', $company) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                    <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                    </svg>
                    <span>Edit Profile</span>
                </a>
                <form method="POST" action="{{ route('coordinator.companies.destroy', $company) }}" onsubmit="return confirm('Archive this company record? It will be soft-deleted and recoverable in the Archive Center.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 rounded-lg text-danger hover:bg-danger/10 border border-transparent hover:border-danger/20 transition" title="Archive / Soft Delete Record">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <!-- Session Feedback -->
    @if (session('status'))
        <div class="mb-6 p-4 rounded-xl bg-success/10 border border-success/30 text-success text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 rounded-xl bg-danger/10 border border-danger/30 text-danger text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- LinkedIn-Style Profile Hero Card -->
    <div class="bg-surface border border-border rounded-xl p-6 sm:p-8 mb-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start gap-5">
                <div class="w-16 h-16 rounded-2xl bg-surface-2 border border-border flex items-center justify-center font-extrabold text-primary text-xl flex-shrink-0 shadow-inner">
                    {{ strtoupper(substr($company->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <h2 class="text-xl font-bold text-text">{{ $company->name }}</h2>
                        <!-- Classification Badge -->
                        @if ($company->classification->value === 'existing_partner')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-success/10 text-success border border-success/20">
                                Existing Partner
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                New Company
                            </span>
                        @endif

                        <!-- Verification Status Badge -->
                        @php
                            $statusClasses = match($company->verification_status->value) {
                                'verified' => 'bg-success/10 text-success border-success/20',
                                'under_review' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20',
                                'pending' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                'rejected', 'revoked' => 'bg-danger/10 text-danger border-danger/20',
                                default => 'bg-surface-2 text-text-muted border-border',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $statusClasses }}">
                            {{ $company->verification_status->label() }}
                        </span>

                        @if(!$company->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold uppercase bg-surface-2 text-text-muted border border-border">
                                Inactive
                            </span>
                        @endif
                    </div>

                    <p class="text-xs text-text-muted mb-3 font-medium">{{ $company->industry ?? 'Industry Sector Unspecified' }}</p>

                    <!-- Meta Tags -->
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-text-muted">
                        @if ($company->city)
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                                <span>{{ $company->city }}</span>
                            </div>
                        @endif

                        @if ($company->website)
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                                </svg>
                                <a href="{{ $company->website }}" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline truncate max-w-xs">
                                    {{ $company->website }}
                                </a>
                            </div>
                        @endif

                        <div class="flex items-center gap-1.5">
                            <span class="text-text-muted">Source:</span>
                            <span class="font-medium text-text capitalize">{{ str_replace('_', ' ', $company->source->value) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verification Progress Scorecard -->
            @php
                $progress = $company->verificationProgressPercentage();
                $verifiedItems = $company->verificationItems->where('status.value', 'accepted')->count();
                $totalItems = $company->verificationItems->count();
            @endphp
            <div class="bg-surface-2 border border-border rounded-xl p-4 min-w-[220px] flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-semibold text-text">Checklist Progress</span>
                        <span class="font-bold text-primary">{{ $verifiedItems }} / {{ $totalItems }}</span>
                    </div>
                    <div class="w-full h-2 bg-surface rounded-full overflow-hidden border border-border mb-2">
                        <div class="h-full bg-primary rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between text-[11px] text-text-muted mt-2">
                    <span>Score: <strong>{{ $progress }}%</strong></span>
                    <a href="{{ route('coordinator.companies.verification', $company) }}" class="font-semibold text-primary hover:underline">
                        Audit Checklist &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Rule BR-17 Promotion Section -->
        @if ($company->classification->value === 'new_company')
            <div class="mt-6 pt-5 border-t border-border">
                @if ($company->canBePromoted())
                    <!-- Qualified for promotion -->
                    <div class="p-4 rounded-xl bg-success/10 border border-success/30 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-success/20 text-success flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-success">Verified & Qualified for Promotion (BR-17)</h4>
                                <p class="text-xs text-text-muted mt-0.5">This company has achieved verified status. By university policy, an OJT Coordinator may now formally promote it to an Existing Partner.</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('coordinator.companies.promote', $company) }}" onsubmit="return confirm('Promote {{ $company->name }} to Existing Partner status? This formal decision will be recorded in the audit trail.');">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-lg text-xs font-bold bg-success text-white shadow hover:opacity-90 transition whitespace-nowrap">
                                Promote to Existing Partner
                            </button>
                        </form>
                    </div>
                @else
                    <!-- Promotion Locked -->
                    <div class="p-3.5 rounded-xl bg-surface-2 border border-border flex items-center gap-3 text-xs text-text-muted">
                        <svg class="w-4 h-4 text-text-muted flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        <span>
                            <strong>Promotion Locked (Rule BR-17):</strong> New Companies can only become Existing Partners when verified by explicit OC decision. Please complete the checklist review.
                        </span>
                    </div>
                @endif
            </div>
        @elseif ($company->promoted_at)
            <!-- Already Promoted Banner -->
            <div class="mt-6 pt-5 border-t border-border flex items-center gap-2 text-xs text-text-muted">
                <svg class="w-4 h-4 text-success flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>
                    Promoted to Existing Partner on <strong class="text-text">{{ $company->promoted_at->format('M d, Y') }}</strong> by <strong class="text-text">{{ $company->promotedBy?->name ?? 'OJT Coordinator' }}</strong>.
                </span>
            </div>
        @endif
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Academic Profile, Contact, Staff -->
        <div class="lg:col-span-2 space-y-6">
            <!-- About & Academic Alignment -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted">Company Overview & Curriculum Fit</h3>
                
                <div>
                    <h4 class="text-xs font-semibold text-text mb-1">Company Description</h4>
                    <p class="text-xs text-text-muted leading-relaxed whitespace-pre-line">
                        {{ $company->description ?: 'No overview description provided.' }}
                    </p>
                </div>

                <div class="pt-3 border-t border-border">
                    <h4 class="text-xs font-semibold text-text mb-1">BSIT Curriculum Relevance Note</h4>
                    <p class="text-xs text-text-muted leading-relaxed whitespace-pre-line">
                        {{ $company->bsit_relevance_note ?: 'No specific curriculum alignment note logged for this company.' }}
                    </p>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted">Contact Person & Location</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-text-muted block text-[11px]">Primary Contact Person:</span>
                        <span class="font-semibold text-text">{{ $company->contact_person ?: 'Not specified' }}</span>
                    </div>

                    <div>
                        <span class="text-text-muted block text-[11px]">Email Address:</span>
                        @if ($company->contact_email)
                            <a href="mailto:{{ $company->contact_email }}" class="font-semibold text-primary hover:underline">
                                {{ $company->contact_email }}
                            </a>
                        @else
                            <span class="text-text-muted">Not specified</span>
                        @endif
                    </div>

                    <div>
                        <span class="text-text-muted block text-[11px]">Phone / Mobile:</span>
                        <span class="font-semibold text-text">{{ $company->contact_phone ?: 'Not specified' }}</span>
                    </div>

                    <div>
                        <span class="text-text-muted block text-[11px]">Office Address:</span>
                        <span class="font-semibold text-text">{{ $company->address ?: 'Not specified' }}</span>
                    </div>
                </div>
            </div>

            <!-- Associated Partner Accounts (PC Role) -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted">Associated Partner Accounts</h3>
                </div>

                @if ($company->partnerAccounts->isEmpty())
                    <div class="p-4 rounded-xl bg-surface-2 text-center text-xs text-text-muted">
                        No active partner representative accounts provisioned for this company yet.
                    </div>
                @else
                    <div class="divide-y divide-border">
                        @foreach ($company->partnerAccounts as $partner)
                            <div class="py-2.5 flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold text-text">{{ $partner->user->name }}</div>
                                    <div class="text-[11px] text-text-muted">{{ $partner->user->email }} &bull; {{ $partner->designation ?? 'Representative' }}</div>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-surface-2 text-text-muted">
                                    Staff ID: #{{ $partner->id }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right 1 Col: Verification Log Timeline -->
        <div class="space-y-6">
            <!-- 6-Point Checklist Quick Overview -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted">Checklist Items</h3>
                    <a href="{{ route('coordinator.companies.verification', $company) }}" class="text-xs font-semibold text-primary hover:underline">
                        Audit
                    </a>
                </div>

                <div class="space-y-2.5">
                    @forelse ($company->verificationItems as $item)
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-surface-2 border border-border text-xs">
                            <span class="font-medium text-text truncate max-w-[150px]">{{ $item->requirement->label }}</span>
                            @php
                                $badgeClass = match($item->status->value) {
                                    'accepted' => 'bg-success/10 text-success border-success/20',
                                    'submitted' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20',
                                    'rejected' => 'bg-danger/10 text-danger border-danger/20',
                                    default => 'bg-surface text-text-muted border-border',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $badgeClass }}">
                                {{ $item->status->label() }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-text-muted text-center py-2">No verification requirements assigned.</p>
                    @endforelse
                </div>
            </div>

            <!-- Verification History Log (Immutable) -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted mb-4">Verification Audit Trail</h3>

                @if ($company->verificationLogs->isEmpty())
                    <p class="text-xs text-text-muted text-center py-4">No verification logs recorded.</p>
                @else
                    <div class="relative pl-4 space-y-4 before:absolute before:left-1 before:top-2 before:bottom-2 before:w-0.5 before:bg-border">
                        @foreach ($company->verificationLogs as $log)
                            <div class="relative text-xs">
                                <div class="absolute -left-[19px] top-1 w-2.5 h-2.5 rounded-full bg-primary border-2 border-surface"></div>
                                <div class="flex items-center justify-between text-[11px] text-text-muted mb-0.5">
                                    <span class="font-semibold text-text">{{ $log->actor?->name ?? 'System' }}</span>
                                    <span>{{ $log->created_at->format('M d, H:i') }}</span>
                                </div>
                                <div class="text-text font-medium text-xs mb-1">
                                    @if ($log->from_status)
                                        <span class="text-text-muted">{{ ucfirst($log->from_status) }}</span> &rarr;
                                    @endif
                                    <span class="font-bold text-primary">{{ ucfirst($log->to_status) }}</span>
                                </div>
                                @if ($log->remark)
                                    <p class="text-[11px] text-text-muted bg-surface-2 p-2 rounded-lg border border-border">
                                        "{{ $log->remark }}"
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
