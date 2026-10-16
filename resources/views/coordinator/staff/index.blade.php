<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading font-bold text-2xl text-text leading-tight">Staff & Partner Accounts</h1>
                <p class="text-xs text-text-muted mt-0.5">Manage provisioned accounts for Co-Coordinators, University Attorney, and Partner Companies.</p>
            </div>
            <a href="{{ route('coordinator.staff.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary text-primary-contrast font-semibold text-xs shadow hover:opacity-95 transition">
                <span>+ Provision Account</span>
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('status'))
            <div class="p-4 rounded-lg bg-success/10 text-success text-xs font-semibold border border-success/20">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-surface border border-border rounded-card shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-surface-2 border-b border-border text-text-muted uppercase text-[10px] font-semibold">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Email Address</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Must Change Pwd</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border font-medium">
                        @foreach($staffUsers as $staff)
                            <tr class="hover:bg-surface-2/40 transition-colors">
                                <td class="px-4 py-3.5 text-text font-semibold">
                                    {{ $staff->name }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-primary/10 text-primary">
                                        {{ $staff->role->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-text-muted font-mono text-[11px]">
                                    {{ $staff->email }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold {{ $staff->isActive() ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }}">
                                        {{ $staff->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-xs text-text-muted">
                                    {{ $staff->must_change_password ? 'Yes' : 'No' }}
                                </td>
                                <td class="px-4 py-3.5 text-right space-x-2">
                                    <!-- Reset Password Action -->
                                    <form method="POST" action="{{ route('coordinator.users.reset-password', $staff) }}" class="inline">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            onclick="return confirm('Reset password for {{ $staff->name }}? A temporary password will be generated and password change enforced.');"
                                            class="px-2.5 py-1.5 rounded-lg border border-border text-xs text-text font-semibold hover:bg-surface-2 transition"
                                        >
                                            Reset Password
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($staffUsers->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $staffUsers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
