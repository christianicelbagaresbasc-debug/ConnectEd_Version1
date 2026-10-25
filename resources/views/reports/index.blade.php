<x-app-layout>
    @section('title', 'Institutional OJT Reports Hub')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">Institutional OJT Reports Hub</h1>
                <p class="text-xs text-text-muted mt-0.5">CHED and institutional compliance reporting, placement statistics, and CSV data exports (FR-18, FR-21).</p>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary/10 text-primary border border-primary/20">
                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                    <span>RFC 4180 Streaming Enabled</span>
                </span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Summary KPI Counters -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-surface border border-border rounded-xl p-4 shadow-sm">
                <div class="text-xs font-semibold text-text-muted">Active Student Cohort</div>
                <div class="text-2xl font-bold text-text mt-1">{{ $totalStudents }}</div>
                <div class="text-xs text-success font-medium mt-0.5">{{ $placedStudents }} students placed</div>
            </div>

            <div class="bg-surface border border-border rounded-xl p-4 shadow-sm">
                <div class="text-xs font-semibold text-text-muted">Placement Rate</div>
                <div class="text-2xl font-bold text-success mt-1">
                    {{ $totalStudents > 0 ? round(($placedStudents / $totalStudents) * 100) : 0 }}%
                </div>
                <div class="text-xs text-text-muted mt-0.5">Overall cohort placement</div>
            </div>

            <div class="bg-surface border border-border rounded-xl p-4 shadow-sm">
                <div class="text-xs font-semibold text-text-muted">Partner Companies</div>
                <div class="text-2xl font-bold text-text mt-1">{{ $totalCompanies }}</div>
                <div class="text-xs text-primary font-medium mt-0.5">{{ $existingPartners }} Existing Partners</div>
            </div>

            <div class="bg-surface border border-border rounded-xl p-4 shadow-sm">
                <div class="text-xs font-semibold text-text-muted">Cataloged Skills</div>
                <div class="text-2xl font-bold text-text mt-1">{{ $totalSkills }}</div>
                <div class="text-xs text-text-muted mt-0.5">IT competencies tracked</div>
            </div>
        </div>

        <!-- Report Modules 3-Card Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- 1. Placements Summary Report -->
            <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-text">Placements Summary Report</h2>
                    <p class="text-xs text-text-muted leading-relaxed">
                        Comprehensive record of confirmed student intern placements, host partner companies, authorized start/end dates, and school year cohorts.
                    </p>
                </div>

                <div class="pt-4 border-t border-border flex flex-col sm:flex-row items-center gap-2">
                    <a href="{{ route('reports.placements') }}" class="w-full sm:w-1/2 py-2 text-center rounded-xl text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                        View Report
                    </a>
                    <a href="{{ route('reports.placements.csv') }}" class="w-full sm:w-1/2 py-2 text-center rounded-xl text-xs font-semibold bg-primary text-white hover:bg-primary-600 transition flex items-center justify-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span>Export CSV</span>
                    </a>
                </div>
            </div>

            <!-- 2. Partner Companies Accreditation Report -->
            <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-success/10 text-success flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-text">Company Accreditation Report</h2>
                    <p class="text-xs text-text-muted leading-relaxed">
                        Accreditation audit of industry partner enterprises, 6-point verification checklist completion, executed MOAs, and total allocated intern slots.
                    </p>
                </div>

                <div class="pt-4 border-t border-border flex flex-col sm:flex-row items-center gap-2">
                    <a href="{{ route('reports.companies') }}" class="w-full sm:w-1/2 py-2 text-center rounded-xl text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                        View Report
                    </a>
                    <a href="{{ route('reports.companies.csv') }}" class="w-full sm:w-1/2 py-2 text-center rounded-xl text-xs font-semibold bg-primary text-white hover:bg-primary-600 transition flex items-center justify-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span>Export CSV</span>
                    </a>
                </div>
            </div>

            <!-- 3. Skill Distribution & Industry Demand Report -->
            <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-warning/10 text-warning flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-text">Skill Distribution & Demand</h2>
                    <p class="text-xs text-text-muted leading-relaxed">
                        Curriculum and industry alignment analysis comparing student-acquired technical competencies with requirements posted in partner opportunities.
                    </p>
                </div>

                <div class="pt-4 border-t border-border flex flex-col sm:flex-row items-center gap-2">
                    <a href="{{ route('reports.skills') }}" class="w-full sm:w-1/2 py-2 text-center rounded-xl text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                        View Report
                    </a>
                    <a href="{{ route('reports.skills.csv') }}" class="w-full sm:w-1/2 py-2 text-center rounded-xl text-xs font-semibold bg-primary text-white hover:bg-primary-600 transition flex items-center justify-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span>Export CSV</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
