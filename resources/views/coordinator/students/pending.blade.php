<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading font-bold text-2xl text-text leading-tight">Student Registration Approvals</h1>
                <p class="text-xs text-text-muted mt-0.5">Review and verify student registrations before granting access to OJT opportunities.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-warning/10 text-warning border border-warning/20">
                    {{ $pendingStudents->total() }} Pending Review
                </span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Status Messages -->
        @if (session('status'))
            <div class="p-4 rounded-lg bg-success/10 text-success text-xs font-semibold border border-success/20">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-lg bg-danger/10 text-danger text-xs font-semibold border border-danger/20">
                {{ session('error') }}
            </div>
        @endif

        <!-- Filter & Search Bar (Universal Filter System) -->
        <div class="bg-surface border border-border rounded-card p-4 shadow-sm">
            <form method="GET" action="{{ route('coordinator.students.pending') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                <div class="sm:col-span-6">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search by student name, number, or email..." 
                        class="w-full rounded-lg border-border bg-surface text-text text-xs focus:border-primary focus:ring-primary"
                    />
                </div>
                <div class="sm:col-span-4">
                    <select name="program" class="w-full rounded-lg border-border bg-surface text-text text-xs focus:border-primary focus:ring-primary">
                        <option value="">All Programs</option>
                        @foreach($allowedPrograms as $prog)
                            <option value="{{ $prog }}" {{ request('program') === $prog ? 'selected' : '' }}>{{ $prog }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-3 rounded-lg bg-primary text-primary-contrast text-xs font-semibold hover:opacity-95 transition">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'program']))
                        <a href="{{ route('coordinator.students.pending') }}" class="py-2 px-3 rounded-lg bg-surface-2 border border-border text-xs text-text-muted hover:text-text">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Pending Students Table / Cards -->
        <div class="bg-surface border border-border rounded-card shadow-sm overflow-hidden" x-data="{ rejectModal: false, selectedStudentId: null, selectedStudentName: '' }">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-surface-2 border-b border-border text-text-muted uppercase text-[10px] font-semibold">
                        <tr>
                            <th class="px-4 py-3">Student Name</th>
                            <th class="px-4 py-3">Student Number</th>
                            <th class="px-4 py-3">Program / Year</th>
                            <th class="px-4 py-3">Email Address</th>
                            <th class="px-4 py-3">Registered On</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border font-medium">
                        @forelse($pendingStudents as $student)
                            <tr class="hover:bg-surface-2/40 transition-colors">
                                <td class="px-4 py-3.5 text-text font-semibold">
                                    {{ $student->name }}
                                </td>
                                <td class="px-4 py-3.5 text-text font-mono">
                                    {{ $student->studentProfile?->student_number ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-primary/10 text-primary">
                                        {{ $student->studentProfile?->program }}
                                    </span>
                                    <span class="text-text-muted text-[11px] ml-1">
                                        {{ $student->studentProfile?->year_level }} {{ $student->studentProfile?->section ? "({$student->studentProfile?->section})" : '' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-text-muted font-mono text-[11px]">
                                    {{ $student->email }}
                                </td>
                                <td class="px-4 py-3.5 text-text-muted text-[11px]">
                                    {{ $student->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="px-4 py-3.5 text-right space-x-2">
                                    <!-- Approve Button Form -->
                                    <form method="POST" action="{{ route('coordinator.students.approve', $student) }}" class="inline">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            onclick="return confirm('Approve account for {{ $student->name }}?');"
                                            class="px-2.5 py-1.5 rounded-lg bg-success text-white font-semibold text-xs shadow hover:opacity-95 transition"
                                        >
                                            &check; Approve
                                        </button>
                                    </form>

                                    <!-- Reject Action Trigger -->
                                    <button 
                                        type="button" 
                                        @click="rejectModal = true; selectedStudentId = {{ $student->id }}; selectedStudentName = '{{ addslashes($student->name) }}';"
                                        class="px-2.5 py-1.5 rounded-lg bg-danger/10 text-danger border border-danger/20 font-semibold text-xs hover:bg-danger/20 transition"
                                    >
                                        &times; Reject
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-text-muted">
                                    <div class="max-w-sm mx-auto space-y-2">
                                        <div class="text-2xl font-bold font-heading text-text">No Pending Registrations</div>
                                        <p class="text-xs">All submitted student accounts have been reviewed and processed.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($pendingStudents->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $pendingStudents->links() }}
                </div>
            @endif

            <!-- Reject Reason Modal -->
            <div 
                x-show="rejectModal" 
                class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60"
                style="display: none;"
            >
                <div @click.outside="rejectModal = false" class="bg-surface border border-border rounded-card max-w-md w-full p-6 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-border">
                        <h3 class="font-heading font-bold text-base text-text">Reject Student Registration</h3>
                        <button type="button" @click="rejectModal = false" class="text-text-muted hover:text-text">&times;</button>
                    </div>

                    <p class="text-xs text-text-muted">
                        Please state the reason for rejecting <strong class="text-text" x-text="selectedStudentName"></strong>. The record will be archived.
                    </p>

                    <form :action="'/students/' + selectedStudentId + '/reject'" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-text-muted mb-1">Rejection Remark</label>
                            <textarea 
                                name="reason" 
                                rows="3" 
                                required 
                                placeholder="e.g. Invalid student number or not officially enrolled for OJT this semester." 
                                class="w-full rounded-lg border-border bg-surface text-text text-xs focus:border-primary focus:ring-primary"
                            ></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="rejectModal = false" class="px-3 py-1.5 rounded-lg border border-border text-xs text-text-muted hover:text-text">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-1.5 rounded-lg bg-danger text-white text-xs font-semibold hover:opacity-95">
                                Confirm Rejection
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
