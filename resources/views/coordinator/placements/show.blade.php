<x-app-layout>
    @section('title', 'Placement Monitoring')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('coordinator.placements.index') }}" class="text-xs text-text-muted hover:text-text font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Back to Placements</span>
                    </a>
                </div>
                <h1 class="text-xl font-bold text-text">
                    Placement Record: {{ $placement->studentProfile->user->name }}
                </h1>
                <p class="text-xs text-text-muted mt-0.5">{{ $placement->company->name }} &bull; {{ $placement->opportunity ? $placement->opportunity->title : 'Internship' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $placement->status->badgeClasses() }}">
                    {{ $placement->status->label() }}
                </span>
            </div>
        </div>
    </x-slot>

    <!-- Feedback Alerts -->
    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-success/10 border border-success/30 text-success text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 p-4 rounded-xl bg-danger/10 border border-danger/30 text-danger text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Column: Internship Management & Weekly Reports -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Start Date Authorization Widget -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-text">Internship Start Authorization</h2>
                        <p class="text-xs text-text-muted">Formal institutional authorization to begin rendered hours at the host training establishment.</p>
                    </div>
                    @if($placement->start_authorized_at)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-success/10 text-success border border-success/20">
                            Authorized on {{ $placement->start_authorized_at->format('M d, Y') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-warning/10 text-warning border border-warning/20">
                            Pending Authorization
                        </span>
                    @endif
                </div>

                @if(! $placement->start_authorized_at)
                    <!-- Start Authorization Form -->
                    <form method="POST" action="{{ route('coordinator.placements.authorize', $placement) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="start_date" class="block text-xs font-semibold text-text-muted mb-1">Official Start Date *</label>
                                <input type="date" name="start_date" id="start_date" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text">
                            </div>
                            <div>
                                <label for="end_date" class="block text-xs font-semibold text-text-muted mb-1">Target Completion Date</label>
                                <input type="date" name="end_date" id="end_date" class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text">
                            </div>
                        </div>
                        <div>
                            <label for="note" class="block text-xs font-semibold text-text-muted mb-1">Authorization Remarks / Endorsement Note</label>
                            <textarea name="note" id="note" rows="2" class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text" placeholder="e.g. Cleared for 486 required internship hours. Endorsement letter delivered to host company."></textarea>
                        </div>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-lg bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                            Grant Formal Start Authorization
                        </button>
                    </form>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        <div class="p-3 rounded-lg bg-surface-2 border border-border">
                            <span class="text-text-muted block text-[11px]">Authorized Start Date:</span>
                            <span class="font-bold text-text text-sm">{{ $placement->start_date ? $placement->start_date->format('M d, Y') : 'N/A' }}</span>
                        </div>
                        <div class="p-3 rounded-lg bg-surface-2 border border-border">
                            <span class="text-text-muted block text-[11px]">Target Completion Date:</span>
                            <span class="font-bold text-text text-sm">{{ $placement->end_date ? $placement->end_date->format('M d, Y') : 'Open Ended' }}</span>
                        </div>
                        <div class="p-3 rounded-lg bg-surface-2 border border-border">
                            <span class="text-text-muted block text-[11px]">Endorsing Coordinator:</span>
                            <span class="font-bold text-text text-sm">{{ $placement->confirmedBy->name }}</span>
                        </div>
                    </div>
                    @if($placement->start_authorization_note)
                        <div class="p-3 rounded-lg bg-surface-2 border border-border text-xs">
                            <strong class="text-text font-semibold">Authorization Note:</strong>
                            <p class="text-text-muted mt-0.5">{{ $placement->start_authorization_note }}</p>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Weekly Accomplishment Reports Section (Assumption A9) -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-text">Weekly Accomplishment Reports (Assumption A9)</h2>
                        <p class="text-xs text-text-muted">Student-submitted weekly documents verified and reviewed by coordinators.</p>
                    </div>
                    <span class="text-xs font-semibold text-text-muted">
                        {{ $placement->weeklyReports->count() }} Report(s) Filed
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($placement->weeklyReports as $report)
                        <div class="p-4 rounded-xl bg-surface-2 border border-border space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-text">Week of {{ $report->week_start->format('M d, Y') }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $report->status->badgeClasses() }}">
                                        {{ $report->status->label() }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('documents.download', $report->document) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        <span>Download Document</span>
                                    </a>
                                </div>
                            </div>

                            @if($report->remark)
                                <div class="text-xs text-text-muted p-2 rounded bg-surface border border-border">
                                    <strong class="text-text">Coordinator Feedback:</strong> {{ $report->remark }}
                                    @if($report->reviewedBy)
                                        <span class="text-[10px] block text-text-muted/80 mt-0.5">Reviewed by {{ $report->reviewedBy->name }}</span>
                                    @endif
                                </div>
                            @endif

                            <!-- Review Action Form (for coordinators) -->
                            <div x-data="{ open: false }" class="pt-2 border-t border-border flex justify-end">
                                <button @click="open = !open" type="button" class="text-xs font-semibold text-primary hover:underline">
                                    {{ $report->isReviewed() ? 'Update Review Feedback' : 'Review / Approve Report &rarr;' }}
                                </button>

                                <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" style="display: none;">
                                    <div @click.outside="open = false" class="bg-surface border border-border rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
                                        <h3 class="text-sm font-bold text-text">Review Weekly Report</h3>
                                        <p class="text-xs text-text-muted">Week of {{ $report->week_start->format('M d, Y') }}</p>
                                        <form method="POST" action="{{ route('coordinator.weekly-reports.review', $report) }}" class="space-y-4">
                                            @csrf
                                            <div>
                                                <label class="block text-xs font-semibold text-text-muted mb-1">Review Outcome *</label>
                                                <select name="status" class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text">
                                                    <option value="reviewed" {{ $report->status->value === 'reviewed' ? 'selected' : '' }}>Reviewed & Approved</option>
                                                    <option value="flagged" {{ $report->status->value === 'flagged' ? 'selected' : '' }}>Flagged for Revision / Resubmission</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-text-muted mb-1">Feedback / Remarks</label>
                                                <textarea name="remark" rows="3" class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text" placeholder="Add specific feedback or revision requirements...">{{ $report->remark }}</textarea>
                                            </div>
                                            <div class="flex justify-end gap-2 pt-2">
                                                <button @click="open = false" type="button" class="px-3 py-1.5 text-xs font-medium rounded-lg text-text hover:bg-surface-2">Cancel</button>
                                                <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-primary text-primary-contrast shadow">Save Review</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-xs text-text-muted">
                            <p>No weekly accomplishment reports have been submitted for this placement yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Column: Student & Placement Status Progression -->
        <div class="space-y-6">
            <!-- Student Information Card -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-text text-sm">Student Intern</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-text-muted block">Name:</span>
                        <span class="font-bold text-text text-sm">{{ $placement->studentProfile->user->name }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Student ID:</span>
                        <span class="font-semibold text-text">{{ $placement->studentProfile->student_number }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Program & Section:</span>
                        <span class="font-medium text-text">{{ $placement->studentProfile->program }} - {{ $placement->studentProfile->year_level }} {{ $placement->studentProfile->section }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Contact Email:</span>
                        <span class="font-medium text-text">{{ $placement->studentProfile->user->email }}</span>
                    </div>
                </div>
            </div>

            <!-- Host Training Establishment -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-text text-sm">Host Company</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-text-muted block">Partner Name:</span>
                        <a href="{{ route('coordinator.companies.show', $placement->company) }}" class="font-bold text-primary hover:underline">
                            {{ $placement->company->name }}
                        </a>
                    </div>
                    <div>
                        <span class="text-text-muted block">Industry:</span>
                        <span class="font-medium text-text">{{ $placement->company->industry }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Location:</span>
                        <span class="font-medium text-text">{{ $placement->company->city }}, {{ $placement->company->address }}</span>
                    </div>
                </div>
            </div>

            <!-- Lifecycle Status Transition Widget -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-3">
                <h3 class="font-bold text-text text-sm">Update Internship Status</h3>
                <p class="text-xs text-text-muted">Transition placement status as student renders hours or concludes internship.</p>

                <div class="space-y-2 pt-2">
                    @if($placement->status->value === 'authorized')
                        <form method="POST" action="{{ route('coordinator.placements.status', $placement) }}">
                            @csrf
                            <input type="hidden" name="status" value="ongoing">
                            <button type="submit" class="w-full text-center px-4 py-2 text-xs font-semibold rounded-lg bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                                Mark as Ongoing Internship
                            </button>
                        </form>
                    @endif

                    @if(in_array($placement->status->value, ['authorized', 'ongoing']))
                        <!-- Complete Placement Form -->
                        <form method="POST" action="{{ route('coordinator.placements.status', $placement) }}">
                            @csrf
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="w-full text-center px-4 py-2 text-xs font-semibold rounded-lg bg-emerald-600 text-white shadow hover:bg-emerald-700 transition" onclick="return confirm('Are you sure you want to mark this placement as successfully completed?');">
                                Mark Internship Completed
                            </button>
                        </form>

                        <!-- Terminate Placement Modal -->
                        <div x-data="{ open: false }">
                            <button @click="open = true" type="button" class="w-full text-center px-4 py-2 text-xs font-semibold rounded-lg bg-danger/10 text-danger border border-danger/20 hover:bg-danger hover:text-white transition">
                                Terminate Placement
                            </button>

                            <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" style="display: none;">
                                <div @click.outside="open = false" class="bg-surface border border-border rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
                                    <h3 class="text-sm font-bold text-danger">Terminate Internship Placement</h3>
                                    <p class="text-xs text-text-muted">Terminating will deactivate this placement and record a formal reason in the audit trail.</p>
                                    <form method="POST" action="{{ route('coordinator.placements.status', $placement) }}" class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="status" value="terminated">
                                        <div>
                                            <label class="block text-xs font-semibold text-text-muted mb-1">Termination Reason (Required) *</label>
                                            <textarea name="termination_reason" rows="3" required class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text" placeholder="State reason for early termination..."></textarea>
                                        </div>
                                        <div class="flex justify-end gap-2 pt-2">
                                            <button @click="open = false" type="button" class="px-3 py-1.5 text-xs font-medium rounded-lg text-text hover:bg-surface-2">Cancel</button>
                                            <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-danger text-white shadow">Confirm Termination</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(! $placement->is_active)
                        <div class="p-3 rounded-lg bg-surface-2 border border-border text-xs text-text-muted text-center">
                            Placement is concluded (Inactive).
                            @if($placement->termination_reason)
                                <div class="mt-1 text-danger font-semibold">Reason: {{ $placement->termination_reason }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
