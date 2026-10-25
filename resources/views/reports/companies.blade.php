<x-app-layout>
    @section('title', 'Company Accreditation Report')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">Company Accreditation Report</h1>
                <p class="text-xs text-text-muted mt-0.5">Partner company verification checklist completion and active agreement records (FR-06, FR-07).</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('reports.companies.csv', request()->query()) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-primary text-white hover:bg-primary-600 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Export CSV (RFC 4180)</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Filter Card -->
        <div class="bg-surface border border-border rounded-xl p-4 sm:p-5 shadow-sm">
            <form method="GET" action="{{ route('reports.companies') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Classification -->
                <div>
                    <label for="classification" class="block text-xs font-semibold text-text-muted mb-1">Company Classification</label>
                    <select 
                        name="classification" 
                        id="classification" 
                        class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    >
                        <option value="all">All Classifications</option>
                        <option value="existing_partner" {{ request('classification') === 'existing_partner' ? 'selected' : '' }}>Existing Partner</option>
                        <option value="new_company" {{ request('classification') === 'new_company' ? 'selected' : '' }}>New Company Intake</option>
                    </select>
                </div>

                <!-- Verification Status -->
                <div>
                    <label for="verification_status" class="block text-xs font-semibold text-text-muted mb-1">Verification Status</label>
                    <select 
                        name="verification_status" 
                        id="verification_status" 
                        class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    >
                        <option value="all">All Statuses</option>
                        <option value="verified" {{ request('verification_status') === 'verified' ? 'selected' : '' }}>Verified (Accredited)</option>
                        <option value="in_progress" {{ request('verification_status') === 'in_progress' ? 'selected' : '' }}>Checklist In Progress</option>
                        <option value="unverified" {{ request('verification_status') === 'unverified' ? 'selected' : '' }}>Unverified</option>
                        <option value="rejected" {{ request('verification_status') === 'rejected' ? 'selected' : '' }}>Verification Rejected</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full py-2 px-4 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                        Filter Report
                    </button>
                    @if (request()->hasAny(['classification', 'verification_status']))
                        <a href="{{ route('reports.companies') }}" class="py-2 px-3 rounded-lg text-xs font-semibold text-text-muted hover:text-text transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Companies Report Table -->
        <div class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-text divide-y divide-border">
                    <thead class="bg-surface-2 text-text-muted font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Company Name</th>
                            <th class="py-3 px-4">Classification</th>
                            <th class="py-3 px-4">Accreditation</th>
                            <th class="py-3 px-4">6-Point Checklist</th>
                            <th class="py-3 px-4">Active MOA</th>
                            <th class="py-3 px-4">Active Interns</th>
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
                                <td class="py-3 px-4 font-medium text-text">
                                    {{ $verifiedCount }} / {{ $totalItems > 0 ? $totalItems : 6 }} items
                                </td>
                                <td class="py-3 px-4">
                                    @if ($comp->activeMoa->isNotEmpty())
                                        <span class="text-success font-semibold text-[11px]">Executed</span>
                                    @else
                                        <span class="text-text-muted text-[11px] italic">None</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-semibold text-text">
                                    {{ $comp->placements_count }} interns
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('coordinator.companies.show', $comp) }}" class="text-primary font-semibold hover:underline">
                                        View Details &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-xs text-text-muted">
                                    No companies match the selected criteria.
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
