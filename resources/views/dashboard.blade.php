<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading font-bold text-2xl text-text leading-tight">
                    {{ Auth::user()->role->label() }} Dashboard
                </h1>
                <p class="text-xs text-text-muted mt-0.5">
                    Welcome back, {{ Auth::user()->name }} &bull; Session active for A.Y. {{ config('connected.current_school_year', '2026-2027') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-success/10 text-success border border-success/20">
                    <span class="w-2 h-2 rounded-full bg-success"></span>
                    Account Active
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-primary/10 text-primary border border-primary/20">
                    Role: {{ Auth::user()->role->value }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @php
            $role = Auth::user()->role->value;
        @endphp

        <!-- Quick Metrics Overview Cards (LinkedIn-style stat tiles) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @if($role === 'oc')
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Students Cohort</span>
                    <div class="text-3xl font-bold font-heading text-text">124</div>
                    <span class="text-[11px] text-success font-medium">108 active &bull; 16 unplaced</span>
                </div>
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Partner Companies</span>
                    <div class="text-3xl font-bold font-heading text-text">42</div>
                    <span class="text-[11px] text-primary font-medium">38 Existing &bull; 4 New</span>
                </div>
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Active MOAs</span>
                    <div class="text-3xl font-bold font-heading text-text">29</div>
                    <span class="text-[11px] text-warning font-medium">5 awaiting legal review</span>
                </div>
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Pending Placements</span>
                    <div class="text-3xl font-bold font-heading text-text">14</div>
                    <span class="text-[11px] text-accent font-medium">Awaiting coordinator confirm</span>
                </div>
            @elseif($role === 'coc')
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Assigned Students</span>
                    <div class="text-3xl font-bold font-heading text-text">28</div>
                    <span class="text-[11px] text-success font-medium">24 placed &bull; 4 in progress</span>
                </div>
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">My Tasks</span>
                    <div class="text-3xl font-bold font-heading text-text">6</div>
                    <span class="text-[11px] text-warning font-medium">2 due this week</span>
                </div>
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Weekly Reports</span>
                    <div class="text-3xl font-bold font-heading text-text">19</div>
                    <span class="text-[11px] text-primary font-medium">15 reviewed &bull; 4 pending</span>
                </div>
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Student Inquiries</span>
                    <div class="text-3xl font-bold font-heading text-text">3</div>
                    <span class="text-[11px] text-text-muted font-medium">Open tickets</span>
                </div>
            @elseif($role === 'ua')
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">MOA Legal Queue</span>
                    <div class="text-3xl font-bold font-heading text-text">5</div>
                    <span class="text-[11px] text-warning font-medium">Awaiting legal review</span>
                </div>
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Revisions Requested</span>
                    <div class="text-3xl font-bold font-heading text-text">2</div>
                    <span class="text-[11px] text-accent font-medium">Pending partner reply</span>
                </div>
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Executed MOAs</span>
                    <div class="text-3xl font-bold font-heading text-text">34</div>
                    <span class="text-[11px] text-success font-medium">Accredited A.Y. 2026-2027</span>
                </div>
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Legal Remarks Log</span>
                    <div class="text-3xl font-bold font-heading text-text">18</div>
                    <span class="text-[11px] text-text-muted font-medium">Internal review notes</span>
                </div>
            @elseif($role === 'pc')
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Active Openings</span>
                    <div class="text-3xl font-bold font-heading text-text">3</div>
                    <span class="text-[11px] text-success font-medium">8 slots total</span>
                </div>
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Student Applicants</span>
                    <div class="text-3xl font-bold font-heading text-text">12</div>
                    <span class="text-[11px] text-primary font-medium">New submissions</span>
                </div>
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Active Interns</span>
                    <div class="text-3xl font-bold font-heading text-text">4</div>
                    <span class="text-[11px] text-text-muted font-medium">BSAU ICS Students</span>
                </div>
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Verification Status</span>
                    <div class="text-xl font-bold font-heading text-success mt-2">Verified Partner</div>
                    <span class="text-[11px] text-text-muted font-medium">Valid through 2027</span>
                </div>
            @elseif($role === 'student')
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Best-Fit Matches</span>
                    <div class="text-3xl font-bold font-heading text-text">8</div>
                    <span class="text-[11px] text-success font-medium">Based on your skills</span>
                </div>
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">My Applications</span>
                    <div class="text-3xl font-bold font-heading text-text">2</div>
                    <span class="text-[11px] text-primary font-medium">1 under review &bull; 1 pending</span>
                </div>
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Placement Status</span>
                    <div class="text-xl font-bold font-heading text-warning mt-2">In Progress</div>
                    <span class="text-[11px] text-text-muted font-medium">Awaiting final placement</span>
                </div>
                <div class="bg-surface border border-border p-5 rounded-card shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-text-muted">Profile Complete</span>
                    <div class="text-3xl font-bold font-heading text-text">85%</div>
                    <span class="text-[11px] text-text-muted font-medium">Add 2 more skills</span>
                </div>
            @endif
        </div>

        <!-- Main Workspace Area -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Activity Feed & Priority Queue -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Priority Widget: Students Without a Company (for OC) -->
                @if($role === 'oc')
                    <div class="bg-surface border border-border rounded-card p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="font-heading font-semibold text-lg text-text">Students Without a Company</h2>
                                <p class="text-xs text-text-muted">Priority monitoring for students requiring placement assistance</p>
                            </div>
                            <span class="px-2.5 py-1 rounded text-xs font-bold bg-danger/10 text-danger border border-danger/20">
                                16 Unplaced
                            </span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="border-b border-border bg-surface-2 text-text-muted uppercase text-[10px] font-semibold">
                                    <tr>
                                        <th class="px-3 py-2">Student</th>
                                        <th class="px-3 py-2">Program</th>
                                        <th class="px-3 py-2">Applications</th>
                                        <th class="px-3 py-2">Assigned COC</th>
                                        <th class="px-3 py-2 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border font-medium">
                                    <tr>
                                        <td class="px-3 py-2.5 text-text font-semibold">Juan Dela Cruz</td>
                                        <td class="px-3 py-2.5">BSIT 4-A</td>
                                        <td class="px-3 py-2.5"><span class="text-warning">2 Pending</span></td>
                                        <td class="px-3 py-2.5 text-text-muted">Unassigned</td>
                                        <td class="px-3 py-2.5 text-right">
                                            <a href="#" class="text-primary hover:underline font-semibold">Assign &rarr;</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2.5 text-text font-semibold">Maria Santos</td>
                                        <td class="px-3 py-2.5">BSIT 4-B</td>
                                        <td class="px-3 py-2.5"><span class="text-danger">0 Submitted</span></td>
                                        <td class="px-3 py-2.5 text-text-muted">Unassigned</td>
                                        <td class="px-3 py-2.5 text-right">
                                            <a href="#" class="text-primary hover:underline font-semibold">Recommend &rarr;</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="bg-surface border border-border rounded-card p-6 shadow-sm space-y-3">
                        <h2 class="font-heading font-semibold text-lg text-text">Recent Updates & Activity</h2>
                        <p class="text-xs text-text-muted">Live synchronization with BSAU ICS OJT coordination database.</p>
                        <div class="p-4 rounded-lg bg-surface-2 border border-border text-xs text-text-muted">
                            No critical alerts at this time. All submissions up to date.
                        </div>
                    </div>
                @endif

                <!-- Attendance Scope Notice (Mandatory Placeholder per Spec) -->
                @if($role === 'oc')
                    <div class="bg-surface border border-dashed border-border rounded-card p-5 text-xs text-text-muted flex items-start gap-3">
                        <svg class="w-5 h-5 text-text-muted flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <strong class="font-semibold text-text">Scope Boundary Note: Attendance & DTR</strong>
                            <p class="mt-0.5">Biometric time recording, hourly logging, and payroll/allowance tracking are explicitly out of scope for ConnectEd Version 1. Student compliance is verified through uploaded weekly accomplishment reports and formal completion certificates.</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right 1 Col: Quick Actions & In-System Notifications Preview -->
            <div class="space-y-6">
                <!-- Notifications Mini-Feed (FR-17) -->
                <div class="bg-surface border border-border rounded-card p-5 shadow-sm space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-border">
                        <h3 class="font-heading font-semibold text-sm text-text">Notifications</h3>
                        <span class="text-[11px] text-primary hover:underline cursor-pointer">Mark all read</span>
                    </div>
                    <div class="space-y-2.5">
                        <div class="p-2.5 rounded-lg bg-surface-2 border border-border text-xs space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-text text-[11px]">System Notification</span>
                                <span class="text-[10px] text-text-muted">Just now</span>
                            </div>
                            <p class="text-text-muted text-[11px]">Welcome to ConnectEd Version 1. System initialized for A.Y. 2026-2027.</p>
                        </div>
                        <div class="p-2.5 rounded-lg bg-surface-2 border border-border text-xs space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-text text-[11px]">Verification Standard</span>
                                <span class="text-[10px] text-text-muted">Today</span>
                            </div>
                            <p class="text-text-muted text-[11px]">5 coordinator checklist items seeded for company accreditation.</p>
                        </div>
                    </div>
                </div>

                <!-- Account Information Summary -->
                <div class="bg-surface border border-border rounded-card p-5 shadow-sm space-y-2 text-xs">
                    <h3 class="font-heading font-semibold text-sm text-text">Account Session</h3>
                    <div class="space-y-1 text-text-muted pt-1">
                        <div><strong class="text-text">Email:</strong> {{ Auth::user()->email }}</div>
                        <div><strong class="text-text">Role Code:</strong> {{ Auth::user()->role->value }}</div>
                        <div><strong class="text-text">Storage Disk:</strong> {{ config('filesystems.default') }}</div>
                        <div><strong class="text-text">Database Host:</strong> PostgreSQL 16 (Neon)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
