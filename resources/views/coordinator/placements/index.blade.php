<x-app-layout>
    @section('title', 'OJT Placements')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">OJT Placements & Intern Monitoring</h1>
                <p class="text-xs text-text-muted mt-0.5">Track finalized student placements, start authorizations, and internship progress (F-015, F-016, BR-19).</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-success/10 text-success border border-success/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                    Rule BR-19: Single Active Placement Enforced
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

    <!-- Status Tabs -->
    <div class="flex items-center gap-1 overflow-x-auto pb-2 mb-6 border-b border-border text-xs">
        @php
            $currentStatus = request('status', 'all');
            $tabs = [
                'all' => ['label' => 'All Placements', 'count' => $counts['all']],
                'placed' => ['label' => 'Pending Start Authorization', 'count' => $counts['placed']],
                'authorized' => ['label' => 'Authorized to Start', 'count' => $counts['authorized']],
                'ongoing' => ['label' => 'Ongoing Internships', 'count' => $counts['ongoing']],
                'completed' => ['label' => 'Completed', 'count' => $counts['completed']],
                'terminated' => ['label' => 'Terminated', 'count' => $counts['terminated']],
            ];
        @endphp

        @foreach($tabs as $key => $tab)
            <a 
                href="{{ route('coordinator.placements.index', array_merge(request()->except('page'), ['status' => $key])) }}" 
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
        <form method="GET" action="{{ route('coordinator.placements.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <!-- Search Query -->
            <div class="lg:col-span-2">
                <label for="search" class="block text-xs font-semibold text-text-muted mb-1">Search Placements</label>
                <input 
                    type="text" 
                    name="search" 
                    id="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search by student name, student ID, company, or role..." 
                    class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                />
            </div>

            <!-- Active Filter -->
            <div>
                <label for="active" class="block text-xs font-semibold text-text-muted mb-1">Placement Active Status</label>
                <select 
                    name="active" 
                    id="active" 
                    class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                >
                    <option value="">All (Active & Concluded)</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active Placements Only</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Concluded Only (Completed/Terminated)</option>
                </select>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 text-xs font-semibold rounded-lg bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                    Filter
                </button>
                <a href="{{ route('coordinator.placements.index') }}" class="px-3 py-2 text-xs font-semibold rounded-lg bg-surface-2 text-text hover:bg-surface border border-border transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Placements Table -->
    <div class="bg-surface border border-border rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="border-b border-border bg-surface-2 text-text-muted uppercase text-[10px] font-semibold">
                    <tr>
                        <th class="px-4 py-3">Student Intern</th>
                        <th class="px-4 py-3">Host Partner Company</th>
                        <th class="px-4 py-3">Opportunity Role</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Start Date</th>
                        <th class="px-4 py-3">End Date</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border font-medium">
                    @forelse($placements as $placement)
                        <tr class="hover:bg-surface-2/40 transition">
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-text">{{ $placement->studentProfile->user->name }}</div>
                                <div class="text-[11px] text-text-muted">SN: {{ $placement->studentProfile->student_number }} &bull; {{ $placement->studentProfile->program }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-text">{{ $placement->company->name }}</div>
                                <div class="text-[11px] text-text-muted">{{ $placement->company->industry }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="text-text font-medium">{{ $placement->opportunity ? $placement->opportunity->title : 'Direct Placement' }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $placement->status->badgeClasses() }}">
                                    {{ $placement->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-text-muted whitespace-nowrap text-[11px]">
                                {{ $placement->start_date ? $placement->start_date->format('M d, Y') : 'Pending' }}
                            </td>
                            <td class="px-4 py-3.5 text-text-muted whitespace-nowrap text-[11px]">
                                {{ $placement->end_date ? $placement->end_date->format('M d, Y') : 'TBD' }}
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <a 
                                    href="{{ route('coordinator.placements.show', $placement) }}" 
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-primary hover:bg-primary hover:text-primary-contrast border border-border transition"
                                >
                                    <span>Manage</span>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                                <p class="font-medium text-xs">No placement records found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($placements->hasPages())
            <div class="p-4 border-t border-border">
                {{ $placements->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
