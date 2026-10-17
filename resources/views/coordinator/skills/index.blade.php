<x-app-layout>
    @section('title', 'IT Skills Catalog')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">IT Skills Catalog</h1>
                <p class="text-xs text-text-muted mt-0.5">Standardized technical and professional skills taxonomy for opportunity matching (FR-03, F-011).</p>
            </div>
            <div class="flex items-center gap-3">
                <button 
                    x-data 
                    @click="$dispatch('open-modal', 'add-skill-modal')" 
                    type="button" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Add Skill to Catalog</span>
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Session Status -->
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

    <!-- Pending Skills Attention Banner -->
    @if ($pendingCount > 0)
        <div class="mb-6 p-4 rounded-xl bg-warning/10 border border-warning/30 text-warning text-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0 text-warning" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <div>
                    <span class="font-bold text-text">{{ $pendingCount }} student-suggested skill(s) pending approval.</span>
                    <span class="text-text-muted">Review and approve to make them available to all ICS students and opportunities.</span>
                </div>
            </div>
            <a href="{{ route('coordinator.skills.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg bg-warning/20 text-text font-semibold hover:bg-warning/30 transition">
                View Pending Skills
            </a>
        </div>
    @endif

    <!-- Filter Card -->
    <div class="bg-surface border border-border rounded-xl p-4 sm:p-5 mb-6 shadow-sm">
        <form method="GET" action="{{ route('coordinator.skills.index') }}" class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            <!-- Search Query -->
            <div class="sm:col-span-2">
                <label for="search" class="block text-xs font-semibold text-text-muted mb-1">Search Skills</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        id="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search skill name (e.g., Laravel, PostgreSQL)..." 
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    />
                </div>
            </div>

            <!-- Category Filter -->
            <div>
                <label for="category" class="block text-xs font-semibold text-text-muted mb-1">Category</label>
                <select 
                    name="category" 
                    id="category" 
                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                >
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-xs font-semibold text-text-muted mb-1">Status</label>
                <select 
                    name="status" 
                    id="status" 
                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                >
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Review</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="sm:col-span-3 lg:col-span-4 flex items-center justify-end gap-2 pt-2 border-t border-border">
                @if (request()->hasAny(['search', 'category', 'status']))
                    <a href="{{ route('coordinator.skills.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium text-text-muted hover:text-text transition">
                        Reset Filters
                    </a>
                @endif
                <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Skills Table Card -->
    <div class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden" x-data="{ editingSkill: null }">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-text divide-y divide-border">
                <thead class="bg-surface-2 text-text-muted font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Skill Name</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Origin / Suggester</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($skills as $skill)
                        <tr class="hover:bg-surface-2/50 transition">
                            <td class="py-3 px-4 font-semibold text-text">
                                {{ $skill->name }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-surface-2 text-text border border-border">
                                    {{ $skill->category }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if ($skill->status->value === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-success/10 text-success border border-success/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                                        Active
                                    </span>
                                @elseif ($skill->status->value === 'pending')
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-warning/10 text-warning border border-warning/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-warning"></span>
                                        Pending Review
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-text-muted/10 text-text-muted border border-border">
                                        <span class="w-1.5 h-1.5 rounded-full bg-text-muted"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-text-muted">
                                @if ($skill->suggester)
                                    <span class="font-medium text-text">{{ $skill->suggester->name }}</span>
                                    <span class="text-[10px] block">({{ $skill->suggester->role->value }})</span>
                                @else
                                    <span class="text-text-muted">Catalog Default</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1">
                                    @if ($skill->status->value === 'pending')
                                        <form method="POST" action="{{ route('coordinator.skills.approve', $skill) }}" class="inline">
                                            @csrf
                                            <button 
                                                type="submit" 
                                                class="px-2.5 py-1 rounded text-xs font-semibold bg-success/15 text-success hover:bg-success/25 transition"
                                                title="Approve Skill"
                                            >
                                                Approve
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Edit Trigger Button -->
                                    <button 
                                        @click="editingSkill = { id: {{ $skill->id }}, name: '{{ addslashes($skill->name) }}', category: '{{ addslashes($skill->category) }}', status: '{{ $skill->status->value }}' }; $dispatch('open-modal', 'edit-skill-modal')"
                                        type="button" 
                                        class="p-1 rounded text-text-muted hover:text-text hover:bg-surface-2 transition"
                                        title="Edit Skill"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                        </svg>
                                    </button>

                                    <!-- Archive Button -->
                                    <form method="POST" action="{{ route('coordinator.skills.destroy', $skill) }}" onsubmit="return confirm('Archive skill \'{{ addslashes($skill->name) }}\'? (Can be restored from Archive Center)');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="p-1 rounded text-text-muted hover:text-danger hover:bg-danger/10 transition"
                                            title="Archive Skill"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center">
                                <div class="max-w-xs mx-auto text-center">
                                    <svg class="w-10 h-10 text-text-muted mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <div class="text-sm font-semibold text-text">No skills found</div>
                                    <p class="text-xs text-text-muted mt-1">Try adjusting your filters or add a new skill to the taxonomy.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($skills->hasPages())
            <div class="p-4 border-t border-border">
                {{ $skills->links() }}
            </div>
        @endif

        <!-- Edit Skill Modal -->
        <x-modal name="edit-skill-modal" :show="false">
            <template x-if="editingSkill">
                <form :action="'/coordinator/skills/' + editingSkill.id" method="POST" class="p-6">
                    @csrf
                    @method('PUT')

                    <h2 class="text-base font-bold text-text">Edit Skill</h2>
                    <p class="text-xs text-text-muted mt-0.5">Modify skill taxonomy classification or status.</p>

                    <div class="mt-4 space-y-4">
                        <div>
                            <x-input-label for="edit_name" value="Skill Name *" />
                            <x-text-input 
                                id="edit_name" 
                                name="name" 
                                type="text" 
                                class="mt-1 block w-full text-xs" 
                                x-model="editingSkill.name" 
                                required 
                            />
                        </div>

                        <div>
                            <x-input-label for="edit_category" value="Category *" />
                            <x-text-input 
                                id="edit_category" 
                                name="category" 
                                type="text" 
                                class="mt-1 block w-full text-xs" 
                                x-model="editingSkill.category" 
                                required 
                            />
                        </div>

                        <div>
                            <x-input-label for="edit_status" value="Status *" />
                            <select 
                                id="edit_status" 
                                name="status" 
                                class="mt-1 block w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                x-model="editingSkill.status"
                                required
                            >
                                <option value="active">Active</option>
                                <option value="pending">Pending Review</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <x-secondary-button @click="$dispatch('close-modal', 'edit-skill-modal')" type="button">
                            Cancel
                        </x-secondary-button>
                        <x-primary-button type="submit">
                            Save Changes
                        </x-primary-button>
                    </div>
                </form>
            </template>
        </x-modal>
    </div>

    <!-- Add Skill Modal -->
    <x-modal name="add-skill-modal" :show="false">
        <form method="POST" action="{{ route('coordinator.skills.store') }}" class="p-6">
            @csrf

            <h2 class="text-base font-bold text-text">Add Skill to Catalog</h2>
            <p class="text-xs text-text-muted mt-0.5">Define a new technical or professional skill in the standardized taxonomy.</p>

            <div class="mt-4 space-y-4">
                <div>
                    <x-input-label for="new_name" value="Skill Name *" />
                    <x-text-input 
                        id="new_name" 
                        name="name" 
                        type="text" 
                        class="mt-1 block w-full text-xs" 
                        placeholder="e.g. Next.js, Docker, Kubernetes" 
                        required 
                    />
                </div>

                <div>
                    <x-input-label for="new_category" value="Category *" />
                    <input 
                        list="categories-list" 
                        id="new_category" 
                        name="category" 
                        type="text" 
                        class="mt-1 block w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition" 
                        placeholder="e.g. Web Development, Cloud, Database" 
                        required 
                    />
                    <datalist id="categories-list">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                </div>

                <div>
                    <x-input-label for="new_status" value="Status *" />
                    <select 
                        id="new_status" 
                        name="status" 
                        class="mt-1 block w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                        required
                    >
                        <option value="active" selected>Active (Immediately available)</option>
                        <option value="pending">Pending Review</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button @click="$dispatch('close-modal', 'add-skill-modal')" type="button">
                    Cancel
                </x-secondary-button>
                <x-primary-button type="submit">
                    Add to Catalog
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
