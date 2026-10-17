<x-app-layout>
    @section('title', 'Post OJT Opportunity')

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('opportunities.index') }}" class="text-xs font-semibold text-text-muted hover:text-text flex items-center gap-1 mb-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    <span>Back to Opportunities</span>
                </a>
                <h1 class="text-xl font-bold text-text">Post Internship Opportunity</h1>
                <p class="text-xs text-text-muted mt-0.5">Specify job details, qualifications, and required/preferred technical competencies (FR-08, FR-09).</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto pb-12">
        <form method="POST" action="{{ route('opportunities.store') }}" class="space-y-6">
            @csrf

            <!-- Basic Role Information Card -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-text border-b border-border pb-2">1. Opportunity Details</h2>

                <!-- Company Selector (OC only; fixed for PC) -->
                @if (Auth::user()->isPc())
                    @php $pcComp = $companies->first(); @endphp
                    <div>
                        <x-input-label for="company_name" value="Partner Company" />
                        <x-text-input id="company_name" type="text" class="mt-1 block w-full text-xs bg-surface-2" value="{{ $pcComp?->name }}" disabled />
                    </div>
                @else
                    <div>
                        <x-input-label for="company_id" value="Partner Company *" />
                        <select 
                            id="company_id" 
                            name="company_id" 
                            class="mt-1 block w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            required
                        >
                            <option value="">Select a verified existing partner...</option>
                            @foreach ($companies as $comp)
                                <option value="{{ $comp->id }}" {{ (string) old('company_id') === (string) $comp->id ? 'selected' : '' }}>
                                    {{ $comp->name }} ({{ $comp->city ?? 'No city' }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('company_id')" class="mt-1" />
                    </div>
                @endif

                <!-- Title -->
                <div>
                    <x-input-label for="title" value="Role / Position Title *" />
                    <x-text-input 
                        id="title" 
                        name="title" 
                        type="text" 
                        class="mt-1 block w-full text-xs" 
                        placeholder="e.g. Junior Web Developer Intern, Network Assistant, QA Tester" 
                        value="{{ old('title') }}" 
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
                        placeholder="Describe the day-to-day responsibilities, learning outcomes, and supervision provided..."
                        required
                    >{{ old('description') }}</textarea>
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
                        placeholder="Specific coursework, minimum year standing, or equipment requirements (e.g. personal laptop)..."
                    >{{ old('qualifications') }}</textarea>
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
                            value="{{ old('slots_total', 1) }}" 
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
                            <option value="onsite" {{ old('work_arrangement') === 'onsite' ? 'selected' : '' }}>Onsite</option>
                            <option value="remote" {{ old('work_arrangement') === 'remote' ? 'selected' : '' }}>Remote</option>
                            <option value="hybrid" {{ old('work_arrangement') === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
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
                            value="{{ old('deadline') }}" 
                        />
                        <x-input-error :messages="$errors->get('deadline')" class="mt-1" />
                    </div>
                </div>

                <!-- Location -->
                <div>
                    <x-input-label for="location" value="Office / Deployment Location" />
                    <x-text-input 
                        id="location" 
                        name="location" 
                        type="text" 
                        class="mt-1 block w-full text-xs" 
                        placeholder="e.g. BulSU Compound, Malolos City / Ortigas Center, Pasig" 
                        value="{{ old('location') }}" 
                    />
                    <x-input-error :messages="$errors->get('location')" class="mt-1" />
                </div>
            </div>

            <!-- Skills Requirements Card (Transparent Match Inputs) -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <div>
                    <h2 class="text-sm font-bold text-text">3. Skill Competency Requirements</h2>
                    <p class="text-xs text-text-muted mt-0.5">
                        These standardized competencies power the rule-based matching engine for students. Required skills carry double weight (2x) in calculations.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                    <!-- Required Skills -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-text flex items-center justify-between">
                            <span>Required Skills (2x Weight) *</span>
                            <span class="text-[10px] text-primary">Must-have competencies</span>
                        </label>
                        <div class="border border-border rounded-lg p-3 max-h-60 overflow-y-auto space-y-1.5 bg-surface-2/30">
                            @foreach ($skills as $skill)
                                <label class="flex items-center gap-2 p-1.5 rounded hover:bg-surface-2 cursor-pointer text-xs">
                                    <input 
                                        type="checkbox" 
                                        name="required_skills[]" 
                                        value="{{ $skill->id }}" 
                                        {{ in_array($skill->id, old('required_skills', [])) ? 'checked' : '' }}
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
                                        {{ in_array($skill->id, old('preferred_skills', [])) ? 'checked' : '' }}
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
                <a href="{{ route('opportunities.index') }}" class="px-4 py-2 rounded-lg text-xs font-semibold text-text-muted hover:text-text transition">
                    Cancel
                </a>
                <div class="flex items-center gap-3">
                    @unless(Auth::user()->isPc())
                        <button 
                            type="submit" 
                            name="status" 
                            value="draft" 
                            class="px-4 py-2 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition"
                        >
                            Save as Draft
                        </button>
                    @endunless
                    <button 
                        type="submit" 
                        name="status" 
                        value="open" 
                        class="px-5 py-2 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition"
                    >
                        {{ Auth::user()->isPc() ? 'Submit for Coordinator Approval' : 'Publish Opportunity' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
