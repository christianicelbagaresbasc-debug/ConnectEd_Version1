<x-app-layout>
    @section('title', 'Company Suggestions Queue')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">Student Company Suggestions Queue</h1>
                <p class="text-xs text-text-muted mt-0.5">Review, convert, or dismiss prospective internship host training establishments submitted by students.</p>
            </div>
            <a href="{{ route('coordinator.companies.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                &larr; Back to Company Records
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

    <!-- Filter Card -->
    <div class="bg-surface border border-border rounded-xl p-4 sm:p-5 mb-6 shadow-sm">
        <form method="GET" action="{{ route('coordinator.suggestions.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <label for="search" class="block text-xs font-semibold text-text-muted mb-1">Search Suggestions</label>
                <input 
                    type="text" 
                    name="search" 
                    id="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search by company name, student, or notes..." 
                    class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                />
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold text-text-muted mb-1">Status</label>
                <select 
                    name="status" 
                    id="status" 
                    onchange="this.form.submit()" 
                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                >
                    <option value="">All Statuses</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted (Pending)</option>
                    <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Converted to Company</option>
                    <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Suggestions List -->
    @if ($suggestions->isEmpty())
        <div class="bg-surface border border-border rounded-xl p-12 text-center">
            <div class="w-12 h-12 mx-auto rounded-full bg-surface-2 flex items-center justify-center text-text-muted mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-text">No company suggestions found</h3>
            <p class="text-xs text-text-muted mt-1 max-w-sm mx-auto">No student submissions match your query. Students can suggest prospective HTEs from their student portal.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($suggestions as $suggestion)
                <div x-data="{ convertOpen: false, dismissOpen: false }" class="bg-surface border border-border rounded-xl p-5 shadow-sm hover:border-primary/30 transition">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                <h3 class="text-base font-bold text-text truncate">{{ $suggestion->company_name }}</h3>
                                @php
                                    $badgeClass = match($suggestion->status->value) {
                                        'converted' => 'bg-success/10 text-success border-success/20',
                                        'submitted' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                        'dismissed' => 'bg-danger/10 text-danger border-danger/20',
                                        default => 'bg-surface-2 text-text-muted border-border',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold border {{ $badgeClass }}">
                                    {{ $suggestion->status->label() }}
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-text-muted mb-3">
                                <div>
                                    Suggested by <strong class="text-text">{{ $suggestion->studentProfile->user->name }}</strong> 
                                    ({{ $suggestion->studentProfile->student_number }})
                                </div>
                                <div>&bull;</div>
                                <div>Submitted on {{ $suggestion->created_at->format('M d, Y H:i') }}</div>
                            </div>

                            @if ($suggestion->contact_details)
                                <div class="text-xs text-text-muted mb-2">
                                    <strong class="font-semibold text-text">Contact Details:</strong>
                                    {{ $suggestion->contact_details }}
                                </div>
                            @endif

                            @if ($suggestion->notes)
                                <div class="text-xs bg-surface-2 border border-border rounded-lg p-3 text-text leading-relaxed">
                                    <strong class="font-semibold text-text-muted text-[11px] block mb-0.5">Student Notes / Reason:</strong>
                                    {{ $suggestion->notes }}
                                </div>
                            @endif

                            @if ($suggestion->company)
                                <div class="mt-3 flex items-center gap-2 text-xs">
                                    <span class="text-text-muted">Converted to Company Record:</span>
                                    <a href="{{ route('coordinator.companies.show', $suggestion->company) }}" class="font-bold text-primary hover:underline">
                                        {{ $suggestion->company->name }} &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Actions for Submitted suggestions -->
                        @if ($suggestion->status->value === 'submitted')
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <button 
                                    @click="convertOpen = true" 
                                    type="button" 
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition"
                                >
                                    Convert to New Company
                                </button>
                                <button 
                                    @click="dismissOpen = true" 
                                    type="button" 
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-danger border border-border hover:bg-danger/10 transition"
                                >
                                    Dismiss
                                </button>
                            </div>
                        @elseif ($suggestion->handled_at)
                            <div class="text-right text-[11px] text-text-muted flex-shrink-0">
                                <div>Handled by <strong class="text-text">{{ $suggestion->handler?->name ?? 'Coordinator' }}</strong></div>
                                <div>{{ $suggestion->handled_at->format('M d, Y') }}</div>
                            </div>
                        @endif
                    </div>

                    <!-- Convert to Company Modal -->
                    <div 
                        x-show="convertOpen" 
                        style="display: none;" 
                        class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4"
                    >
                        <div @click.outside="convertOpen = false" class="bg-surface border border-border rounded-2xl max-w-lg w-full p-6 shadow-xl">
                            <h3 class="text-base font-bold text-text mb-1">Convert Suggestion to New Company</h3>
                            <p class="text-xs text-text-muted mb-4">
                                This will create a verified New Company record and initialize the 5-point verification checklist.
                            </p>

                            <form method="POST" action="{{ route('coordinator.suggestions.convert', $suggestion) }}" class="space-y-4">
                                @csrf

                                <div>
                                    <label class="block text-xs font-semibold text-text mb-1">Company Name</label>
                                    <input type="text" value="{{ $suggestion->company_name }}" disabled class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface-2 text-text opacity-80" />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label for="industry_{{ $suggestion->id }}" class="block text-xs font-semibold text-text mb-1">Industry</label>
                                        <input type="text" name="industry" id="industry_{{ $suggestion->id }}" placeholder="e.g. IT Solutions" class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition" />
                                    </div>

                                    <div>
                                        <label for="city_{{ $suggestion->id }}" class="block text-xs font-semibold text-text mb-1">City / Municipality</label>
                                        <input type="text" name="city" id="city_{{ $suggestion->id }}" placeholder="e.g. Malolos, Bulacan" class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition" />
                                    </div>
                                </div>

                                <div>
                                    <label for="address_{{ $suggestion->id }}" class="block text-xs font-semibold text-text mb-1">Office Address</label>
                                    <input type="text" name="address" id="address_{{ $suggestion->id }}" placeholder="Street, Building, Barangay" class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition" />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label for="contact_person_{{ $suggestion->id }}" class="block text-xs font-semibold text-text mb-1">Contact Person</label>
                                        <input type="text" name="contact_person" id="contact_person_{{ $suggestion->id }}" placeholder="HR or Manager Name" class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition" />
                                    </div>

                                    <div>
                                        <label for="contact_email_{{ $suggestion->id }}" class="block text-xs font-semibold text-text mb-1">Contact Email</label>
                                        <input type="email" name="contact_email" id="contact_email_{{ $suggestion->id }}" placeholder="hr@company.com" class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition" />
                                    </div>
                                </div>

                                <div>
                                    <label for="bsit_relevance_note_{{ $suggestion->id }}" class="block text-xs font-semibold text-text mb-1">BSIT Curriculum Fit</label>
                                    <textarea name="bsit_relevance_note" id="bsit_relevance_note_{{ $suggestion->id }}" rows="2" placeholder="Relevance to BSIT internship competencies..." class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"></textarea>
                                </div>

                                <div class="pt-3 border-t border-border flex items-center justify-end gap-2">
                                    <button @click="convertOpen = false" type="button" class="px-3 py-1.5 text-xs font-medium text-text-muted hover:text-text rounded-lg transition">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-primary text-primary-contrast rounded-lg shadow hover:opacity-90 transition">
                                        Confirm & Create Company
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Dismiss Modal -->
                    <div 
                        x-show="dismissOpen" 
                        style="display: none;" 
                        class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4"
                    >
                        <div @click.outside="dismissOpen = false" class="bg-surface border border-border rounded-2xl max-w-md w-full p-6 shadow-xl">
                            <h3 class="text-base font-bold text-text mb-1">Dismiss Suggestion</h3>
                            <p class="text-xs text-text-muted mb-4">
                                Provide an optional note explaining why this company is not eligible for BSAU ICS internship.
                            </p>

                            <form method="POST" action="{{ route('coordinator.suggestions.dismiss', $suggestion) }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="reason_{{ $suggestion->id }}" class="block text-xs font-semibold text-text mb-1">Dismissal Reason / Remark</label>
                                    <textarea name="reason" id="reason_{{ $suggestion->id }}" rows="3" placeholder="e.g. Unresponsive, outside allowable geographic zone, or unrelated industry." class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"></textarea>
                                </div>

                                <div class="pt-3 border-t border-border flex items-center justify-end gap-2">
                                    <button @click="dismissOpen = false" type="button" class="px-3 py-1.5 text-xs font-medium text-text-muted hover:text-text rounded-lg transition">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-danger text-white rounded-lg shadow hover:opacity-90 transition">
                                        Dismiss Suggestion
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $suggestions->links() }}
        </div>
    @endif
</x-app-layout>
