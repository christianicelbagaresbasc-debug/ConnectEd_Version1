<x-app-layout>
    @section('title', 'My Company Suggestions')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">My Suggested Companies</h1>
                <p class="text-xs text-text-muted mt-0.5">Track the review and verification status of prospective employers you suggested to the OJT Coordination Office.</p>
            </div>
            <a href="{{ route('student.suggestions.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Suggest Another Company</span>
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

    @if ($suggestions->isEmpty())
        <div class="bg-surface border border-border rounded-xl p-12 text-center">
            <div class="w-12 h-12 mx-auto rounded-full bg-surface-2 flex items-center justify-center text-text-muted mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-text">You haven't suggested any companies yet</h3>
            <p class="text-xs text-text-muted mt-1 max-w-sm mx-auto">Have an employer in mind that isn't on our partner list? Submit their details and our coordinators will initiate accreditation.</p>
            <div class="mt-4">
                <a href="{{ route('student.suggestions.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary text-primary-contrast hover:opacity-90 transition">
                    Suggest a Company Now
                </a>
            </div>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($suggestions as $suggestion)
                <div class="bg-surface border border-border rounded-xl p-5 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                <h3 class="text-base font-bold text-text">{{ $suggestion->company_name }}</h3>
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

                            <p class="text-[11px] text-text-muted mb-3">Submitted on {{ $suggestion->created_at->format('M d, Y H:i') }}</p>

                            @if ($suggestion->contact_details)
                                <div class="text-xs text-text-muted mb-2">
                                    <strong class="font-semibold text-text">Provided Contact:</strong>
                                    {{ $suggestion->contact_details }}
                                </div>
                            @endif

                            @if ($suggestion->notes)
                                <div class="text-xs bg-surface-2 border border-border rounded-lg p-3 text-text leading-relaxed">
                                    <strong class="font-semibold text-text-muted text-[11px] block mb-0.5">Your Submission Notes:</strong>
                                    {{ $suggestion->notes }}
                                </div>
                            @endif

                            @if ($suggestion->company)
                                <div class="mt-3 flex items-center gap-2 text-xs">
                                    <span class="text-success font-semibold">Accredited!</span>
                                    <a href="{{ route('student.companies.show', $suggestion->company) }}" class="font-bold text-primary hover:underline">
                                        View Company Partner Profile &rarr;
                                    </a>
                                </div>
                            @endif
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
