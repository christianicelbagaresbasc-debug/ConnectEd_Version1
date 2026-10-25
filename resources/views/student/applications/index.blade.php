<x-app-layout>
    @section('title', 'My Applications')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">My Internship Applications</h1>
                <p class="text-xs text-text-muted mt-0.5">Track your submitted OJT applications, received offers, and confirmation status (F-014, F-015).</p>
            </div>
            <div>
                <a href="{{ route('opportunities.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <span>Browse More Opportunities</span>
                </a>
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
    @if (session('info'))
        <div class="mb-6 p-4 rounded-xl bg-info/10 border border-info/30 text-info text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <span>{{ session('info') }}</span>
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

    <!-- Active Placement Banner if Student is already placed -->
    @if($studentProfile->has_active_placement && $studentProfile->activePlacement)
        <div class="mb-6 p-5 rounded-xl bg-primary/10 border border-primary/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-success animate-pulse"></span>
                    <h3 class="font-bold text-sm text-text">Active Internship Placement Confirmed</h3>
                </div>
                <p class="text-xs text-text-muted">You are currently placed at <strong class="text-text">{{ $studentProfile->activePlacement->company->name }}</strong>.</p>
            </div>
            <a href="{{ route('student.placements.show', $studentProfile->activePlacement) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                <span>View Placement & Submit Weekly Reports</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>
    @endif

    <!-- Applications List -->
    <div class="space-y-4">
        @forelse($applications as $app)
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-border pb-3">
                    <div>
                        <span class="text-xs font-semibold text-text-muted uppercase tracking-wider">{{ $app->opportunity->company->name }}</span>
                        <h2 class="text-base font-bold text-text">
                            <a href="{{ route('student.applications.show', $app) }}" class="hover:text-primary transition">
                                {{ $app->opportunity->title }}
                            </a>
                        </h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $app->status->badgeClasses() }}">
                            {{ $app->status->label() }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <span class="text-text-muted block text-[11px]">Submitted Date:</span>
                        <span class="font-medium text-text">{{ $app->submitted_at ? $app->submitted_at->format('M d, Y h:i A') : 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block text-[11px]">Work Arrangement:</span>
                        <span class="font-medium text-text">{{ $app->opportunity->work_arrangement->label() }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block text-[11px]">Location:</span>
                        <span class="font-medium text-text">{{ $app->opportunity->location ?? 'Bulacan' }}</span>
                    </div>
                </div>

                <!-- Offer Received Banner & Dual Confirmation Actions -->
                @if($app->isAccepted())
                    <div class="p-4 rounded-xl bg-primary/5 border border-primary/20 space-y-3">
                        <div class="flex items-center gap-2 text-primary font-bold text-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                            <span>Action Required: You have received an official internship offer!</span>
                        </div>
                        <p class="text-xs text-text-muted">
                            Confirm your acceptance below to finalize your placement. Note that accepting an offer will automatically withdraw your other open applications (Assumption A6).
                        </p>

                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            @if(! $app->student_confirmed_at)
                                <form method="POST" action="{{ route('student.applications.confirm', $app) }}">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-lg bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                                        Confirm & Accept Offer
                                    </button>
                                </form>

                                <!-- Decline Modal -->
                                <div x-data="{ open: false }">
                                    <button @click="open = true" type="button" class="px-3 py-2 text-xs font-medium rounded-lg text-danger hover:bg-danger/10 border border-danger/20 transition">
                                        Decline Offer
                                    </button>

                                    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" style="display: none;">
                                        <div @click.outside="open = false" class="bg-surface border border-border rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
                                            <h3 class="text-sm font-bold text-danger">Decline Internship Offer</h3>
                                            <p class="text-xs text-text-muted">Are you sure you want to decline this offer from {{ $app->opportunity->company->name }}?</p>
                                            <form method="POST" action="{{ route('student.applications.decline', $app) }}" class="space-y-4">
                                                @csrf
                                                <div>
                                                    <label class="block text-xs font-semibold text-text-muted mb-1">Reason for declining (Optional)</label>
                                                    <textarea name="reason" rows="2" class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text" placeholder="e.g. Schedule conflict or accepted another opportunity..."></textarea>
                                                </div>
                                                <div class="flex justify-end gap-2 pt-2">
                                                    <button @click="open = false" type="button" class="px-3 py-1.5 text-xs font-medium rounded-lg text-text hover:bg-surface-2">Cancel</button>
                                                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-danger text-white shadow">Decline Offer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-xs text-success font-semibold flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>You confirmed this offer on {{ $app->student_confirmed_at->format('M d, Y h:i A') }}. Awaiting coordinator endorsement.</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-between pt-2 border-t border-border text-xs">
                    <a href="{{ route('student.applications.show', $app) }}" class="font-semibold text-primary hover:underline">
                        View Application Details & Audit Trail &rarr;
                    </a>

                    @if(in_array($app->status->value, ['pending', 'under_review']))
                        <div x-data="{ open: false }">
                            <button @click="open = true" type="button" class="text-xs text-danger hover:underline">
                                Withdraw Application
                            </button>

                            <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" style="display: none;">
                                <div @click.outside="open = false" class="bg-surface border border-border rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
                                    <h3 class="text-sm font-bold text-danger">Withdraw Application</h3>
                                    <p class="text-xs text-text-muted">Are you sure you want to withdraw your application for {{ $app->opportunity->title }}?</p>
                                    <form method="POST" action="{{ route('student.applications.withdraw', $app) }}" class="space-y-4">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-semibold text-text-muted mb-1">Reason (Optional)</label>
                                            <textarea name="reason" rows="2" class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text" placeholder="Reason for withdrawing..."></textarea>
                                        </div>
                                        <div class="flex justify-end gap-2 pt-2">
                                            <button @click="open = false" type="button" class="px-3 py-1.5 text-xs font-medium rounded-lg text-text hover:bg-surface-2">Cancel</button>
                                            <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-danger text-white shadow">Confirm Withdraw</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-surface border border-border rounded-xl p-12 text-center text-text-muted">
                <svg class="w-12 h-12 mx-auto text-text-muted/40 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <h3 class="font-bold text-sm text-text">No Applications Submitted Yet</h3>
                <p class="text-xs text-text-muted mt-1 max-w-sm mx-auto">Browse verified company opportunities aligned with your skills and submit your first application.</p>
                <div class="mt-4">
                    <a href="{{ route('opportunities.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                        Browse OJT Opportunities
                    </a>
                </div>
            </div>
        @endforelse

        @if($applications->hasPages())
            <div class="p-4 bg-surface border border-border rounded-xl">
                {{ $applications->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
