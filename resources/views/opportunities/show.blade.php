<x-app-layout>
    @section('title', $opportunity->title . ' — ' . $opportunity->company->name)

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('opportunities.index') }}" class="text-xs font-semibold text-text-muted hover:text-text flex items-center gap-1 mb-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    <span>Back to Opportunities</span>
                </a>
                <h1 class="text-xl font-bold text-text">{{ $opportunity->title }}</h1>
                <p class="text-xs text-text-muted mt-0.5">
                    Offered by <span class="font-semibold text-text">{{ $opportunity->company->name }}</span>
                    &bull; School Year {{ $opportunity->school_year }}
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                @can('approve', $opportunity)
                    @if ($opportunity->status->value === 'pending_approval')
                        <form method="POST" action="{{ route('opportunities.approve', $opportunity) }}" class="inline">
                            @csrf
                            <button 
                                type="submit" 
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-success text-white shadow hover:opacity-90 transition"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                <span>Approve & Publish</span>
                            </button>
                        </form>
                    @endif
                @endcan

                @can('update', $opportunity)
                    <a href="{{ route('opportunities.edit', $opportunity) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                        </svg>
                        <span>Edit</span>
                    </a>
                @endcan

                @can('delete', $opportunity)
                    <form method="POST" action="{{ route('opportunities.destroy', $opportunity) }}" onsubmit="return confirm('Move this opportunity to the Archive Center?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-danger bg-danger/10 border border-danger/20 hover:bg-danger/20 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            <span>Archive</span>
                        </button>
                    </form>
                @endcan
            </div>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-12">
        <!-- Main Details Column (2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description Card -->
            <div class="bg-surface border border-border rounded-xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <h2 class="text-sm font-bold text-text">Job Description & Responsibilities</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-surface-2 text-text-muted border border-border">
                        {{ ucfirst($opportunity->work_arrangement->value) }}
                    </span>
                </div>
                <div class="text-xs text-text leading-relaxed whitespace-pre-line">
                    {{ $opportunity->description }}
                </div>
            </div>

            <!-- Qualifications Card -->
            @if ($opportunity->qualifications)
                <div class="bg-surface border border-border rounded-xl p-6 shadow-sm space-y-3">
                    <h2 class="text-sm font-bold text-text border-b border-border pb-3">Qualifications & Prerequisites</h2>
                    <div class="text-xs text-text leading-relaxed whitespace-pre-line">
                        {{ $opportunity->qualifications }}
                    </div>
                </div>
            @endif

            <!-- Competency Requirements (Transparent Matching Basis) -->
            <div class="bg-surface border border-border rounded-xl p-6 shadow-sm space-y-5">
                <div>
                    <h2 class="text-sm font-bold text-text">Skill Competencies Required</h2>
                    <p class="text-xs text-text-muted mt-0.5">
                        These criteria feed directly into the transparent skill recommendation calculation for students.
                    </p>
                </div>

                <!-- Required Skills -->
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-primary mb-2 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span>Required Skills (2x Weight in Matching)</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse ($opportunity->requiredSkills as $req)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-primary/10 text-primary border border-primary/20">
                                <span>{{ $req->name }}</span>
                                <span class="text-[10px] text-text-muted">({{ $req->category }})</span>
                            </span>
                        @empty
                            <span class="text-xs text-text-muted italic">No strict required skills specified.</span>
                        @endforelse
                    </div>
                </div>

                <!-- Preferred Skills -->
                <div class="pt-2 border-t border-border">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-text-muted mb-2">
                        Preferred Skills (1x Weight in Matching)
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse ($opportunity->preferredSkills as $pref)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-surface-2 text-text border border-border">
                                <span>{{ $pref->name }}</span>
                                <span class="text-[10px] text-text-muted">({{ $pref->category }})</span>
                            </span>
                        @empty
                            <span class="text-xs text-text-muted italic">No preferred skills specified.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Student Applications Notice -->
            @if (Auth::user()->isStudent())
                <div class="bg-primary/5 border border-primary/20 rounded-xl p-5 flex items-start gap-4">
                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xs font-bold text-text">OJT Applications Module (Phase 6)</h3>
                        <p class="text-xs text-text-muted mt-0.5 leading-relaxed">
                            Formal student applications with single active placement concurrency will activate in the next release.
                            Ensure your skill competencies and certifications are up to date on your profile to view your personal match breakdown.
                        </p>
                        <div class="mt-3 flex items-center gap-3">
                            <a href="{{ route('student.profile.skills') }}" class="text-xs font-bold text-primary hover:underline">
                                Update My Skills &rarr;
                            </a>
                            <a href="{{ route('student.recommendations.index') }}" class="text-xs font-bold text-primary hover:underline">
                                View Recommendations &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar Metadata Column (1 col) -->
        <div class="space-y-6">
            <!-- Organization Overview Card -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted border-b border-border pb-2">Host Training Establishment</h3>
                <div>
                    <div class="font-bold text-text text-sm">{{ $opportunity->company->name }}</div>
                    <div class="text-xs text-text-muted mt-0.5">{{ $opportunity->company->industry ?? 'IT & Technology' }}</div>
                </div>

                <div class="text-xs space-y-2 pt-2 border-t border-border">
                    @if ($opportunity->company->address || $opportunity->company->city)
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-text-muted flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                            <span class="text-text-muted">{{ $opportunity->company->address ?? $opportunity->company->city }}</span>
                        </div>
                    @endif

                    @if ($opportunity->company->website)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-text-muted flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                            </svg>
                            <a href="{{ $opportunity->company->website }}" target="_blank" rel="noopener" class="text-primary hover:underline truncate">
                                {{ $opportunity->company->website }}
                            </a>
                        </div>
                    @endif
                </div>

                <div class="pt-2 border-t border-border">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-success/10 text-success border border-success/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                        Verified Existing Partner
                    </span>
                </div>
            </div>

            <!-- Opportunity Snapshot Card -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted border-b border-border pb-2">Opportunity Snapshot</h3>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-text-muted">Status:</span>
                        <span class="font-bold uppercase text-[11px] text-text">
                            {{ $opportunity->status->value }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-text-muted">Total Slots:</span>
                        <span class="font-bold text-text">{{ $opportunity->slots_total }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-text-muted">Available Slots:</span>
                        <span class="font-bold text-success">{{ $opportunity->slotsAvailable() }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-text-muted">Arrangement:</span>
                        <span class="font-semibold text-text capitalize">{{ $opportunity->work_arrangement->value }}</span>
                    </div>

                    @if ($opportunity->location)
                        <div class="flex items-center justify-between">
                            <span class="text-text-muted">Location:</span>
                            <span class="font-medium text-text truncate max-w-[150px]">{{ $opportunity->location }}</span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between">
                        <span class="text-text-muted">Deadline:</span>
                        <span class="font-semibold text-text">
                            {{ $opportunity->deadline ? $opportunity->deadline->format('M d, Y') : 'Open until filled' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-text-muted">Academic Year:</span>
                        <span class="font-semibold text-text">{{ $opportunity->school_year }}</span>
                    </div>
                </div>

                @if ($opportunity->creator)
                    <div class="pt-3 border-t border-border text-[11px] text-text-muted">
                        Posted by {{ $opportunity->creator->name }} on {{ $opportunity->created_at->format('M d, Y') }}
                        @if ($opportunity->approved_at)
                            <br>Approved by {{ $opportunity->approver?->name ?? 'Coordinator' }}
                        @endif
                    </div>
                @endif
            </div>

            <!-- Student Application Action Card (F-014) -->
            @auth
                @if(Auth::user()->isStudent())
                    @php
                        $profile = Auth::user()->studentProfile;
                        $existingApp = $profile ? $opportunity->applications()->where('student_profile_id', $profile->id)->first() : null;
                    @endphp
                    <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted border-b border-border pb-2">Apply for Placement</h3>
                        @if($existingApp)
                            <div class="space-y-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $existingApp->status->badgeClasses() }}">
                                    {{ $existingApp->status->label() }}
                                </span>
                                <p class="text-xs text-text-muted">You applied for this position on {{ $existingApp->submitted_at->format('M d, Y') }}.</p>
                                <a href="{{ route('student.applications.show', $existingApp) }}" class="block w-full text-center px-4 py-2 text-xs font-semibold rounded-lg bg-surface-2 text-primary hover:bg-surface border border-border transition">
                                    View Application Status &rarr;
                                </a>
                            </div>
                        @elseif($profile && $profile->has_active_placement)
                            <div class="p-3 rounded-lg bg-surface-2 border border-border text-xs text-text-muted">
                                You already have an active placement and cannot apply for additional roles (Rule BR-19).
                            </div>
                        @elseif($opportunity->isOpen())
                            <div x-data="{ open: false }" class="space-y-2">
                                <p class="text-xs text-text-muted">Ready to apply? Submit your application with your verified skills profile.</p>
                                <button @click="open = true" type="button" class="w-full px-4 py-2 text-xs font-semibold rounded-lg bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                                    Apply for this Opportunity
                                </button>

                                <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" style="display: none;">
                                    <div @click.outside="open = false" class="bg-surface border border-border rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
                                        <h3 class="text-sm font-bold text-text">Apply for {{ $opportunity->title }}</h3>
                                        <p class="text-xs text-text-muted">Your verified skills profile and official resume will be shared with the coordinator and host company.</p>
                                        <form method="POST" action="{{ route('student.opportunities.apply', $opportunity) }}" class="space-y-4">
                                            @csrf
                                            <div>
                                                <label class="block text-xs font-semibold text-text-muted mb-1">Cover Note / Expression of Interest (Optional)</label>
                                                <textarea name="cover_note" rows="3" class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text" placeholder="Explain your interest in this role and why your skills are a great match..."></textarea>
                                            </div>
                                            <div class="flex justify-end gap-2 pt-2">
                                                <button @click="open = false" type="button" class="px-3 py-1.5 text-xs font-medium rounded-lg text-text hover:bg-surface-2">Cancel</button>
                                                <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-primary text-primary-contrast shadow">Submit Application</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="p-3 rounded-lg bg-surface-2 border border-border text-xs text-text-muted">
                                This opportunity is currently closed to new applications.
                            </div>
                        @endif
                    </div>
                @endif
            @endauth
        </div>
    </div>
</x-app-layout>

