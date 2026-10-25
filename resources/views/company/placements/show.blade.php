<x-app-layout>
    @section('title', 'Intern Overview')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('company.placements.index') }}" class="text-xs text-text-muted hover:text-text font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Back to Interns</span>
                    </a>
                </div>
                <h1 class="text-xl font-bold text-text">
                    Intern Progress: {{ $placement->studentProfile->user->name }}
                </h1>
                <p class="text-xs text-text-muted mt-0.5">{{ $placement->opportunity ? $placement->opportunity->title : 'Internship' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $placement->status->badgeClasses() }}">
                    {{ $placement->status->label() }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Column: Weekly Reports -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <h2 class="text-sm font-bold text-text">Weekly Accomplishment Reports</h2>
                    <span class="text-xs text-text-muted">{{ $placement->weeklyReports->count() }} Submissions</span>
                </div>

                <div class="space-y-3">
                    @forelse($placement->weeklyReports as $report)
                        <div class="p-4 rounded-xl bg-surface-2 border border-border space-y-2">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-text">Week of {{ $report->week_start->format('M d, Y') }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $report->status->badgeClasses() }}">
                                        {{ $report->status->label() }}
                                    </span>
                                </div>
                                <a href="{{ route('documents.download', $report->document) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Download Report Document</span>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-xs text-text-muted">
                            <p>No accomplishment reports submitted yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="space-y-6">
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-text text-sm">Intern Information</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-text-muted block">Student Name:</span>
                        <span class="font-bold text-text">{{ $placement->studentProfile->user->name }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Program:</span>
                        <span class="font-medium text-text">{{ $placement->studentProfile->program }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Email:</span>
                        <span class="font-medium text-text">{{ $placement->studentProfile->user->email }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Start Date:</span>
                        <span class="font-semibold text-text">{{ $placement->start_date ? $placement->start_date->format('M d, Y') : 'Pending' }}</span>
                    </div>
                    <div>
                        <span class="text-text-muted block">Target End Date:</span>
                        <span class="font-semibold text-text">{{ $placement->end_date ? $placement->end_date->format('M d, Y') : 'TBD' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
