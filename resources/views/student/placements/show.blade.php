<x-app-layout>
    @section('title', 'My Internship Placement')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">
                    My Internship Placement
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
        <!-- Main Column: Internship Overview & Weekly Reports -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Internship Authorization & Timeline Card -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-text">Placement & Authorization Information</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div class="p-3 rounded-lg bg-surface-2 border border-border">
                        <span class="text-text-muted block text-[11px]">Authorized Start Date:</span>
                        <span class="font-bold text-text text-sm">
                            {{ $placement->start_date ? $placement->start_date->format('M d, Y') : 'Pending Authorization' }}
                        </span>
                    </div>
                    <div class="p-3 rounded-lg bg-surface-2 border border-border">
                        <span class="text-text-muted block text-[11px]">Target End Date:</span>
                        <span class="font-bold text-text text-sm">
                            {{ $placement->end_date ? $placement->end_date->format('M d, Y') : 'Open Ended' }}
                        </span>
                    </div>
                    <div class="p-3 rounded-lg bg-surface-2 border border-border">
                        <span class="text-text-muted block text-[11px]">Academic School Year:</span>
                        <span class="font-bold text-text text-sm">{{ $placement->school_year }}</span>
                    </div>
                </div>

                @if($placement->start_authorization_note)
                    <div class="p-3 rounded-lg bg-surface-2 border border-border text-xs">
                        <strong class="text-text font-semibold">Coordinator Authorization Remarks:</strong>
                        <p class="text-text-muted mt-0.5">{{ $placement->start_authorization_note }}</p>
                    </div>
                @endif
            </div>

            <!-- Upload Weekly Report Card (Assumption A9) -->
            @if($placement->is_active)
                <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                    <div class="border-b border-border pb-3">
                        <h2 class="text-sm font-bold text-text">Submit Weekly Accomplishment Report (Assumption A9)</h2>
                        <p class="text-xs text-text-muted">Upload your weekly progress documentation (PDF or DOCX, max 10MB) for coordinator verification.</p>
                    </div>

                    <form method="POST" action="{{ route('student.placements.reports.store', $placement) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="week_start" class="block text-xs font-semibold text-text-muted mb-1">Week Of (Select any day in week) *</label>
                                <input type="date" name="week_start" id="week_start" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text">
                            </div>
                            <div>
                                <label for="report_file" class="block text-xs font-semibold text-text-muted mb-1">Accomplishment Report File (PDF/DOCX) *</label>
                                <input type="file" name="report_file" id="report_file" required accept=".pdf,.docx,.png,.jpg,.jpeg" class="w-full px-3 py-1.5 text-xs rounded-lg border border-border bg-surface text-text file:mr-3 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-primary-contrast hover:file:opacity-90">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-lg bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                                Submit Weekly Report
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Weekly Reports History -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <h2 class="text-sm font-bold text-text">Submitted Weekly Reports</h2>
                    <span class="text-xs text-text-muted font-medium">{{ $placement->weeklyReports->count() }} Submissions</span>
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
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('documents.download', $report->document) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        <span>Download File</span>
                                    </a>
                                </div>
                            </div>

                            @if($report->remark)
                                <div class="p-2.5 rounded-lg bg-surface border border-border text-xs">
                                    <strong class="text-text font-semibold">Coordinator Feedback:</strong>
                                    <p class="text-text-muted mt-0.5">{{ $report->remark }}</p>
                                    @if($report->reviewedBy)
                                        <span class="text-[10px] text-text-muted/80 block mt-1">Reviewed by {{ $report->reviewedBy->name }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8 text-xs text-text-muted">
                            <svg class="w-10 h-10 mx-auto text-text-muted/40 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <p>You have not submitted any weekly accomplishment reports yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Column: Host Company Information -->
        <div class="space-y-6">
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-text text-sm">Host Partner Company</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-text-muted block">Company Name:</span>
                        <span class="font-bold text-text">{{ $placement->company->name }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Industry Sector:</span>
                        <span class="font-medium text-text">{{ $placement->company->industry }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Office Address:</span>
                        <span class="font-medium text-text">{{ $placement->company->city }}, {{ $placement->company->address }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Contact Person:</span>
                        <span class="font-medium text-text">{{ $placement->company->contact_person }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Contact Email:</span>
                        <span class="font-medium text-text">{{ $placement->company->contact_email }}</span>
                    </div>
                </div>
            </div>

            <!-- Attendance Scope Notice -->
            <div class="bg-surface border border-dashed border-border rounded-xl p-4 text-xs text-text-muted flex items-start gap-2.5">
                <svg class="w-4 h-4 text-text-muted flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <strong class="text-text font-semibold">Accomplishment Reporting Notice</strong>
                    <p class="mt-0.5">Under BSAU ICS guidelines, student progress is verified by your coordinator via these uploaded weekly reports.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
