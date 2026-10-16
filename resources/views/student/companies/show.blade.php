<x-app-layout>
    @section('title', $company->name . ' — Partner Company')

    <x-slot name="header">
        <div class="flex items-center gap-2 text-xs text-text-muted mb-1">
            <a href="{{ route('student.companies.index') }}" class="hover:text-primary transition">Partner Companies</a>
            <span>/</span>
            <span class="text-text font-medium">{{ $company->name }}</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-xl font-bold text-text truncate">{{ $company->name }}</h1>
            <a href="{{ route('student.companies.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                &larr; Back to Directory
            </a>
        </div>
    </x-slot>

    <!-- Hero Card -->
    <div class="bg-surface border border-border rounded-xl p-6 sm:p-8 mb-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start gap-5">
                <div class="w-16 h-16 rounded-2xl bg-surface-2 border border-border flex items-center justify-center font-extrabold text-primary text-xl flex-shrink-0 shadow-inner">
                    {{ strtoupper(substr($company->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <h2 class="text-xl font-bold text-text">{{ $company->name }}</h2>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-success/10 text-success border border-success/20">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Verified Host Training Establishment
                        </span>
                    </div>

                    <p class="text-xs text-text-muted font-medium mb-3">{{ $company->industry ?? 'IT & Computing Industry' }}</p>

                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-text-muted">
                        @if ($company->city)
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                                <span>{{ $company->city }}</span>
                            </div>
                        @endif

                        @if ($company->website)
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                                </svg>
                                <a href="{{ $company->website }}" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline">
                                    {{ $company->website }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- About Company -->
            <div class="bg-surface border border-border rounded-xl p-6 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted mb-3">About the Organization</h3>
                <p class="text-xs text-text leading-relaxed whitespace-pre-line">
                    {{ $company->description ?: 'No public description available for this organization.' }}
                </p>
            </div>

            <!-- BSIT Relevance -->
            <div class="bg-surface border border-border rounded-xl p-6 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted mb-3">BSAU BSIT Internship Alignment</h3>
                <p class="text-xs text-text leading-relaxed whitespace-pre-line">
                    {{ $company->bsit_relevance_note ?: 'This company has been verified for BSAU BSIT internship training standards.' }}
                </p>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Institutional Verification Notice -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-success/20 text-success flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-text">Officially Verified</h4>
                        <p class="text-xs text-text-muted mt-1 leading-relaxed">
                            Statutory documents (SEC/DTI, BIR, Mayor's Permit) and internship safety standards for this company have been audited and approved by the BSAU OJT Coordination Office.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Location Card -->
            @if ($company->address)
                <div class="bg-surface border border-border rounded-xl p-5 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted mb-2">Office Location</h3>
                    <p class="text-xs text-text leading-relaxed">
                        {{ $company->address }}
                        @if ($company->city)
                            <br><span class="text-text-muted">{{ $company->city }}</span>
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
