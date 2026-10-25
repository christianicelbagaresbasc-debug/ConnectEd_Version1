<x-app-layout>
    @section('title', 'Applicants Queue')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">Applicants Queue</h1>
                <p class="text-xs text-text-muted mt-0.5">Review student applicants for your posted OJT opportunities (F-014, BR-20).</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-primary/10 text-primary border border-primary/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                    Rule BR-20: Chronological Order (No Student Ranking)
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

    <!-- Applications Table -->
    <div class="bg-surface border border-border rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="border-b border-border bg-surface-2 text-text-muted uppercase text-[10px] font-semibold">
                    <tr>
                        <th class="px-4 py-3">Applicant Name</th>
                        <th class="px-4 py-3">Program</th>
                        <th class="px-4 py-3">Applied Position</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Submission Date</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border font-medium">
                    @forelse($applications as $app)
                        <tr class="hover:bg-surface-2/40 transition">
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-text">{{ $app->studentProfile->user->name }}</div>
                                <div class="text-[11px] text-text-muted">{{ $app->studentProfile->user->email }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="text-text font-medium">{{ $app->studentProfile->program }}</div>
                                <div class="text-[11px] text-text-muted">Section {{ $app->studentProfile->section }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-text">{{ $app->opportunity->title }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $app->status->badgeClasses() }}">
                                    {{ $app->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-text-muted whitespace-nowrap text-[11px]">
                                {{ $app->submitted_at ? $app->submitted_at->format('M d, Y h:i A') : 'N/A' }}
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <a 
                                    href="{{ route('company.applications.show', $app) }}" 
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-primary hover:bg-primary hover:text-primary-contrast border border-border transition"
                                >
                                    <span>Review Candidate</span>
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-text-muted">
                                <p>No student applications received yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($applications->hasPages())
            <div class="p-4 border-t border-border">
                {{ $applications->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
