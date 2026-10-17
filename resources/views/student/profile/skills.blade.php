<x-app-layout>
    @section('title', 'My Skills & Certifications')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">Skills & Competency Profile</h1>
                <p class="text-xs text-text-muted mt-0.5">
                    Maintain your technical skills and certifications to power your transparent OJT recommendations (FR-03, F-011).
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('student.recommendations.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                    </svg>
                    <span>View My Recommendations</span>
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Session Feedback -->
    @if (session('status'))
        <div class="mb-6 p-4 rounded-xl bg-success/10 border border-success/30 text-success text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-12">
        <!-- Left: Skills Profile (2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Current Skills List Card -->
            <div class="bg-surface border border-border rounded-xl p-5 sm:p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-text">My Acquired Skills ({{ $studentSkills->count() }})</h2>
                        <p class="text-xs text-text-muted mt-0.5">Skills listed here are matched against open partner opportunities.</p>
                    </div>
                </div>

                @if ($studentSkills->isEmpty())
                    <div class="py-8 text-center bg-surface-2/40 rounded-xl border border-dashed border-border p-6">
                        <svg class="w-10 h-10 text-text-muted mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <h3 class="text-xs font-bold text-text">No skills added to your profile yet</h3>
                        <p class="text-xs text-text-muted mt-1 max-w-sm mx-auto">
                            Add technical competencies from the catalog below so the recommendation engine can calculate your match scores.
                        </p>
                    </div>
                @else
                    <div class="space-y-2.5">
                        @foreach ($studentSkills as $sSkill)
                            <div class="p-3 rounded-lg border border-border bg-surface-2/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-primary/40 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary font-bold flex items-center justify-center text-xs flex-shrink-0">
                                        {{ strtoupper(substr($sSkill->skill->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-xs text-text">{{ $sSkill->skill->name }}</div>
                                        <div class="text-[11px] text-text-muted">{{ $sSkill->skill->category }}</div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 self-end sm:self-center">
                                    <!-- Proficiency Updater Form -->
                                    <form method="POST" action="{{ route('student.profile.skills.update', $sSkill) }}" class="flex items-center gap-1.5">
                                        @csrf
                                        @method('PUT')
                                        <select 
                                            name="proficiency" 
                                            onchange="this.form.submit()" 
                                            class="py-1 px-2.5 text-xs rounded border border-border bg-surface text-text font-medium focus:ring-1 focus:ring-primary focus:border-transparent cursor-pointer"
                                        >
                                            <option value="beginner" {{ $sSkill->proficiency?->value === 'beginner' ? 'selected' : '' }}>Beginner</option>
                                            <option value="intermediate" {{ $sSkill->proficiency?->value === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                            <option value="advanced" {{ $sSkill->proficiency?->value === 'advanced' ? 'selected' : '' }}>Advanced</option>
                                        </select>
                                    </form>

                                    <!-- Remove Button -->
                                    <form method="POST" action="{{ route('student.profile.skills.destroy', $sSkill) }}" onsubmit="return confirm('Remove \'{{ addslashes($sSkill->skill->name) }}\' from your profile?');">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="p-1 rounded text-text-muted hover:text-danger hover:bg-danger/10 transition"
                                            title="Remove Skill"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Add Skill from Catalog Card -->
            <div class="bg-surface border border-border rounded-xl p-5 sm:p-6 shadow-sm space-y-4">
                <div class="border-b border-border pb-3">
                    <h2 class="text-sm font-bold text-text">Add Skill from Standard Catalog</h2>
                    <p class="text-xs text-text-muted mt-0.5">Select from active ICS approved technical skills.</p>
                </div>

                <form method="POST" action="{{ route('student.profile.skills.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <x-input-label for="skill_id" value="Select Skill *" />
                            <select 
                                id="skill_id" 
                                name="skill_id" 
                                class="mt-1 block w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition" 
                                required
                            >
                                <option value="">Choose a skill...</option>
                                @foreach ($availableSkills as $category => $catSkills)
                                    <optgroup label="{{ $category }}">
                                        @foreach ($catSkills as $skill)
                                            <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('skill_id')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="proficiency" value="Proficiency Level" />
                            <select 
                                id="proficiency" 
                                name="proficiency" 
                                class="mt-1 block w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            >
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button type="submit">
                            Add to My Profile
                        </x-primary-button>
                    </div>
                </form>
            </div>

            <!-- Suggest a Missing Skill Card -->
            <div class="bg-surface border border-border rounded-xl p-5 sm:p-6 shadow-sm space-y-4">
                <div class="border-b border-border pb-3">
                    <h2 class="text-sm font-bold text-text">Suggest a New Skill for the Catalog</h2>
                    <p class="text-xs text-text-muted mt-0.5">Have a skill not listed in the catalog? Propose it for coordinator review.</p>
                </div>

                <form method="POST" action="{{ route('student.skills.suggest') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @csrf
                    <div>
                        <x-input-label for="suggest_name" value="Skill Name *" />
                        <x-text-input 
                            id="suggest_name" 
                            name="name" 
                            type="text" 
                            class="mt-1 block w-full text-xs" 
                            placeholder="e.g. Terraform, GraphQL, Solidity" 
                            required 
                        />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="suggest_category" value="Category *" />
                        <x-text-input 
                            id="suggest_category" 
                            name="category" 
                            type="text" 
                            class="mt-1 block w-full text-xs" 
                            placeholder="e.g. Cloud, Web Development, DevOps" 
                            required 
                        />
                        <x-input-error :messages="$errors->get('category')" class="mt-1" />
                    </div>

                    <div class="sm:col-span-2 flex justify-end pt-2">
                        <button type="submit" class="px-4 py-2 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                            Submit Skill Suggestion
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right: Certifications & Academic Snapshot (1 col) -->
        <div class="space-y-6">
            <!-- Academic Snapshot Card -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted border-b border-border pb-2">Academic Profile</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-text-muted">Student Number:</span>
                        <span class="font-semibold text-text">{{ $studentProfile->student_number }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-text-muted">Degree Program:</span>
                        <span class="font-semibold text-text">{{ $studentProfile->program }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-text-muted">Year & Section:</span>
                        <span class="font-semibold text-text">{{ $studentProfile->year_level }} - {{ $studentProfile->section ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-text-muted">Academic Year:</span>
                        <span class="font-semibold text-text">{{ $studentProfile->school_year }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-text-muted">Placement Status:</span>
                        @if ($studentProfile->has_active_placement)
                            <span class="font-bold text-success text-[11px] uppercase">Placed</span>
                        @else
                            <span class="font-semibold text-warning text-[11px] uppercase">Actively Seeking</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Certifications Card (FR-03) -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-border pb-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted">Certifications ({{ $certifications->count() }})</h3>
                    <button 
                        x-data 
                        @click="$dispatch('open-modal', 'add-cert-modal')" 
                        type="button" 
                        class="text-xs font-bold text-primary hover:underline"
                    >
                        + Add New
                    </button>
                </div>

                @forelse ($certifications as $cert)
                    <div class="p-3 rounded-lg border border-border bg-surface-2/20 space-y-1 relative group">
                        <div class="flex items-start justify-between">
                            <div class="font-bold text-xs text-text">{{ $cert->title }}</div>
                            <form method="POST" action="{{ route('student.profile.certifications.destroy', $cert) }}" onsubmit="return confirm('Remove certification?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-text-muted hover:text-danger text-[10px] p-0.5" title="Remove">
                                    &times;
                                </button>
                            </form>
                        </div>
                        <div class="text-[11px] text-text-muted">{{ $cert->issuer }}</div>
                        @if ($cert->issued_at)
                            <div class="text-[10px] text-text-muted">Issued: {{ $cert->issued_at->format('M Y') }}</div>
                        @endif
                        @if ($cert->document)
                            <div class="pt-1">
                                <a href="{{ route('documents.download', $cert->document) }}" class="inline-flex items-center gap-1 text-[11px] text-primary hover:underline">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    <span>Download Certificate</span>
                                </a>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-text-muted italic py-3 text-center">
                        No certifications recorded yet. Add relevant credentials (e.g. Cisco CCNA, AWS Cloud, DICT trainings).
                    </p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Add Certification Modal -->
    <x-modal name="add-cert-modal" :show="false">
        <form method="POST" action="{{ route('student.profile.certifications.store') }}" class="p-6">
            @csrf

            <h2 class="text-base font-bold text-text">Add Student Certification</h2>
            <p class="text-xs text-text-muted mt-0.5">Record professional credentials, industry training, or course certificates (FR-03).</p>

            <div class="mt-4 space-y-4">
                <div>
                    <x-input-label for="cert_title" value="Certificate Title *" />
                    <x-text-input 
                        id="cert_title" 
                        name="title" 
                        type="text" 
                        class="mt-1 block w-full text-xs" 
                        placeholder="e.g. AWS Certified Cloud Practitioner, Cisco Networking Basics" 
                        required 
                    />
                </div>

                <div>
                    <x-input-label for="cert_issuer" value="Issuing Organization *" />
                    <x-text-input 
                        id="cert_issuer" 
                        name="issuer" 
                        type="text" 
                        class="mt-1 block w-full text-xs" 
                        placeholder="e.g. Amazon Web Services, Cisco, DICT, Coursera" 
                        required 
                    />
                </div>

                <div>
                    <x-input-label for="cert_issued_at" value="Date Issued" />
                    <x-text-input 
                        id="cert_issued_at" 
                        name="issued_at" 
                        type="date" 
                        class="mt-1 block w-full text-xs" 
                    />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button @click="$dispatch('close-modal', 'add-cert-modal')" type="button">
                    Cancel
                </x-secondary-button>
                <x-primary-button type="submit">
                    Save Certification
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
