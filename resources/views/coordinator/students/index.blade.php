<x-app-layout>
    @section('title', 'ICS Students Directory')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">ICS Students Directory</h1>
                <p class="text-xs text-text-muted mt-0.5">Directory and cohort monitoring of approved ICS student interns (FR-03, FR-18).</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('coordinator.students.pending') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                    <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>Pending Approvals</span>
                </a>
            </div>
        </div>
    </x-slot>

    <!-- BR-20 Rule Reminder Banner -->
    <div class="mb-6 p-4 rounded-xl bg-surface border border-border text-xs text-text-muted flex items-start gap-3 shadow-sm">
        <div class="w-7 h-7 rounded-lg bg-surface-2 flex items-center justify-center text-text-muted flex-shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>
        <div>
            <strong class="text-text font-semibold">Institutional Rule BR-20:</strong>
            Students are listed alphabetically by name. Match scores and rankings are confidential to students and are never calculated or displayed here.
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-surface border border-border rounded-xl p-4 sm:p-5 mb-6 shadow-sm">
        <form method="GET" action="{{ route('coordinator.students.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label for="search" class="block text-xs font-semibold text-text-muted mb-1">Search Student</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        id="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search by name, student number, or email..." 
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    />
                </div>
            </div>

            <!-- Program Filter -->
            <div>
                <label for="program" class="block text-xs font-semibold text-text-muted mb-1">Degree Program</label>
                <select 
                    name="program" 
                    id="program" 
                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                >
                    <option value="">All Programs</option>
                    @foreach ($allowedPrograms as $prog)
                        <option value="{{ $prog }}" {{ request('program') === $prog ? 'selected' : '' }}>{{ $prog }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Placement Status Filter -->
            <div>
                <label for="placement_status" class="block text-xs font-semibold text-text-muted mb-1">Placement Status</label>
                <select 
                    name="placement_status" 
                    id="placement_status" 
                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                >
                    <option value="">All Students</option>
                    <option value="placed" {{ request('placement_status') === 'placed' ? 'selected' : '' }}>Placed</option>
                    <option value="unplaced" {{ request('placement_status') === 'unplaced' ? 'selected' : '' }}>Actively Seeking</option>
                </select>
            </div>

            <!-- Skill Filter -->
            <div>
                <label for="skill_id" class="block text-xs font-semibold text-text-muted mb-1">Acquired Skill</label>
                <select 
                    name="skill_id" 
                    id="skill_id" 
                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                >
                    <option value="">All Skills</option>
                    @foreach ($skills as $skill)
                        <option value="{{ $skill->id }}" {{ (string) request('skill_id') === (string) $skill->id ? 'selected' : '' }}>
                            {{ $skill->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-5 flex items-center justify-end gap-2 pt-2 border-t border-border">
                @if (request()->hasAny(['search', 'program', 'placement_status', 'skill_id']))
                    <a href="{{ route('coordinator.students.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium text-text-muted hover:text-text transition">
                        Reset Filters
                    </a>
                @endif
                <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                    Filter Directory
                </button>
            </div>
        </form>
    </div>

    <!-- Student Table Card -->
    <div class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-text divide-y divide-border">
                <thead class="bg-surface-2 text-text-muted font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Student</th>
                        <th class="py-3 px-4">Student #</th>
                        <th class="py-3 px-4">Program & Year</th>
                        <th class="py-3 px-4">Placement Status</th>
                        <th class="py-3 px-4">Skills Acquired</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($students as $student)
                        <tr class="hover:bg-surface-2/50 transition">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary font-bold flex items-center justify-center text-xs flex-shrink-0">
                                        {{ strtoupper(substr($student->user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-text">{{ $student->user->name }}</div>
                                        <div class="text-[11px] text-text-muted">{{ $student->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] text-text font-semibold">
                                {{ $student->student_number }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-text">{{ $student->program }}</span>
                                <span class="text-text-muted text-[11px] block">{{ $student->year_level }} &bull; Sec {{ $student->section ?? 'N/A' }}</span>
                            </td>
                            <td class="py-3 px-4">
                                @if ($student->has_active_placement)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-success/10 text-success border border-success/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                                        Placed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-warning/10 text-warning border border-warning/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-warning"></span>
                                        Seeking Placement
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-surface-2 border border-border">
                                    {{ $student->skills->count() }} skills
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('coordinator.students.show', $student) }}" class="inline-flex items-center gap-1 font-semibold text-primary hover:underline">
                                    <span>Profile</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="max-w-xs mx-auto text-center">
                                    <svg class="w-10 h-10 text-text-muted mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                    </svg>
                                    <div class="text-sm font-semibold text-text">No students found</div>
                                    <p class="text-xs text-text-muted mt-1">No active students match the specified search and filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($students->hasPages())
            <div class="p-4 border-t border-border">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
