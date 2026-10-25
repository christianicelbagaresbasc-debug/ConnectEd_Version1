<x-app-layout>
    @section('title', 'Application Details')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('student.applications.index') }}" class="text-xs text-text-muted hover:text-text font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Back to My Applications</span>
                    </a>
                </div>
                <h1 class="text-xl font-bold text-text">
                    {{ $application->opportunity->title }}
                </h1>
                <p class="text-xs text-text-muted mt-0.5">{{ $application->opportunity->company->name }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $application->status->badgeClasses() }}">
                    {{ $application->status->label() }}
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Dual Confirmation Status Card (if accepted) -->
            @if($application->isAccepted())
                <div class="bg-surface border border-primary/20 rounded-xl p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-border pb-3">
                        <div>
                            <h2 class="text-sm font-bold text-text">Offer Confirmation Status</h2>
                            <p class="text-xs text-text-muted">Finalizing this placement requires dual confirmation from both you and the OJT Coordinator.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-lg bg-surface-2 border border-border">
                            <span class="text-xs font-bold text-text block mb-1">Your Confirmation</span>
                            @if($application->student_confirmed_at)
                                <div class="text-xs text-success font-semibold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Confirmed on {{ $application->student_confirmed_at->format('M d, Y h:i A') }}
                                </div>
                            @else
                                <form method="POST" action="{{ route('student.applications.confirm', $application) }}" class="mt-2">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2 text-xs font-semibold rounded-lg bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                                        Confirm Acceptance Now
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="p-4 rounded-lg bg-surface-2 border border-border">
                            <span class="text-xs font-bold text-text block mb-1">Coordinator Endorsement</span>
                            @if($application->oc_confirmed_at)
                                <div class="text-xs text-success font-semibold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Endorsed on {{ $application->oc_confirmed_at->format('M d, Y h:i A') }}
                                </div>
                            @else
                                <div class="text-xs text-warning font-semibold">
                                    Pending Coordinator Endorsement
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Cover Note & Decision Remarks -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-text">Application Details</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-text-muted block">Submitted Date:</span>
                        <span class="font-semibold text-text">{{ $application->submitted_at ? $application->submitted_at->format('M d, Y h:i A') : 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Current Status:</span>
                        <span class="font-semibold text-text">{{ $application->status->label() }}</span>
                    </div>
                </div>

                @if($application->cover_note)
                    <div class="pt-3 border-t border-border">
                        <span class="text-xs font-semibold text-text-muted block mb-1">Your Submitted Cover Note:</span>
                        <div class="p-3 rounded-lg bg-surface-2 border border-border text-xs text-text italic">
                            "{{ $application->cover_note }}"
                        </div>
                    </div>
                @endif

                @if($application->decision_remark)
                    <div class="pt-3 border-t border-border">
                        <span class="text-xs font-semibold text-text-muted block mb-1">Feedback / Decision Remarks:</span>
                        <div class="p-3 rounded-lg bg-surface-2 border border-border text-xs text-text">
                            {{ $application->decision_remark }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Status Timeline -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-text">Status History Timeline</h2>
                <div class="space-y-3">
                    @foreach($application->statusLogs as $log)
                        <div class="flex items-start gap-3 text-xs">
                            <div class="w-2 h-2 rounded-full bg-primary mt-1.5 flex-shrink-0"></div>
                            <div class="flex-1 bg-surface-2 p-3 rounded-lg border border-border">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold text-text">{{ $log->to_status->label() }}</span>
                                    <span class="text-[10px] text-text-muted">{{ $log->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                                @if($log->remark)
                                    <p class="text-text-muted text-[11px]">{{ $log->remark }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="space-y-6">
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-text text-sm">Company Information</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-text-muted block">Partner Name:</span>
                        <span class="font-bold text-text">{{ $application->opportunity->company->name }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Industry:</span>
                        <span class="font-medium text-text">{{ $application->opportunity->company->industry }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Location:</span>
                        <span class="font-medium text-text">{{ $application->opportunity->location }}</span>
                    </div>
                </div>
            </div>

            @if($application->placement)
                <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-3">
                    <h3 class="font-bold text-success text-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Official Placement Active
                    </h3>
                    <p class="text-xs text-text-muted">Your placement is finalized. You can now access your weekly report submissions.</p>
                    <a href="{{ route('student.placements.show', $application->placement) }}" class="block w-full text-center px-4 py-2 text-xs font-semibold rounded-lg bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                        View Placement & Weekly Reports &rarr;
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
