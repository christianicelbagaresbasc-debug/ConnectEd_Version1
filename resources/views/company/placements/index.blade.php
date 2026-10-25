<x-app-layout>
    @section('title', 'Active Interns')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">Company Interns</h1>
                <p class="text-xs text-text-muted mt-0.5">Track BSAU ICS student interns placed at your establishment (F-015, F-016).</p>
            </div>
        </div>
    </x-slot>

    <!-- Interns Table -->
    <div class="bg-surface border border-border rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="border-b border-border bg-surface-2 text-text-muted uppercase text-[10px] font-semibold">
                    <tr>
                        <th class="px-4 py-3">Student Intern</th>
                        <th class="px-4 py-3">Program</th>
                        <th class="px-4 py-3">Assigned Role</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Start Date</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border font-medium">
                    @forelse($placements as $placement)
                        <tr class="hover:bg-surface-2/40 transition">
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-text">{{ $placement->studentProfile->user->name }}</div>
                                <div class="text-[11px] text-text-muted">{{ $placement->studentProfile->user->email }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="text-text font-medium">{{ $placement->studentProfile->program }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-text">{{ $placement->opportunity ? $placement->opportunity->title : 'Intern' }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $placement->status->badgeClasses() }}">
                                    {{ $placement->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-text-muted whitespace-nowrap text-[11px]">
                                {{ $placement->start_date ? $placement->start_date->format('M d, Y') : 'Pending Start' }}
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <a 
                                    href="{{ route('company.placements.show', $placement) }}" 
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-primary hover:bg-primary hover:text-primary-contrast border border-border transition"
                                >
                                    <span>View Progress</span>
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-text-muted">
                                <p>No interns currently placed at your company.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($placements->hasPages())
            <div class="p-4 border-t border-border">
                {{ $placements->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
