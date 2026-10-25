<x-app-layout>
    @section('title', 'Applications Queue')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">OJT Applications Queue</h1>
                <p class="text-xs text-text-muted mt-0.5">Review and manage student internship applications and formal offer decisions (F-014, BR-20).</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-primary/10 text-primary border border-primary/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                    Rule BR-20: Non-Ranking Application Queue
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

    <!-- Status Tabs -->
    <div class="flex items-center gap-1 overflow-x-auto pb-2 mb-6 border-b border-border text-xs">
        @php
            $currentStatus = request('status', 'all');
            $tabs = [
                'all' => ['label' => 'All Applications', 'count' => $counts['all']],
                'pending' => ['label' => 'Pending Review', 'count' => $counts['pending']],
                'under_review' => ['label' => 'Under Review', 'count' => $counts['under_review']],
                'accepted' => ['label' => 'Offers (Accepted)', 'count' => $counts['accepted']],
                'placed' => ['label' => 'Placed', 'count' => $counts['placed']],
                'rejected' => ['label' => 'Rejected', 'count' => $counts['rejected']],
                'withdrawn' => ['label' => 'Withdrawn', 'count' => $counts['withdrawn']],
                'declined' => ['label' => 'Declined', 'count' => $counts['declined']],
            ];
        @endphp

        @foreach($tabs as $key => $tab)
            <a 
                href="{{ route('coordinator.applications.index', array_merge(request()->except('page'), ['status' => $key])) }}" 
                class="px-3 py-2 rounded-lg font-medium whitespace-nowrap transition flex items-center gap-2 {{ $currentStatus === $key ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text-muted hover:text-text hover:bg-surface-2' }}"
            >
                <span>{{ $tab['label'] }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $currentStatus === $key ? 'bg-primary-contrast/20 text-primary-contrast' : 'bg-surface-2 text-text-muted border border-border' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-surface border border-border rounded-xl p-4 sm:p-5 mb-6 shadow-sm">
        <form method="GET" action="{{ route('coordinator.applications.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <!-- Search Query -->
            <div class="lg:col-span-2">
                <label for="search" class="block text-xs font-semibold text-text-muted mb-1">Search Applicants</label>
                <input 
                    type="text" 
                    name="search" 
                    id="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search by student name, student number, or role title..." 
                    class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                />
            </div>

            <!-- Company Filter -->
            <div>
                <label for="company_id" class="block text-xs font-semibold text-text-muted mb-1">Filter by Partner Company</label>
                <select 
                    name="company_id" 
                    id="company_id" 
                    class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                >
                    <option value="">All Partner Companies</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 text-xs font-semibold rounded-lg bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                    Filter
                </button>
                <a href="{{ route('coordinator.applications.index') }}" class="px-3 py-2 text-xs font-semibold rounded-lg bg-surface-2 text-text hover:bg-surface border border-border transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Applications Table -->
    <div class="bg-surface border border-border rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="border-b border-border bg-surface-2 text-text-muted uppercase text-[10px] font-semibold">
                    <tr>
                        <th class="px-4 py-3">Student Applicant</th>
                        <th class="px-4 py-3">Program & Section</th>
                        <th class="px-4 py-3">Opportunity & Company</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Dual Confirmation</th>
                        <th class="px-4 py-3">Submitted At</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border font-medium">
                    @forelse($applications as $app)
                        <tr class="hover:bg-surface-2/40 transition">
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-text">{{ $app->studentProfile->user->name }}</div>
                                <div class="text-[11px] text-text-muted">SN: {{ $app->studentProfile->student_number }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="text-text font-medium">{{ $app->studentProfile->program }}</div>
                                <div class="text-[11px] text-text-muted">{{ $app->studentProfile->year_level }} - Sec {{ $app->studentProfile->section }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-text">{{ $app->opportunity->title }}</div>
                                <div class="text-[11px] text-text-muted">{{ $app->opportunity->company->name }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $app->status->badgeClasses() }}">
                                    {{ $app->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($app->isPlaced())
                                    <span class="inline-flex items-center gap-1 text-[11px] text-success font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Dual Confirmed
                                    </span>
                                @elseif($app->isAccepted())
                                    <div class="space-y-0.5 text-[10px]">
                                        <div class="{{ $app->student_confirmed_at ? 'text-success font-semibold' : 'text-text-muted' }}">
                                            Student: {{ $app->student_confirmed_at ? 'Confirmed' : 'Pending' }}
                                        </div>
                                        <div class="{{ $app->oc_confirmed_at ? 'text-success font-semibold' : 'text-text-muted' }}">
                                            Coordinator: {{ $app->oc_confirmed_at ? 'Endorsed' : 'Pending' }}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-text-muted text-[11px]">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-text-muted whitespace-nowrap text-[11px]">
                                {{ $app->submitted_at ? $app->submitted_at->format('M d, Y h:i A') : 'N/A' }}
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <a 
                                    href="{{ route('coordinator.applications.show', $app) }}" 
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-primary hover:bg-primary hover:text-primary-contrast border border-border transition"
                                >
                                    <span>Review</span>
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-text-muted">
                                <svg class="w-10 h-10 mx-auto text-text-muted/50 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                <p class="font-medium text-xs">No applications found matching your criteria.</p>
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
