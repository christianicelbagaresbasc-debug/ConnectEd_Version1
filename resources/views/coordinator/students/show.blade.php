<x-app-layout>
    @section('title', 'Student Profile — ' . $studentProfile->user->name)

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('coordinator.students.index') }}" class="text-xs font-semibold text-text-muted hover:text-text flex items-center gap-1 mb-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    <span>Back to Students Directory</span>
                </a>
                <h1 class="text-xl font-bold text-text">{{ $studentProfile->user->name }}</h1>
                <p class="text-xs text-text-muted mt-0.5">
                    Student ID: <span class="font-mono font-semibold text-text">{{ $studentProfile->student_number }}</span>
                    &bull; {{ $studentProfile->program }} ({{ $studentProfile->year_level }})
                </p>
            </div>
            <div>
                @if ($studentProfile->has_active_placement)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-success/10 text-success border border-success/20">
                        <span class="w-2 h-2 rounded-full bg-success"></span>
                        Placed in OJT
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-warning/10 text-warning border border-warning/20">
                        <span class="w-2 h-2 rounded-full bg-warning"></span>
                        Actively Seeking Placement
                    </span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-12">
        <!-- Left: Skills and Certifications (2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Technical Skills Card -->
            <div class="bg-surface border border-border rounded-xl p-6 shadow-sm space-y-4">
                <div class="border-b border-border pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-text">Acquired Technical Skills ({{ $studentProfile->studentSkills->count() }})</h2>
                        <p class="text-xs text-text-muted mt-0.5">Competencies claimed by the student in their ConnectEd profile.</p>
                    </div>
                </div>

                @if ($studentProfile->studentSkills->isEmpty())
                    <p class="text-xs text-text-muted italic py-4 text-center">
                        Student has not added any technical skills to their profile yet.
                    </p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @foreach ($studentProfile->studentSkills as $sSkill)
                            <div class="p-3 rounded-lg border border-border bg-surface-2/30 flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold text-text">{{ $sSkill->skill->name }}</div>
                                    <div class="text-[11px] text-text-muted">{{ $sSkill->skill->category }}</div>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider bg-surface border border-border text-text">
                                    {{ $sSkill->proficiency?->value ?? 'Beginner' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Certifications Card -->
            <div class="bg-surface border border-border rounded-xl p-6 shadow-sm space-y-4">
                <div class="border-b border-border pb-3">
                    <h2 class="text-sm font-bold text-text">Certifications & Credentials ({{ $studentProfile->certifications->count() }})</h2>
                    <p class="text-xs text-text-muted mt-0.5">Verified certificates and external trainings attached by the student.</p>
                </div>

                @forelse ($studentProfile->certifications as $cert)
                    <div class="p-3.5 rounded-lg border border-border bg-surface-2/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-0.5">
                            <div class="text-xs font-bold text-text">{{ $cert->title }}</div>
                            <div class="text-[11px] text-text-muted">Issued by {{ $cert->issuer }} &bull; {{ $cert->issued_at ? $cert->issued_at->format('M Y') : 'Date not recorded' }}</div>
                        </div>

                        @if ($cert->document)
                            <a href="{{ route('documents.download', $cert->document) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-semibold bg-surface border border-border text-primary hover:bg-surface-2 transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                <span>Certificate File</span>
                            </a>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-text-muted italic py-4 text-center">
                        No external certifications recorded.
                    </p>
                @endforelse
            </div>
        </div>

        <!-- Right: Academic & Contact Metadata (1 col) -->
        <div class="space-y-6">
            <!-- Academic Information Card -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted border-b border-border pb-2">Academic Standing</h3>
                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-text-muted">Degree Program:</span>
                        <span class="font-semibold text-text">{{ $studentProfile->program }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-text-muted">Year & Section:</span>
                        <span class="font-semibold text-text">{{ $studentProfile->year_level }} &bull; Sec {{ $studentProfile->section ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-text-muted">School Year:</span>
                        <span class="font-semibold text-text">{{ $studentProfile->school_year }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-text-muted">Assigned Co-Coordinator:</span>
                        <span class="font-semibold text-text">{{ $studentProfile->assignedCoc?->name ?? 'Unassigned' }}</span>
                    </div>
                </div>
            </div>

            <!-- Contact Information Card -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted border-b border-border pb-2">Contact Details</h3>
                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-text-muted">Email:</span>
                        <a href="mailto:{{ $studentProfile->user->email }}" class="font-medium text-primary hover:underline truncate max-w-[160px]">
                            {{ $studentProfile->user->email }}
                        </a>
                    </div>
                    @if ($studentProfile->contact_number)
                        <div class="flex items-center justify-between">
                            <span class="text-text-muted">Phone:</span>
                            <span class="font-medium text-text">{{ $studentProfile->contact_number }}</span>
                        </div>
                    @endif
                    @if ($studentProfile->portfolio_url)
                        <div class="flex items-center justify-between">
                            <span class="text-text-muted">Portfolio:</span>
                            <a href="{{ $studentProfile->portfolio_url }}" target="_blank" rel="noopener" class="font-medium text-primary hover:underline truncate max-w-[160px]">
                                {{ $studentProfile->portfolio_url }}
                            </a>
                        </div>
                    @endif
                </div>

                @if ($studentProfile->bio)
                    <div class="pt-3 border-t border-border">
                        <div class="text-[11px] font-bold text-text-muted mb-1">Student Bio:</div>
                        <p class="text-xs text-text leading-relaxed whitespace-pre-line">{{ $studentProfile->bio }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
