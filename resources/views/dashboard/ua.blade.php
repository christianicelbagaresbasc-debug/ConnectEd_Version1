<x-app-layout>
    @section('title', 'University Attorney Legal Console')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">University Attorney Legal Console</h1>
                <p class="text-xs text-text-muted mt-0.5">Memorandum of Agreement legal vetting, institutional compliance, and risk notes (FR-21, FR-22).</p>
            </div>
            <div>
                <a href="{{ route('moas.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary text-white hover:bg-primary-600 transition">
                    <span>Legal Review Queue</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Legal Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-surface border border-warning/30 rounded-xl p-4 shadow-sm bg-warning/5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-warning">Awaiting Legal Review</span>
                    <span class="p-1.5 rounded-lg bg-warning/20 text-warning">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-warning">{{ $legalReviewQueue->count() }}</div>
                    <div class="text-xs text-text-muted mt-0.5">MOAs submitted for attorney review</div>
                </div>
            </div>

            <div class="bg-surface border border-border rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-text-muted">Executed MOAs</span>
                    <span class="p-1.5 rounded-lg bg-success/10 text-success">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-text">{{ $executedMoasCount }}</div>
                    <div class="text-xs text-success font-medium mt-0.5">Fully signed & active agreements</div>
                </div>
            </div>

            <div class="bg-surface border border-border rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-text-muted">Total Agreements</span>
                    <span class="p-1.5 rounded-lg bg-primary/10 text-primary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-text">{{ $totalMoasCount }}</div>
                    <div class="text-xs text-text-muted mt-0.5">{{ $expiredMoasCount }} expired in records</div>
                </div>
            </div>
        </div>

        <!-- Priority Legal Review Queue Table -->
        <div class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-border flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-text">MOAs Awaiting Legal Vetting</h3>
                    <p class="text-xs text-text-muted">Agreements currently queued in <span class="font-semibold text-warning">under_legal_review</span> state</p>
                </div>
                <a href="{{ route('moas.index') }}" class="text-xs font-semibold text-primary hover:underline">
                    Open Legal Queue &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-text divide-y divide-border">
                    <thead class="bg-surface-2 text-text-muted font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Partner Company</th>
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4">Current Custodian</th>
                            <th class="py-3 px-4">Legal Notes</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($legalReviewQueue as $moa)
                            <tr class="hover:bg-surface-2/40 transition">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-text">{{ $moa->company->name }}</div>
                                    <div class="text-[11px] text-text-muted">{{ $moa->type?->label() ?? 'Individual' }} &bull; SY {{ $moa->school_year ?? '2026-2027' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    @if ($moa->studentProfile)
                                        <div class="font-semibold text-text">{{ $moa->studentProfile->user->name }}</div>
                                        <div class="font-mono text-[11px] text-text-muted">{{ $moa->studentProfile->student_number }}</div>
                                    @else
                                        <span class="text-text-muted italic">Institutional MOA</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-2 border border-border">
                                        {{ $moa->current_custodian_party?->label() ?? 'Attorney' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-surface-2 border border-border">
                                        {{ $moa->notes->count() }} notes logged
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('moas.show', $moa) }}" class="inline-flex items-center gap-1 font-semibold text-primary hover:underline">
                                        <span>Review & Vetting</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-text-muted">
                                    <svg class="w-8 h-8 text-success mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <div class="font-semibold text-text">No agreements currently awaiting legal review.</div>
                                    <p class="text-xs text-text-muted mt-1">The legal queue is all caught up!</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Agreements List -->
        <div class="bg-surface border border-border rounded-xl shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border">
                <div>
                    <h3 class="text-sm font-bold text-text">Active & Executed Agreements</h3>
                    <p class="text-xs text-text-muted">Memoranda of Agreement with partner organizations</p>
                </div>
                <a href="{{ route('moas.index') }}" class="text-xs font-semibold text-primary hover:underline">
                    All MOAs &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @forelse ($recentAgreements as $moa)
                    <div class="p-3.5 rounded-xl border border-border bg-surface-2/40 flex flex-col justify-between space-y-2">
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-xs text-text truncate">{{ $moa->company->name }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold border {{ $moa->status->badgeClasses() }}">
                                    {{ $moa->status->label() }}
                                </span>
                            </div>
                            @if ($moa->studentProfile)
                                <div class="text-[11px] text-text-muted mt-1">
                                    Student: {{ $moa->studentProfile->user->name }}
                                </div>
                            @endif
                        </div>
                        <a href="{{ route('moas.show', $moa) }}" class="text-xs font-semibold text-primary hover:underline pt-1">
                            View MOA &rarr;
                        </a>
                    </div>
                @empty
                    <div class="col-span-3 py-6 text-center text-xs text-text-muted">
                        No active agreements recorded yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
