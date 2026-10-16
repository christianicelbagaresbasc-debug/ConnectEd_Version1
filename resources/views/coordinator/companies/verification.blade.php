<x-app-layout>
    @section('title', 'Verification Checklist — ' . $company->name)

    <x-slot name="header">
        <div class="flex items-center gap-2 text-xs text-text-muted mb-1">
            <a href="{{ route('coordinator.companies.index') }}" class="hover:text-primary transition">Companies</a>
            <span>/</span>
            <a href="{{ route('coordinator.companies.show', $company) }}" class="hover:text-primary transition">{{ $company->name }}</a>
            <span>/</span>
            <span class="text-text font-medium">Verification Checklist</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">6-Point Company Verification Checklist</h1>
                <p class="text-xs text-text-muted mt-0.5">Audit statutory licenses, tax compliance, mentorship program capacity, and workplace safety standards.</p>
            </div>
            <a href="{{ route('coordinator.companies.show', $company) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                &larr; Back to Company Profile
            </a>
        </div>
    </x-slot>

    <!-- Session Alerts -->
    @if (session('status'))
        <div class="mb-6 p-4 rounded-xl bg-success/10 border border-success/30 text-success text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 rounded-xl bg-danger/10 border border-danger/30 text-danger text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Overall Verification Decision Bar -->
    <div class="bg-surface border border-border rounded-xl p-6 mb-6 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Status Change Form -->
            <div class="lg:col-span-2">
                <div class="flex items-center gap-3 mb-3">
                    <h2 class="text-sm font-bold text-text">Overall Verification Decision</h2>
                    @php
                        $statusClasses = match($company->verification_status->value) {
                            'verified' => 'bg-success/10 text-success border-success/20',
                            'under_review' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20',
                            'pending' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                            'rejected', 'revoked' => 'bg-danger/10 text-danger border-danger/20',
                            default => 'bg-surface-2 text-text-muted border-border',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $statusClasses }}">
                        Current: {{ $company->verification_status->label() }}
                    </span>
                </div>
                <p class="text-xs text-text-muted mb-4">
                    Transitioning the verification status records a permanent event in the verification audit log. Verification is required before promotion to Existing Partner (BR-17).
                </p>

                <form method="POST" action="{{ route('coordinator.companies.verification.status.update', $company) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label for="verification_status" class="block text-xs font-semibold text-text mb-1">New Status <span class="text-danger">*</span></label>
                            <select 
                                name="verification_status" 
                                id="verification_status" 
                                required 
                                class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            >
                                <option value="pending" {{ $company->verification_status->value === 'pending' ? 'selected' : '' }}>Pending Review</option>
                                <option value="under_review" {{ $company->verification_status->value === 'under_review' ? 'selected' : '' }}>Under Review</option>
                                <option value="verified" {{ $company->verification_status->value === 'verified' ? 'selected' : '' }}>Verified (Approved)</option>
                                <option value="rejected" {{ $company->verification_status->value === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="revoked" {{ $company->verification_status->value === 'revoked' ? 'selected' : '' }}>Revoked</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="remark" class="block text-xs font-semibold text-text mb-1">Coordinator Audit Remark <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="remark" 
                                id="remark" 
                                required 
                                minlength="5" 
                                placeholder="State reason or statutory justification (e.g., All 5 permits verified on SEC & LGU portals)..." 
                                class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition @error('remark') border-danger @enderror"
                            />
                            @error('remark')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" class="px-4 py-2 text-xs font-semibold bg-primary text-primary-contrast rounded-lg shadow hover:opacity-90 transition">
                            Record Verification Decision
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right 1 Col: Scorecard & BR-17 Promotion Action -->
            <div class="bg-surface-2 border border-border rounded-xl p-4 flex flex-col justify-between">
                @php
                    $progress = $company->verificationProgressPercentage();
                    $verifiedItems = $company->verificationItems->where('status.value', 'accepted')->count();
                    $totalItems = $company->verificationItems->count();
                @endphp
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-bold text-text">Compliance Scorecard</span>
                        <span class="font-extrabold text-primary">{{ $verifiedItems }} of {{ $totalItems }} Items</span>
                    </div>
                    <div class="w-full h-2.5 bg-surface rounded-full overflow-hidden border border-border mb-2">
                        <div class="h-full bg-primary rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                    </div>
                    <p class="text-[11px] text-text-muted">
                        {{ $progress }}% of active checklist items are in <em>Accepted</em> status.
                    </p>
                </div>

                @if ($company->classification->value === 'new_company')
                    <div class="mt-4 pt-3 border-t border-border">
                        @if ($company->canBePromoted())
                            <form method="POST" action="{{ route('coordinator.companies.promote', $company) }}" onsubmit="return confirm('Promote this company to Existing Partner?');">
                                @csrf
                                <button type="submit" class="w-full py-2 px-3 rounded-lg text-xs font-bold bg-success text-white shadow hover:opacity-90 transition text-center">
                                    Promote to Existing Partner
                                </button>
                            </form>
                        @else
                            <div class="text-[11px] text-text-muted text-center italic">
                                Promotion to Existing Partner unlocks once verification is approved.
                            </div>
                        @endif
                    </div>
                @else
                    <div class="mt-4 pt-3 border-t border-border text-center">
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-success">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Existing Partner
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 6-Point Checklist Items -->
    <div class="space-y-4 mb-8">
        <h2 class="text-sm font-bold text-text">Statutory & Mentorship Checklist Items</h2>

        @foreach ($company->verificationItems as $item)
            <div x-data="{ expanded: false }" class="bg-surface border border-border rounded-xl shadow-sm transition overflow-hidden">
                <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start gap-4 flex-1">
                        <!-- Number/Icon -->
                        <div class="w-9 h-9 rounded-lg bg-surface-2 border border-border flex items-center justify-center font-bold text-xs text-text-muted flex-shrink-0 mt-0.5">
                            {{ $loop->iteration }}
                        </div>

                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="text-sm font-bold text-text">{{ $item->requirement->label }}</h3>
                                @if ($item->requirement->is_mandatory)
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold uppercase bg-danger/10 text-danger border border-danger/20">
                                        Mandatory
                                    </span>
                                @endif
                                @php
                                    $itemBadgeClass = match($item->status->value) {
                                        'accepted' => 'bg-success/10 text-success border-success/20',
                                        'submitted' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20',
                                        'rejected' => 'bg-danger/10 text-danger border-danger/20',
                                        default => 'bg-surface-2 text-text-muted border-border',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold border {{ $itemBadgeClass }}">
                                    {{ $item->status->label() }}
                                </span>
                            </div>
                            <p class="text-xs text-text-muted leading-relaxed">
                                {{ $item->requirement->description }}
                            </p>

                            <!-- Document or Reviewer meta -->
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-[11px] text-text-muted">
                                @if ($item->document)
                                    <div class="flex items-center gap-1 text-primary">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                                        </svg>
                                        <a href="{{ route('documents.download', $item->document) }}" class="underline font-semibold" target="_blank">
                                            {{ $item->document->original_filename }} ({{ number_format($item->document->size_bytes / 1024, 1) }} KB)
                                        </a>
                                    </div>
                                @else
                                    <span class="italic text-text-muted">No document file attached</span>
                                @endif

                                @if ($item->reviewer)
                                    <div>
                                        Reviewed by <strong class="text-text">{{ $item->reviewer->name }}</strong> &bull; {{ $item->reviewed_at?->format('M d, Y H:i') }}
                                    </div>
                                @endif
                            </div>

                            @if ($item->remark)
                                <div class="mt-2 text-xs bg-surface-2 border border-border rounded-lg p-2 text-text">
                                    <strong class="font-semibold text-text-muted text-[11px] block">Reviewer Remark:</strong>
                                    {{ $item->remark }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Toggle Review Controls -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button 
                            @click="expanded = !expanded" 
                            type="button" 
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition flex items-center gap-1.5"
                        >
                            <span x-text="expanded ? 'Hide Reviewer' : 'Review Item'"></span>
                            <svg class="w-3.5 h-3.5 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Expanded Review Form -->
                <div x-show="expanded" style="display: none;" class="px-5 pb-5 pt-3 border-t border-border bg-surface-2/40">
                    <form method="POST" action="{{ route('coordinator.companies.verification.items.update', [$company, $item]) }}" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="status_{{ $item->id }}" class="block text-xs font-semibold text-text mb-1">Item Status <span class="text-danger">*</span></label>
                                <select 
                                    name="status" 
                                    id="status_{{ $item->id }}" 
                                    required 
                                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                >
                                    <option value="missing" {{ $item->status->value === 'missing' ? 'selected' : '' }}>Missing (Not provided)</option>
                                    <option value="submitted" {{ $item->status->value === 'submitted' ? 'selected' : '' }}>Submitted (Pending check)</option>
                                    <option value="accepted" {{ $item->status->value === 'accepted' ? 'selected' : '' }}>Accepted (Compliant)</option>
                                    <option value="rejected" {{ $item->status->value === 'rejected' ? 'selected' : '' }}>Rejected (Deficient)</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="remark_{{ $item->id }}" class="block text-xs font-semibold text-text mb-1">Compliance Notes / Deficiency Details</label>
                                <input 
                                    type="text" 
                                    name="remark" 
                                    id="remark_{{ $item->id }}" 
                                    value="{{ old('remark', $item->remark) }}" 
                                    placeholder="e.g. Valid until Dec 2026. Checked on SEC i-View portal." 
                                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button 
                                @click="expanded = false" 
                                type="button" 
                                class="px-3 py-1.5 text-xs font-medium text-text-muted hover:text-text rounded-lg transition"
                            >
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-primary text-primary-contrast rounded-lg shadow hover:opacity-90 transition">
                                Save Item Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Verification Audit Trail (Immutable) -->
    <div class="bg-surface border border-border rounded-xl p-5 shadow-sm">
        <h2 class="text-sm font-bold text-text mb-1">Append-Only Verification Event Log</h2>
        <p class="text-xs text-text-muted mb-4">Every status mutation and coordinator review note is immutably logged for institutional accountability.</p>

        @if ($company->verificationLogs->isEmpty())
            <div class="p-4 rounded-xl bg-surface-2 text-center text-xs text-text-muted">
                No verification events recorded yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border text-[11px] font-bold uppercase tracking-wider text-text-muted">
                            <th class="py-2.5 px-3">Timestamp</th>
                            <th class="py-2.5 px-3">Coordinator / Actor</th>
                            <th class="py-2.5 px-3">State Transition</th>
                            <th class="py-2.5 px-3">Audit Remark</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border text-xs">
                        @foreach ($company->verificationLogs as $log)
                            <tr class="hover:bg-surface-2/50 transition">
                                <td class="py-2.5 px-3 text-text-muted whitespace-nowrap">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                                <td class="py-2.5 px-3 font-semibold text-text whitespace-nowrap">{{ $log->actor?->name ?? 'System' }}</td>
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    @if ($log->from_status)
                                        <span class="text-text-muted capitalize">{{ str_replace('_', ' ', $log->from_status) }}</span> &rarr;
                                    @endif
                                    <span class="font-bold text-primary capitalize">{{ str_replace('_', ' ', $log->to_status) }}</span>
                                </td>
                                <td class="py-2.5 px-3 text-text-muted max-w-md">{{ $log->remark ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
