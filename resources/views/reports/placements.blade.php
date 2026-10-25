<x-app-layout>
    @section('title', 'Placements Summary Report')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">Placements Summary Report</h1>
                <p class="text-xs text-text-muted mt-0.5">Cohort monitoring of student intern placement agreements and start authorizations (FR-18, FR-21).</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('reports.placements.csv', request()->query()) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-primary text-white hover:bg-primary-600 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Export CSV (RFC 4180)</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Filter Card -->
        <div class="bg-surface border border-border rounded-xl p-4 sm:p-5 shadow-sm">
            <form method="GET" action="{{ route('reports.placements') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Status -->
                <div>
                    <label for="status" class="block text-xs font-semibold text-text-muted mb-1">Placement Status</label>
                    <select 
                        name="status" 
                        id="status" 
                        class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    >
                        <option value="all">All Placement States</option>
                        <option value="placed" {{ request('status') === 'placed' ? 'selected' : '' }}>Placed</option>
                        <option value="authorized" {{ request('status') === 'authorized' ? 'selected' : '' }}>Authorized to Start</option>
                        <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Ongoing Training</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                    </select>
                </div>

                <!-- School Year -->
                <div>
                    <label for="school_year" class="block text-xs font-semibold text-text-muted mb-1">Academic Year</label>
                    <select 
                        name="school_year" 
                        id="school_year" 
                        class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    >
                        <option value="all">All School Years</option>
                        @foreach ($schoolYears as $sy)
                            <option value="{{ $sy }}" {{ request('school_year') === $sy ? 'selected' : '' }}>SY {{ $sy }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full py-2 px-4 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                        Filter Report
                    </button>
                    @if (request()->hasAny(['status', 'school_year']))
                        <a href="{{ route('reports.placements') }}" class="py-2 px-3 rounded-lg text-xs font-semibold text-text-muted hover:text-text transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Placements Report Table -->
        <div class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-text divide-y divide-border">
                    <thead class="bg-surface-2 text-text-muted font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4">Student #</th>
                            <th class="py-3 px-4">Program & Year</th>
                            <th class="py-3 px-4">Host Company</th>
                            <th class="py-3 px-4">Opportunity</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Authorized Start</th>
                            <th class="py-3 px-4">SY</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($placements as $placement)
                            <tr class="hover:bg-surface-2/40 transition">
                                <td class="py-3 px-4 font-bold text-text">
                                    {{ $placement->studentProfile?->user?->name ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] font-semibold text-text">
                                    {{ $placement->studentProfile?->student_number ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    {{ $placement->studentProfile?->program }} &bull; Sec {{ $placement->studentProfile?->section ?? 'A' }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-text">
                                    {{ $placement->company?->name ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    {{ $placement->opportunity?->title ?? 'Manual Placement' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $placement->status->badgeClasses() }}">
                                        {{ $placement->status->label() }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    {{ $placement->start_date?->format('M d, Y') ?? 'Not started' }}
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-text-muted">
                                    {{ $placement->school_year ?? '2026-2027' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-xs text-text-muted">
                                    No placements match the selected criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($placements->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $placements->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
