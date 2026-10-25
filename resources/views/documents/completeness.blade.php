<x-app-layout>
    @section('title', 'Document Completeness Matrix')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">Document Completeness Matrix</h1>
                <p class="text-xs text-text-muted mt-0.5">Institutional audit matrix for student intern credentials and partner company accreditation (FR-27).</p>
            </div>
            <div>
                <a href="{{ route('document-library.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                    <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <span>Browse All Documents</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div x-data="{ activeTab: 'students' }" class="space-y-6">
        <!-- Matrix View Tabs -->
        <div class="flex items-center gap-2 border-b border-border pb-3">
            <button 
                @click="activeTab = 'students'"
                :class="activeTab === 'students' ? 'bg-primary text-white shadow-sm' : 'bg-surface text-text-muted hover:text-text border border-border'"
                class="px-4 py-2 rounded-xl text-xs font-semibold transition"
            >
                Student Credential Matrix
            </button>
            <button 
                @click="activeTab = 'companies'"
                :class="activeTab === 'companies' ? 'bg-primary text-white shadow-sm' : 'bg-surface text-text-muted hover:text-text border border-border'"
                class="px-4 py-2 rounded-xl text-xs font-semibold transition"
            >
                Partner Company Verification Matrix
            </button>
        </div>

        <!-- Student Completeness Matrix -->
        <div x-show="activeTab === 'students'" class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-border">
                <h2 class="text-sm font-bold text-text">Student Internship Credential Audit</h2>
                <p class="text-xs text-text-muted mt-0.5">Verification of mandatory resumes, placement authorizations, and coordinator assignments.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-text divide-y divide-border">
                    <thead class="bg-surface-2 text-text-muted font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4">Student #</th>
                            <th class="py-3 px-4">Resume / CV</th>
                            <th class="py-3 px-4">Privacy Consent</th>
                            <th class="py-3 px-4">Supervising COC</th>
                            <th class="py-3 px-4">Placement Status</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($students as $student)
                            <tr class="hover:bg-surface-2/40 transition">
                                <td class="py-3 px-4 font-semibold text-text">
                                    {{ $student->user->name }}
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-text font-semibold">
                                    {{ $student->student_number }}
                                </td>
                                <td class="py-3 px-4">
                                    @if ($student->resume)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-success">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                            Uploaded
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-warning">
                                            Missing
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if ($student->consented_at)
                                        <span class="text-success font-medium text-[11px]">Consented</span>
                                    @else
                                        <span class="text-danger font-medium text-[11px]">Pending</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    {{ $student->assignedCoc?->name ?? 'Unassigned' }}
                                </td>
                                <td class="py-3 px-4">
                                    @if ($student->has_active_placement)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-success/10 text-success border border-success/20">
                                            Placed
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-warning/10 text-warning border border-warning/20">
                                            Seeking
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('coordinator.students.show', $student) }}" class="text-primary font-semibold hover:underline">
                                        View Profile &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-xs text-text-muted">
                                    No student profiles found.
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

        <!-- Partner Company Accreditation Matrix -->
        <div x-show="activeTab === 'companies'" x-cloak class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-border">
                <h2 class="text-sm font-bold text-text">Partner Company Accreditation & 6-Point Checklist Matrix</h2>
                <p class="text-xs text-text-muted mt-0.5">Verification checklist completion for Existing Partners and New Company intakes (FR-06, FR-07).</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-text divide-y divide-border">
                    <thead class="bg-surface-2 text-text-muted font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Company</th>
                            <th class="py-3 px-4">Classification</th>
                            <th class="py-3 px-4">Accreditation Status</th>
                            <th class="py-3 px-4">Checklist Items Verified</th>
                            <th class="py-3 px-4">MOA Status</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($companies as $comp)
                            @php
                                $verifiedCount = $comp->verificationItems->where('status', \App\Enums\VerificationItemStatus::ACCEPTED)->count();
                                $totalItems = $comp->verificationItems->count();
                            @endphp
                            <tr class="hover:bg-surface-2/40 transition">
                                <td class="py-3 px-4 font-bold text-text">
                                    {{ $comp->name }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $comp->classification->badgeClasses() }}">
                                        {{ $comp->classification->label() }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $comp->verification_status->badgeClasses() }}">
                                        {{ $comp->verification_status->label() }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-text">{{ $verifiedCount }} / {{ $totalItems > 0 ? $totalItems : 6 }} verified</span>
                                </td>
                                <td class="py-3 px-4">
                                    @if ($comp->activeMoa->isNotEmpty())
                                        <span class="text-success font-semibold text-[11px]">Active Executed MOA</span>
                                    @else
                                        <span class="text-text-muted text-[11px] italic">No active agreement</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('coordinator.companies.verification', $comp) }}" class="text-primary font-semibold hover:underline">
                                        Checklist &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-xs text-text-muted">
                                    No companies recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($companies->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $companies->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
