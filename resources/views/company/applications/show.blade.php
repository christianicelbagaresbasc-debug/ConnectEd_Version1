<x-app-layout>
    @section('title', 'Candidate Review')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('company.applications.index') }}" class="text-xs text-text-muted hover:text-text font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Back to Applicants</span>
                    </a>
                </div>
                <h1 class="text-xl font-bold text-text">
                    Candidate Review: {{ $application->studentProfile->user->name }}
                </h1>
                <p class="text-xs text-text-muted mt-0.5">Applied for {{ $application->opportunity->title }}</p>
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
        <!-- Main Column: Qualifications & Evaluation -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Decision Actions Bar -->
            @if(in_array($application->status->value, ['pending', 'under_review']))
                <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-3">
                    <h2 class="text-sm font-bold text-text">Candidate Decision</h2>
                    <p class="text-xs text-text-muted">Review candidate qualifications below. You may extend an internship offer or decline the application.</p>
                    <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-border">
                        @if($application->isPending())
                            <form method="POST" action="{{ route('company.applications.review', $application) }}">
                                @csrf
                                <button type="submit" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-surface-2 text-text hover:bg-surface border border-border transition">
                                    Mark Under Review
                                </button>
                            </form>
                        @endif

                        <!-- Accept Offer Modal -->
                        <div x-data="{ open: false }">
                            <button @click="open = true" type="button" class="px-4 py-2 text-xs font-semibold rounded-lg bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                                Extend Internship Offer
                            </button>

                            <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" style="display: none;">
                                <div @click.outside="open = false" class="bg-surface border border-border rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
                                    <h3 class="text-sm font-bold text-text">Extend Internship Offer</h3>
                                    <p class="text-xs text-text-muted">Student will receive your formal offer to confirm their placement slot.</p>
                                    <form method="POST" action="{{ route('company.applications.decide', $application) }}" class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="decision" value="accepted">
                                        <div>
                                            <label class="block text-xs font-semibold text-text-muted mb-1">Offer Message / Welcome Note</label>
                                            <textarea name="remark" rows="3" class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text" placeholder="e.g. Pleased to offer you the internship position..."></textarea>
                                        </div>
                                        <div class="flex justify-end gap-2 pt-2">
                                            <button @click="open = false" type="button" class="px-3 py-1.5 text-xs font-medium rounded-lg text-text hover:bg-surface-2">Cancel</button>
                                            <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-primary text-primary-contrast shadow">Confirm Offer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Reject Modal -->
                        <div x-data="{ open: false }">
                            <button @click="open = true" type="button" class="px-3.5 py-2 text-xs font-semibold rounded-lg text-danger hover:bg-danger/10 border border-danger/20 transition">
                                Decline Candidate
                            </button>

                            <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" style="display: none;">
                                <div @click.outside="open = false" class="bg-surface border border-border rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
                                    <h3 class="text-sm font-bold text-danger">Decline Candidate</h3>
                                    <p class="text-xs text-text-muted">State feedback or reason for not moving forward.</p>
                                    <form method="POST" action="{{ route('company.applications.decide', $application) }}" class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="decision" value="rejected">
                                        <div>
                                            <label class="block text-xs font-semibold text-text-muted mb-1">Feedback Remarks</label>
                                            <textarea name="remark" rows="3" class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text" placeholder="Feedback for candidate..."></textarea>
                                        </div>
                                        <div class="flex justify-end gap-2 pt-2">
                                            <button @click="open = false" type="button" class="px-3 py-1.5 text-xs font-medium rounded-lg text-text hover:bg-surface-2">Cancel</button>
                                            <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-danger text-white shadow">Decline</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Candidate Profile -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-text">Candidate Profile</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-text-muted block">Student Name:</span>
                        <span class="font-bold text-text text-sm">{{ $application->studentProfile->user->name }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Email:</span>
                        <span class="font-medium text-text">{{ $application->studentProfile->user->email }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Program:</span>
                        <span class="font-medium text-text">{{ $application->studentProfile->program }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Resume Document:</span>
                        @if($application->studentProfile->resume)
                            <a href="{{ route('documents.download', $application->studentProfile->resume) }}" class="inline-flex items-center gap-1 font-semibold text-primary hover:underline">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Download Official Resume</span>
                            </a>
                        @else
                            <span class="text-text-muted italic">No resume attached</span>
                        @endif
                    </div>
                </div>

                <!-- Skills -->
                <div class="pt-3 border-t border-border">
                    <span class="text-xs font-semibold text-text-muted block mb-2">Verified IT Skills:</span>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($application->studentProfile->skills as $skill)
                            <span class="px-2.5 py-1 rounded-md text-[11px] font-medium bg-surface-2 border border-border text-text">
                                {{ $skill->name }}
                                <span class="text-text-muted text-[10px]">({{ ucfirst($skill->pivot->proficiency ?? 'competent') }})</span>
                            </span>
                        @empty
                            <span class="text-xs text-text-muted italic">No skills tagged.</span>
                        @endforelse
                    </div>
                </div>

                @if($application->cover_note)
                    <div class="pt-3 border-t border-border">
                        <span class="text-xs font-semibold text-text-muted block mb-1">Candidate Cover Letter:</span>
                        <div class="p-3 rounded-lg bg-surface-2 border border-border text-xs text-text italic">
                            "{{ $application->cover_note }}"
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar Column: Opportunity Specs -->
        <div class="space-y-6">
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-text text-sm">Opportunity Overview</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-text-muted block">Position:</span>
                        <span class="font-bold text-text">{{ $application->opportunity->title }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Available Slots:</span>
                        <span class="font-semibold text-text">{{ $application->opportunity->slotsAvailable() }} of {{ $application->opportunity->slots_total }} open</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Work Arrangement:</span>
                        <span class="font-medium text-text">{{ $application->opportunity->work_arrangement->label() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
