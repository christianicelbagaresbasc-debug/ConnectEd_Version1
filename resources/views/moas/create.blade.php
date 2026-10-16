<x-app-layout>
    @section('title', 'Draft New MOA')

    <x-slot name="header">
        <div class="flex items-center gap-2 text-xs text-text-muted mb-1">
            <a href="{{ route('moas.index') }}" class="hover:text-primary transition">MOAs</a>
            <span>/</span>
            <span class="text-text font-medium">Draft New Record</span>
        </div>
        <h1 class="text-xl font-bold text-text">Draft Memorandum of Agreement (MOA)</h1>
        <p class="text-xs text-text-muted mt-0.5">Establish a legal institutional or student-specific internship agreement and initialize custody tracking.</p>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-surface border border-border rounded-xl shadow-sm p-6 sm:p-8" x-data="{ moaType: '{{ old('type', 'university_company') }}' }">
            <form method="POST" action="{{ route('moas.store') }}" class="space-y-6">
                @csrf

                <!-- Basic Agreement Information -->
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-text-muted mb-4 pb-2 border-b border-border">Agreement Core Details</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="company_id" class="block text-xs font-semibold text-text mb-1">Partner Organization / HTE <span class="text-danger">*</span></label>
                            <select 
                                name="company_id" 
                                id="company_id" 
                                required 
                                class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition @error('company_id') border-danger @enderror"
                            >
                                <option value="">Select Partner Company</option>
                                @foreach ($companies as $comp)
                                    <option value="{{ $comp->id }}" {{ old('company_id') == $comp->id ? 'selected' : '' }}>
                                        {{ $comp->name }} ({{ $comp->classification->label() }})
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="type" class="block text-xs font-semibold text-text mb-1">Agreement Type <span class="text-danger">*</span></label>
                            <select 
                                name="type" 
                                id="type" 
                                x-model="moaType" 
                                required 
                                class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            >
                                <option value="university_company">University-Wide (Umbrella MOA)</option>
                                <option value="student_company">Student-Specific MOA</option>
                            </select>
                        </div>

                        <!-- Conditional Student Selection for student_company -->
                        <div class="sm:col-span-2" x-show="moaType === 'student_company'" style="display: none;">
                            <label for="student_profile_id" class="block text-xs font-semibold text-text mb-1">Assigned Student Intern <span class="text-danger">*</span></label>
                            <select 
                                name="student_profile_id" 
                                id="student_profile_id" 
                                class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            >
                                <option value="">Select Student</option>
                                @foreach ($students as $stu)
                                    <option value="{{ $stu->id }}" {{ old('student_profile_id') == $stu->id ? 'selected' : '' }}>
                                        {{ $stu->user->name }} — {{ $stu->student_number }} ({{ $stu->program }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-text-muted mt-1">Student-specific MOAs bind a particular student intern to the partner organization.</p>
                        </div>

                        <div>
                            <label for="moa_number" class="block text-xs font-semibold text-text mb-1">Institutional MOA Tracking Number</label>
                            <input 
                                type="text" 
                                name="moa_number" 
                                id="moa_number" 
                                value="{{ old('moa_number') }}" 
                                placeholder="e.g. BSAU-ICS-2026-MOA-001" 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="effective_date" class="block text-xs font-semibold text-text mb-1">Effective Date</label>
                                <input 
                                    type="date" 
                                    name="effective_date" 
                                    id="effective_date" 
                                    value="{{ old('effective_date') }}" 
                                    class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                />
                            </div>

                            <div>
                                <label for="expiry_date" class="block text-xs font-semibold text-text mb-1">Expiration Date</label>
                                <input 
                                    type="date" 
                                    name="expiry_date" 
                                    id="expiry_date" 
                                    value="{{ old('expiry_date') }}" 
                                    class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Initial Custody Assignment (Rule BR-16) -->
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-text-muted mb-4 pb-2 border-b border-border">Initial Document Custody Position (BR-16)</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="initial_custodian_party" class="block text-xs font-semibold text-text mb-1">Initial Holding Party <span class="text-danger">*</span></label>
                            <select 
                                name="initial_custodian_party" 
                                id="initial_custodian_party" 
                                required 
                                class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            >
                                <option value="oc" selected>OJT Coordinator (OC Office)</option>
                                <option value="coc">Co-Coordinator (COC)</option>
                                <option value="ua">University Attorney (Legal Office)</option>
                                <option value="company">Partner Company</option>
                                <option value="student">Student</option>
                            </select>
                        </div>

                        <div>
                            <label for="custody_note" class="block text-xs font-semibold text-text mb-1">Custody Log Initial Remark</label>
                            <input 
                                type="text" 
                                name="custody_note" 
                                id="custody_note" 
                                value="{{ old('custody_note', 'Initial draft prepared for legal review.') }}" 
                                placeholder="Brief reason for custody placement..." 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                        </div>
                    </div>
                </div>

                <!-- Summary Description -->
                <div>
                    <label for="summary" class="block text-xs font-semibold text-text mb-1">Agreement Terms / Scope Summary</label>
                    <textarea 
                        name="summary" 
                        id="summary" 
                        rows="3" 
                        placeholder="Key covenants, duration, allowed internship roles, stipends, or insurance clauses..." 
                        class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    >{{ old('summary') }}</textarea>
                </div>

                <!-- Controls -->
                <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                    <a href="{{ route('moas.index') }}" class="px-4 py-2 text-xs font-medium text-text-muted hover:text-text rounded-lg transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold bg-primary text-primary-contrast rounded-lg shadow hover:opacity-90 transition">
                        Create MOA Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
