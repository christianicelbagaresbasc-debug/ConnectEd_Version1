<x-app-layout>
    @section('title', 'Skill Distribution & Demand Report')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">Skill Distribution & Demand Report</h1>
                <p class="text-xs text-text-muted mt-0.5">Analysis of student-acquired competencies versus skills required by partner opportunities (FR-18, FR-21).</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('reports.skills.csv', request()->query()) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-primary text-white hover:bg-primary-600 transition shadow-sm">
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
            <form method="GET" action="{{ route('reports.skills') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Category -->
                <div>
                    <label for="category" class="block text-xs font-semibold text-text-muted mb-1">Skill Category</label>
                    <select 
                        name="category" 
                        id="category" 
                        class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    >
                        <option value="all">All Skill Categories</option>
                        @foreach ($skillCategories as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full py-2 px-4 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                        Filter Report
                    </button>
                    @if (request()->hasAny(['category']))
                        <a href="{{ route('reports.skills') }}" class="py-2 px-3 rounded-lg text-xs font-semibold text-text-muted hover:text-text transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Skills Report Table -->
        <div class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-text divide-y divide-border">
                    <thead class="bg-surface-2 text-text-muted font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Technical Skill</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Acquired by Students</th>
                            <th class="py-3 px-4">Demanded (Required)</th>
                            <th class="py-3 px-4">Demanded (Preferred)</th>
                            <th class="py-3 px-4 text-right">Total Demand</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($skills as $skill)
                            @php
                                $totalDemand = ($skill->required_count ?? 0) + ($skill->preferred_count ?? 0);
                            @endphp
                            <tr class="hover:bg-surface-2/40 transition">
                                <td class="py-3 px-4 font-bold text-text">
                                    {{ $skill->name }}
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    {{ $skill->category ?? 'Technical' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $skill->status->badgeClasses() }}">
                                        {{ $skill->status->label() }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-semibold text-primary">
                                    {{ $skill->student_skills_count ?? 0 }} students
                                </td>
                                <td class="py-3 px-4 text-text font-medium">
                                    {{ $skill->required_count ?? 0 }} openings
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    {{ $skill->preferred_count ?? 0 }} openings
                                </td>
                                <td class="py-3 px-4 text-right font-bold {{ $totalDemand > 0 ? 'text-success' : 'text-text-muted' }}">
                                    {{ $totalDemand }} listings
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-xs text-text-muted">
                                    No skills found for the selected category.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($skills->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $skills->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
