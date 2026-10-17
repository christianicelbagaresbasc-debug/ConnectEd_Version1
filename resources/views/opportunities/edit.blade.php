<x-app-layout>
    @section('title', 'Edit Opportunity — ' . $opportunity->title)

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('opportunities.show', $opportunity) }}" class="text-xs font-semibold text-text-muted hover:text-text flex items-center gap-1 mb-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    <span>Back to Opportunity</span>
                </a>
                <h1 class="text-xl font-bold text-text">Edit Opportunity</h1>
                <p class="text-xs text-text-muted mt-0.5">{{ $opportunity->company->name }} &bull; {{ $opportunity->title }}</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto pb-12">
        <form method="POST" action="{{ route('opportunities.update', $opportunity) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Role Information Card -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-text border-b border-border pb-2">1. Opportunity Details</h2>

                <!-- Company (Read-only on edit) -->
                <div>
                    <x-input-label value="Partner Company" />
                    <x-text-input type="text" class="mt-1 block w-full text-xs bg-surface-2" value="{{ $opportunity->company->name }}" disabled />
                </div>

                <!-- Title -->
                <div>
                    <x-input-label for="title" value="Role / Position Title *" />
                    <x-text-input 
                        id="title" 
                        name="title" 
                        type="text" 
                        class="mt-1 block w-full text-xs" 
                        value="{{ old('title', $opportunity->title) }}" 
                        required 
                    />
                    <x-input-error :messages="$errors->get('title')" class="mt-1" />
                </div>

                <!-- Description -->
                <div>
                    <x-input-label for="description" value="Role Description & Duties *" />
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4" 
                        class="mt-1 block w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition" 
                        required
                    >{{ old('description', $opportunity->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                </div>

                <!-- Qualifications -->
                <div>
                    <x-input-label for="qualifications" value="Qualifications & Prerequisites" />
                    <textarea 
                        id="qualifications" 
                        name="qualifications" 
                        rows="3" 
                        class="mt-1 block w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    >{{ old('qualifications', $opportunity->qualifications) }}</textarea>
                    <x-input-error :messages="$errors->get('qualifications')" class="mt-1" />
                </div>
            </div>

            <!-- Logistics & Capacity Card -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-text border-b border-border pb-2">2. Logistics & Capacity</h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Slots Total -->
                    <div>
                        <x-input-label for="slots_total" value="Total Intern Slots *" />
                        <x-text-input 
                            id="slots_total" 
                            name="slots_total" 
                            type="number" 
                            min="1" 
                            max="100" 
                            class="mt-1 block w-full text-xs" 
                            value="{{ old('slots_total', $opportunity->slots_total) }}" 
                            required 
                        />
                        <x-input-error :messages="$errors->get('slots_total')" class="mt-1" />
                    </div>

                    <!-- Work Arrangement -->
                    <div>
                        <x-input-label for="work_arrangement" value="Work Arrangement *" />
                        <select 
                            id="work_arrangement" 
                            name="work_arrangement" 
                            class="mt-1 block w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            required
                        >
                            <option value="onsite" {{ old('work_arrangement', $opportunity->work_arrangement->value) === 'onsite' ? 'selected' : '' }}>Onsite</option>
                            <option value="remote" {{ old('work_arrangement', $opportunity->work_arrangement->value) === 'remote' ? 'selected' : '' }}>Remote</option>
                            <option value="hybrid" {{ old('work_arrangement', $opportunity->work_arrangement->value) === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                        </select>
                        <x-input-error :messages="$errors->get('work_arrangement')" class="mt-1" />
                    </div>

                    <!-- Deadline -->
                    <div>
                        <x-input-label for="deadline" value="Application Deadline" />
                        <x-text-input 
                            id="deadline" 
                            name="deadline" 
                            type="date" 
                            class="mt-1 block w-full text-xs" 
                            value="{{ old('deadline', $opportunity->deadline?->toDateString()) }}" 
                        />
                        <x-input-error :messages="$errors->get('deadline')" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Location -->
                    <div>
                        <x-input-label for="location" value="Deployment Location" />
                        <x-text-input 
                            id="location" 
                            name="location" 
                            type="text" 
                            class="mt-1 block w-full text-xs" 
                            value="{{ old('location', $opportunity->location) }}" 
                        />
                        <x-input-error :messages="$errors->get('location')" class="mt-1" />
                    </div>

                    <!-- Status (if coordinator/staff) -->
                    @if (Auth::user()->isOc())
                        <div>
                            <x-input-label for="status" value="Publication Status *" />
                            <select 
                                id="status" 
                                name="status" 
                                class="mt-1 block w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            >
                                <option value="open" {{ old('status', $opportunity->status->value) === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="pending_approval" {{ old('status', $opportunity->status->value) === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                                <option value="draft" {{ old('status', $opportunity->status->value) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="closed" {{ old('status', $opportunity->status->value) === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Skills Requirements Card -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <div>
                    <h2 class="text-sm font-bold text-text">3. Skill Competency Requirements</h2>
                    <p class="text-xs text-text-muted mt-0.5">Required skills carry double weight (2x) in the student recommendation engine.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                    <!-- Required Skills -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-text flex items-center justify-between">
                            <span>Required Skills (2x Weight)</span>
                            <span class="text-[10px] text-primary">Must-have competencies</span>
                        </label>
                        <div class="border border-border rounded-lg p-3 max-h-60 overflow-y-auto space-y-1.5 bg-surface-2/30">
                            @foreach ($skills as $skill)
                                <label class="flex items-center gap-2 p-1.5 rounded hover:bg-surface-2 cursor-pointer text-xs">
                                    <input 
                                        type="checkbox" 
                                        name="required_skills[]" 
                                        value="{{ $skill->id }}" 
                                        {{ in_array($skill->id, old('required_skills', $currentRequired)) ? 'checked' : '' }}
                                        class="rounded border-border text-primary focus:ring-primary"
                                    />
                                    <span class="text-text font-medium">{{ $skill->name }}</span>
                                    <span class="text-[10px] text-text-muted ml-auto">({{ $skill->category }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Preferred Skills -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-text flex items-center justify-between">
                            <span>Preferred Skills (1x Weight)</span>
                            <span class="text-[10px] text-text-muted">Nice-to-have competencies</span>
                        </label>
                        <div class="border border-border rounded-lg p-3 max-h-60 overflow-y-auto space-y-1.5 bg-surface-2/30">
                            @foreach ($skills as $skill)
                                <label class="flex items-center gap-2 p-1.5 rounded hover:bg-surface-2 cursor-pointer text-xs">
                                    <input 
                                        type="checkbox" 
                                        name="preferred_skills[]" 
                                        value="{{ $skill->id }}" 
                                        {{ in_array($skill->id, old('preferred_skills', $currentPreferred)) ? 'checked' : '' }}
                                        class="rounded border-border text-primary focus:ring-primary"
                                    />
                                    <span class="text-text font-medium">{{ $skill->name }}</span>
                                    <span class="text-[10px] text-text-muted ml-auto">({{ $skill->category }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Controls -->
            <div class="flex items-center justify-between pt-4">
                <a href="{{ route('opportunities.show', $opportunity) }}" class="px-4 py-2 rounded-lg text-xs font-semibold text-text-muted hover:text-text transition">
                    Cancel
                </a>
                <button 
                    type="submit" 
                    class="px-5 py-2 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition"
                >
                    Update Opportunity
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
