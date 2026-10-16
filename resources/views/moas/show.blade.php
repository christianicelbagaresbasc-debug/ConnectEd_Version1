<x-app-layout>
    @section('title', ($moa->moa_number ?: 'MOA #' . $moa->id) . ' — ' . $moa->company->name)

    <x-slot name="header">
        <div class="flex items-center gap-2 text-xs text-text-muted mb-1">
            <a href="{{ route('moas.index') }}" class="hover:text-primary transition">MOAs</a>
            <span>/</span>
            <span class="text-text font-medium">{{ $moa->moa_number ?: 'MOA #' . $moa->id }}</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text truncate">
                    {{ $moa->company->name }} — {{ $moa->moa_number ?: 'MOA #' . $moa->id }}
                </h1>
                <p class="text-xs text-text-muted mt-0.5">
                    {{ $moa->type->label() }} &bull; Academic Year {{ $moa->school_year }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                @can('update', $moa)
                    <a href="{{ route('moas.edit', $moa) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                        <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                        </svg>
                        <span>Edit Details</span>
                    </a>
                @endcan

                @can('delete', $moa)
                    <form method="POST" action="{{ route('moas.destroy', $moa) }}" onsubmit="return confirm('Archive this MOA record? It will be moved to the Archive Center.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-lg text-danger hover:bg-danger/10 border border-transparent hover:border-danger/20 transition" title="Archive MOA">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <!-- Session Feedback -->
    @if (session('status'))
        <div class="mb-6 p-4 rounded-xl bg-success/10 border border-success/30 text-success text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Main Hero Card -->
    <div class="bg-surface border border-border rounded-xl p-6 sm:p-8 mb-6 shadow-sm" x-data="{ transferOpen: false, versionOpen: false, statusOpen: false }">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-surface-2 text-text border border-border">
                        {{ $moa->type->label() }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $moa->status->badgeClasses() }}">
                        {{ $moa->status->label() }}
                    </span>
                </div>

                <h2 class="text-xl font-bold text-text mb-1">
                    <a href="{{ route('coordinator.companies.show', $moa->company) }}" class="hover:text-primary transition">
                        {{ $moa->company->name }}
                    </a>
                </h2>

                @if ($moa->studentProfile)
                    <p class="text-xs text-text-muted mb-2 font-medium">
                        Student Intern: <strong class="text-text">{{ $moa->studentProfile->user->name }}</strong> ({{ $moa->studentProfile->student_number }})
                    </p>
                @endif

                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-text-muted mt-3">
                    <div>
                        Effective: <strong class="text-text">{{ $moa->effective_date?->format('M d, Y') ?? 'Pending Execution' }}</strong>
                    </div>
                    <div>
                        Expires: <strong class="text-text">{{ $moa->expiry_date?->format('M d, Y') ?? 'N/A' }}</strong>
                    </div>
                    <div>
                        Drafted by: <strong class="text-text">{{ $moa->creator?->name ?? 'Coordinator' }}</strong>
                    </div>
                </div>
            </div>

            <!-- Current Custody Callout Box (Rule BR-16) -->
            <div class="bg-surface-2 border border-border rounded-xl p-4 min-w-[260px] flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-text-muted block mb-1">
                        Current Document Custody (BR-16)
                    </span>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold {{ $moa->current_custodian_party->badgeClasses() }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            {{ $moa->current_custodian_party->label() }}
                        </span>
                    </div>
                    @if ($moa->currentCustodianUser)
                        <p class="text-[11px] text-text-muted">
                            Holding Officer: <strong class="text-text">{{ $moa->currentCustodianUser->name }}</strong>
                        </p>
                    @endif
                </div>

                @can('transferCustody', $moa)
                    <div class="mt-3 pt-2.5 border-t border-border flex items-center justify-between">
                        <button 
                            @click="transferOpen = true" 
                            type="button" 
                            class="w-full py-1.5 px-3 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition text-center"
                        >
                            Transfer Custody &rarr;
                        </button>
                    </div>
                @endcan
            </div>
        </div>

        <!-- Summary text if any -->
        @if ($moa->summary)
            <div class="mt-6 pt-4 border-t border-border">
                <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted mb-1">Agreement Terms / Covenants</h3>
                <p class="text-xs text-text leading-relaxed whitespace-pre-line">
                    {{ $moa->summary }}
                </p>
            </div>
        @endif

        <!-- Quick Status Update Bar for Staff -->
        @can('update', $moa)
            <div class="mt-6 pt-4 border-t border-border flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-text-muted">Lifecycle Stage:</span>
                    <strong class="text-text">{{ $moa->status->label() }}</strong>
                </div>

                <div class="flex items-center gap-2">
                    <button 
                        @click="statusOpen = true" 
                        type="button" 
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition"
                    >
                        Update Agreement Status
                    </button>
                    <button 
                        @click="versionOpen = true" 
                        type="button" 
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition"
                    >
                        + Upload Document Version
                    </button>
                </div>
            </div>
        @endcan

        <!-- Custody Transfer Modal (BR-16) -->
        <div 
            x-show="transferOpen" 
            style="display: none;" 
            class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4"
        >
            <div @click.outside="transferOpen = false" class="bg-surface border border-border rounded-2xl max-w-md w-full p-6 shadow-xl">
                <h3 class="text-base font-bold text-text mb-1">Transfer MOA Custody</h3>
                <p class="text-xs text-text-muted mb-4">
                    Rule BR-16: Document custody flows between parties in any order recorded by humans. Record the holding party and person.
                </p>

                <form method="POST" action="{{ route('moas.custody.transfer', $moa) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="to_party" class="block text-xs font-semibold text-text mb-1">Receiving Party <span class="text-danger">*</span></label>
                        <select 
                            name="to_party" 
                            id="to_party" 
                            required 
                            class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                        >
                            <option value="oc">OJT Coordinator (OC Office)</option>
                            <option value="coc">Co-Coordinator (COC)</option>
                            <option value="ua">University Attorney (Legal Office)</option>
                            <option value="company">Partner Company</option>
                            <option value="student">Student</option>
                        </select>
                    </div>

                    <div>
                        <label for="note" class="block text-xs font-semibold text-text mb-1">Transfer Note / Tracking Reason <span class="text-danger">*</span></label>
                        <textarea 
                            name="note" 
                            id="note" 
                            rows="3" 
                            required 
                            minlength="3" 
                            placeholder="e.g. Delivered physical paper copies to Legal Counsel for review and clearance." 
                            class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                        ></textarea>
                    </div>

                    <div class="pt-3 border-t border-border flex items-center justify-end gap-2">
                        <button @click="transferOpen = false" type="button" class="px-3 py-1.5 text-xs font-medium text-text-muted hover:text-text rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-primary text-primary-contrast rounded-lg shadow hover:opacity-90 transition">
                            Log Custody Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Upload New Version Modal -->
        <div 
            x-show="versionOpen" 
            style="display: none;" 
            class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4"
        >
            <div @click.outside="versionOpen = false" class="bg-surface border border-border rounded-2xl max-w-md w-full p-6 shadow-xl">
                <h3 class="text-base font-bold text-text mb-1">Upload New MOA Version</h3>
                <p class="text-xs text-text-muted mb-4">
                    Upload a revised draft, signed copy, or final notarized document.
                </p>

                <form method="POST" action="{{ route('moas.versions.store', $moa) }}" class="space-y-4">
                    @csrf
                    <!-- Dropzone Slot Component -->
                    <div>
                        <label class="block text-xs font-semibold text-text mb-1">Document Attachment <span class="text-danger">*</span></label>
                        <x-dropzone 
                            name="document_id" 
                            category="moa" 
                            maxSize="10485760" 
                            accept=".pdf,.docx" 
                            hint="Upload PDF or DOCX agreement copy (Max 10MB)"
                        />
                    </div>

                    <div>
                        <label for="version_note" class="block text-xs font-semibold text-text mb-1">Version Notes / Revision Summary</label>
                        <input 
                            type="text" 
                            name="note" 
                            id="version_note" 
                            placeholder="e.g. Added insurance clause requested by Legal Office" 
                            class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                        />
                    </div>

                    <div class="pt-3 border-t border-border flex items-center justify-end gap-2">
                        <button @click="versionOpen = false" type="button" class="px-3 py-1.5 text-xs font-medium text-text-muted hover:text-text rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-primary text-primary-contrast rounded-lg shadow hover:opacity-90 transition">
                            Save Version
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Status Transition Modal -->
        <div 
            x-show="statusOpen" 
            style="display: none;" 
            class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4"
        >
            <div @click.outside="statusOpen = false" class="bg-surface border border-border rounded-2xl max-w-md w-full p-6 shadow-xl">
                <h3 class="text-base font-bold text-text mb-1">Update MOA Status</h3>
                <p class="text-xs text-text-muted mb-4">
                    Record formal transition across the memorandum lifecycle.
                </p>

                <form method="POST" action="{{ route('moas.status.update', $moa) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="status_select" class="block text-xs font-semibold text-text mb-1">Target Status <span class="text-danger">*</span></label>
                        <select 
                            name="status" 
                            id="status_select" 
                            required 
                            class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                        >
                            <option value="draft" {{ $moa->status->value === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="under_legal_review" {{ $moa->status->value === 'under_legal_review' ? 'selected' : '' }}>Under Legal Review (Forward to UA)</option>
                            <option value="for_signing_school" {{ $moa->status->value === 'for_signing_school' ? 'selected' : '' }}>For University Signing</option>
                            <option value="for_signing_company" {{ $moa->status->value === 'for_signing_company' ? 'selected' : '' }}>For Company Signing</option>
                            <option value="notarized" {{ $moa->status->value === 'notarized' ? 'selected' : '' }}>Notarized</option>
                            <option value="active" {{ $moa->status->value === 'active' ? 'selected' : '' }}>Active / Executed</option>
                            <option value="expired" {{ $moa->status->value === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="terminated" {{ $moa->status->value === 'terminated' ? 'selected' : '' }}>Terminated</option>
                        </select>
                    </div>

                    <div>
                        <label for="status_remark" class="block text-xs font-semibold text-text mb-1">Transition Remark</label>
                        <input 
                            type="text" 
                            name="remark" 
                            id="status_remark" 
                            placeholder="e.g. Fully signed by University President and Company President" 
                            class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                        />
                    </div>

                    <div class="pt-3 border-t border-border flex items-center justify-end gap-2">
                        <button @click="statusOpen = false" type="button" class="px-3 py-1.5 text-xs font-medium text-text-muted hover:text-text rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-primary text-primary-contrast rounded-lg shadow hover:opacity-90 transition">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Versions & Custody History -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Versions & Files -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted">Document Version History</h3>
                </div>

                @if ($moa->versions->isEmpty())
                    <div class="p-4 rounded-xl bg-surface-2 text-center text-xs text-text-muted">
                        No digital files attached to this agreement yet.
                    </div>
                @else
                    <div class="divide-y divide-border">
                        @foreach ($moa->versions as $version)
                            <div class="py-3 flex items-center justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold text-xs flex-shrink-0">
                                        v{{ $version->version_no }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-text">
                                            {{ $version->document->original_filename }}
                                        </div>
                                        <p class="text-[11px] text-text-muted">
                                            Uploaded by {{ $version->uploader->name }} &bull; {{ $version->created_at->format('M d, Y H:i') }}
                                            @if ($version->note)
                                                &bull; <span class="italic">"{{ $version->note }}"</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <a href="{{ route('documents.download', $version->document) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition flex items-center gap-1.5 flex-shrink-0">
                                    <svg class="w-3.5 h-3.5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    Download
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Custody Transfer Log (Rule BR-16: Unconstrained) -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted mb-4">
                    Custody Chain Timeline (Rule BR-16)
                </h3>

                @if ($moa->transfers->isEmpty())
                    <p class="text-xs text-text-muted text-center py-4">No custody transfers recorded.</p>
                @else
                    <div class="relative pl-4 space-y-4 before:absolute before:left-1 before:top-2 before:bottom-2 before:w-0.5 before:bg-border">
                        @foreach ($moa->transfers as $transfer)
                            <div class="relative text-xs">
                                <div class="absolute -left-[19px] top-1 w-2.5 h-2.5 rounded-full bg-primary border-2 border-surface"></div>
                                <div class="flex items-center justify-between text-[11px] text-text-muted mb-0.5">
                                    <span class="font-semibold text-text">Transferred by {{ $transfer->actor?->name ?? 'System' }}</span>
                                    <span>{{ $transfer->created_at->format('M d, Y H:i') }}</span>
                                </div>
                                <div class="text-text font-medium text-xs mb-1">
                                    @if ($transfer->from_party)
                                        <span class="text-text-muted">{{ $transfer->from_party->label() }}</span> &rarr;
                                    @endif
                                    <span class="font-bold text-primary">{{ $transfer->to_party->label() }}</span>
                                </div>
                                @if ($transfer->note)
                                    <p class="text-[11px] text-text-muted bg-surface-2 p-2 rounded-lg border border-border">
                                        "{{ $transfer->note }}"
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right 1 Col: Rule BR-18 Legal Review & Notes (Hidden from PC and Student) -->
        <div class="space-y-6">
            @can('viewInternalNotes', $moa)
                <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted">
                            University Legal Notes (Rule BR-18)
                        </h3>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                            Internal Staff Only
                        </span>
                    </div>
                    <p class="text-[11px] text-text-muted leading-relaxed">
                        These legal comments, risk levels, and revision directives are confidential to university counsel (OC, COC, UA) and strictly omitted from student and partner company views.
                    </p>

                    <!-- Add Note Form -->
                    @can('addInternalNote', $moa)
                        <form method="POST" action="{{ route('attorney.moas.notes.store', $moa) }}" class="space-y-3 pt-2 border-t border-border">
                            @csrf
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[11px] font-semibold text-text mb-1">Note Type</label>
                                    <select name="type" required class="w-full py-1.5 px-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition">
                                        <option value="legal_remark">Legal Counsel Remark</option>
                                        <option value="revision">Required Revision</option>
                                        <option value="general">General Internal Note</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-text mb-1">Risk Assessment</label>
                                    <select name="risk_level" class="w-full py-1.5 px-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition">
                                        <option value="">No Risk Tag</option>
                                        <option value="low">Low Risk</option>
                                        <option value="medium">Medium Risk</option>
                                        <option value="high">High Risk</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <textarea name="body" rows="2" required minlength="5" placeholder="Enter confidential legal commentary or clause revision requirement..." class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"></textarea>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-primary text-primary-contrast rounded-lg shadow hover:opacity-90 transition">
                                    Log Internal Remark
                                </button>
                            </div>
                        </form>
                    @endcan

                    <!-- Notes List -->
                    <div class="space-y-3 pt-3 border-t border-border">
                        @forelse ($moa->notes as $note)
                            <div class="p-3 rounded-lg bg-surface-2 border border-border text-xs space-y-1.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-text">{{ $note->author->name }}</span>
                                    <div class="flex items-center gap-1">
                                        @if ($note->risk_level)
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase {{ $note->risk_level->badgeClasses() }}">
                                                {{ $note->risk_level->label() }}
                                            </span>
                                        @endif
                                        <span class="text-[10px] text-text-muted">{{ $note->created_at->format('M d, H:i') }}</span>
                                    </div>
                                </div>

                                <p class="text-xs text-text leading-relaxed">
                                    {{ $note->body }}
                                </p>

                                <div class="flex items-center justify-between pt-1 text-[11px] text-text-muted">
                                    <span class="capitalize">{{ str_replace('_', ' ', $note->type->value) }}</span>
                                    @if ($note->isResolved())
                                        <span class="text-success font-semibold flex items-center gap-1">
                                            &check; Resolved by {{ $note->resolver?->name ?? 'Counsel' }}
                                        </span>
                                    @else
                                        @can('resolveNote', $moa)
                                            <form method="POST" action="{{ route('attorney.moas.notes.resolve', [$moa, $note]) }}">
                                                @csrf
                                                <button type="submit" class="text-primary font-semibold hover:underline">
                                                    Mark Resolved
                                                </button>
                                            </form>
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-text-muted text-center py-2">No internal legal remarks recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            @else
                <!-- Non-staff notice -->
                <div class="bg-surface border border-border rounded-xl p-5 shadow-sm text-xs text-text-muted text-center">
                    <svg class="w-8 h-8 mx-auto text-success mb-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <strong class="font-semibold text-text block mb-1">Official University Partnership</strong>
                    This agreement is administered under BSAU ICS Internship policies. Contact the OJT Coordination Office for questions regarding MOA execution.
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>
