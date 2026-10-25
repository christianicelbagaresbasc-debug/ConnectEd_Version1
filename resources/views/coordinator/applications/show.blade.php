<x-app-layout>
    @section('title', 'Application Details')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('coordinator.applications.index') }}" class="text-xs text-text-muted hover:text-text font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Back to Queue</span>
                    </a>
                </div>
                <h1 class="text-xl font-bold text-text">
                    Application Review: {{ $application->studentProfile->user->name }}
                </h1>
                <p class="text-xs text-text-muted mt-0.5">Applied for {{ $application->opportunity->title }} at {{ $application->opportunity->company->name }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $application->status->badgeClasses() }}">
                    {{ $application->status->label() }}
                </span>
            </div>
        </div>
    </x-slot>

    <!-- Session Feedback -->
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
        <!-- Main Column: Student Information & Decision Workflow -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Dual Confirmation Status Card (Rule BR-19) -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-text">Dual Confirmation Workflow (Rule BR-19)</h2>
                        <p class="text-xs text-text-muted">Placement requires both student acceptance and coordinator institutional endorsement.</p>
                    </div>
                    @if($application->isPlaced())
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-success/10 text-success border border-success/20">
                            Dual Confirmed & Placed
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Student Confirmation Box -->
                    <div class="p-4 rounded-lg bg-surface-2 border border-border space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-text">1. Student Acceptance</span>
                            @if($application->student_confirmed_at)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-success/10 text-success border border-success/20">
                                    Confirmed
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-warning/10 text-warning border border-warning/20">
                                    Pending Student
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-text-muted">
                            @if($application->student_confirmed_at)
                                Student confirmed on {{ $application->student_confirmed_at->format('M d, Y h:i A') }}
                            @else
                                Awaiting student offer confirmation.
                            @endif
                        </p>
                    </div>

                    <!-- Coordinator Confirmation Box -->
                    <div class="p-4 rounded-lg bg-surface-2 border border-border space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-text">2. Coordinator Endorsement</span>
                            @if($application->oc_confirmed_at)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-success/10 text-success border border-success/20">
                                    Endorsed
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-warning/10 text-warning border border-warning/20">
                                    Pending Endorsement
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-text-muted">
                            @if($application->oc_confirmed_at)
                                Coordinator endorsed on {{ $application->oc_confirmed_at->format('M d, Y h:i A') }}
                            @else
                                Awaiting coordinator placement confirmation.
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Coordinator Action Bar -->
                @if($application->isPending() || $application->isUnderReview())
                    <div class="pt-3 border-t border-border flex flex-wrap items-center gap-3">
                        @if($application->isPending())
                            <form method="POST" action="{{ route('coordinator.applications.review', $application) }}">
                                @csrf
                                <button type="submit" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-surface-2 text-text hover:bg-surface border border-border transition">
                                    Mark Under Review
                                </button>
                            </form>
                        @endif

                        <!-- Accept Offer Modal / Form -->
                        <div x-data="{ open: false }">
                            <button @click="open = true" type="button" class="px-4 py-2 text-xs font-semibold rounded-lg bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                                Extend Offer (Accept)
                            </button>

                            <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" style="display: none;">
                                <div @click.outside="open = false" class="bg-surface border border-border rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
                                    <h3 class="text-sm font-bold text-text">Extend Internship Offer</h3>
                                    <p class="text-xs text-text-muted">Accepting this application will notify the student to confirm their slot.</p>
                                    <form method="POST" action="{{ route('coordinator.applications.decide', $application) }}" class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="decision" value="accepted">
                                        <div>
                                            <label class="block text-xs font-semibold text-text-muted mb-1">Decision Remark (Optional)</label>
                                            <textarea name="remark" rows="3" class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text" placeholder="e.g. Qualified for Technical Support Internship"></textarea>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" name="auto_confirm" id="auto_confirm" value="1" class="rounded border-border text-primary focus:ring-primary" checked>
                                            <label for="auto_confirm" class="text-xs text-text">Also record institutional endorsement now</label>
                                        </div>
                                        <div class="flex justify-end gap-2 pt-2">
                                            <button @click="open = false" type="button" class="px-3 py-1.5 text-xs font-medium rounded-lg text-text hover:bg-surface-2">Cancel</button>
                                            <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-primary text-primary-contrast shadow">Extend Offer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Reject Form / Modal -->
                        <div x-data="{ open: false }">
                            <button @click="open = true" type="button" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-danger/10 text-danger border border-danger/20 hover:bg-danger hover:text-white transition">
                                Reject Application
                            </button>

                            <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" style="display: none;">
                                <div @click.outside="open = false" class="bg-surface border border-border rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
                                    <h3 class="text-sm font-bold text-danger">Reject Application</h3>
                                    <p class="text-xs text-text-muted">Please provide a clear reason for the rejection record.</p>
                                    <form method="POST" action="{{ route('coordinator.applications.decide', $application) }}" class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="decision" value="rejected">
                                        <div>
                                            <label class="block text-xs font-semibold text-text-muted mb-1">Reason / Feedback (Required)</label>
                                            <textarea name="remark" rows="3" required class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text" placeholder="State reason for rejection..."></textarea>
                                        </div>
                                        <div class="flex justify-end gap-2 pt-2">
                                            <button @click="open = false" type="button" class="px-3 py-1.5 text-xs font-medium rounded-lg text-text hover:bg-surface-2">Cancel</button>
                                            <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-danger text-white shadow">Confirm Rejection</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($application->isAccepted() && ! $application->oc_confirmed_at)
                    <div class="pt-3 border-t border-border flex items-center justify-between">
                        <div class="text-xs text-text-muted">Student offer is active. Institutional endorsement is pending.</div>
                        <form method="POST" action="{{ route('coordinator.applications.confirm', $application) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-lg bg-success text-white shadow hover:opacity-90 transition">
                                Endorse Placement
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Student Profile Information Card -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-text">Student Applicant Details</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-text-muted block">Full Name:</span>
                        <span class="font-semibold text-text text-sm">{{ $application->studentProfile->user->name }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Student ID Number:</span>
                        <span class="font-semibold text-text text-sm">{{ $application->studentProfile->student_number }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Academic Program:</span>
                        <span class="font-medium text-text">{{ $application->studentProfile->program }} (Year {{ $application->studentProfile->year_level }} - Section {{ $application->studentProfile->section }})</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Official Resume:</span>
                        @if($application->studentProfile->resume)
                            <a href="{{ route('documents.download', $application->studentProfile->resume) }}" class="inline-flex items-center gap-1 font-semibold text-primary hover:underline">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Download Resume ({{ $application->studentProfile->resume->file_name }})</span>
                            </a>
                        @else
                            <span class="text-text-muted italic">No resume uploaded</span>
                        @endif
                    </div>
                </div>

                <!-- Student Skills Profile -->
                <div class="pt-3 border-t border-border">
                    <span class="text-xs font-semibold text-text-muted block mb-2">Student Skills Catalog Profile:</span>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($application->studentProfile->skills as $skill)
                            <span class="px-2.5 py-1 rounded-md text-[11px] font-medium bg-surface-2 border border-border text-text">
                                {{ $skill->name }}
                                <span class="text-text-muted text-[10px]">({{ ucfirst($skill->pivot->proficiency ?? 'competent') }})</span>
                            </span>
                        @empty
                            <span class="text-xs text-text-muted italic">No skills tagged in student profile.</span>
                        @endforelse
                    </div>
                </div>

                <!-- Cover Note -->
                @if($application->cover_note)
                    <div class="pt-3 border-t border-border">
                        <span class="text-xs font-semibold text-text-muted block mb-1">Student Cover Note:</span>
                        <div class="p-3 rounded-lg bg-surface-2 border border-border text-xs text-text italic">
                            "{{ $application->cover_note }}"
                        </div>
                    </div>
                @endif
            </div>

            <!-- Application Status History Audit Log -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-text">Application Status Audit Trail</h2>
                <div class="space-y-3">
                    @forelse($application->statusLogs as $log)
                        <div class="flex items-start gap-3 text-xs">
                            <div class="w-2 h-2 rounded-full bg-primary mt-1.5 flex-shrink-0"></div>
                            <div class="flex-1 bg-surface-2 p-3 rounded-lg border border-border">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold text-text">
                                        {{ $log->from_status ? $log->from_status->label() . ' → ' : '' }}{{ $log->to_status->label() }}
                                    </span>
                                    <span class="text-[10px] text-text-muted">{{ $log->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                                <div class="text-text-muted text-[11px]">
                                    By: <strong class="text-text">{{ $log->actor ? $log->actor->name : 'System' }}</strong>
                                    @if($log->remark)
                                        &bull; "{{ $log->remark }}"
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-text-muted italic">No status transitions logged yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Column: Opportunity Specs & Placement Shortcut -->
        <div class="space-y-6">
            <!-- Opportunity Summary Card -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-text text-sm">Target Opportunity</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-text-muted block">Position:</span>
                        <a href="{{ route('opportunities.show', $application->opportunity) }}" class="font-bold text-primary hover:underline text-sm block">
                            {{ $application->opportunity->title }}
                        </a>
                    </div>
                    <div>
                        <span class="text-text-muted block">Company:</span>
                        <span class="font-semibold text-text">{{ $application->opportunity->company->name }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Slots Availability:</span>
                        <span class="font-semibold text-text">{{ $application->opportunity->slots_filled }} / {{ $application->opportunity->slots_total }} slots filled</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Work Arrangement:</span>
                        <span class="font-medium text-text">{{ $application->opportunity->work_arrangement->label() }}</span>
                    </div>
                </div>
            </div>

            <!-- Placement Link (if placed) -->
            @if($application->placement)
                <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-3">
                    <h3 class="font-bold text-success text-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Official Placement Record
                    </h3>
                    <p class="text-xs text-text-muted">This student has been officially placed under this application.</p>
                    <a href="{{ route('coordinator.placements.show', $application->placement) }}" class="block w-full text-center px-4 py-2 text-xs font-semibold rounded-lg bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                        View Placement & Weekly Reports
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
