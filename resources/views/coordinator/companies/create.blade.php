<x-app-layout>
    @section('title', 'Add Company Record')

    <x-slot name="header">
        <div class="flex items-center gap-2 text-xs text-text-muted mb-1">
            <a href="{{ route('coordinator.companies.index') }}" class="hover:text-primary transition">Companies</a>
            <span>/</span>
            <span class="text-text font-medium">New Record</span>
        </div>
        <h1 class="text-xl font-bold text-text">Add Company Record</h1>
        <p class="text-xs text-text-muted mt-0.5">Register a prospective Host Training Establishment (HTE) and initialize its verification checklist.</p>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-surface border border-border rounded-xl shadow-sm p-6 sm:p-8">
            <!-- Advisory Banner -->
            <div class="mb-6 p-4 rounded-xl bg-primary/10 border border-primary/20 flex items-start gap-3">
                <svg class="w-5 h-5 text-primary flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                <div class="text-xs text-text leading-relaxed">
                    <strong class="font-semibold text-primary">Verification Checklist Auto-Initialization:</strong>
                    Saving this company record will automatically seed all 5 active BSAU ICS verification requirements (SEC/DTI, BIR, Mayor's Permit, Internship Program, and Safety Standards) in a <em>Missing</em> status for compliance auditing.
                </div>
            </div>

            <form method="POST" action="{{ route('coordinator.companies.store') }}" class="space-y-6">
                @csrf

                <!-- Basic Identification -->
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-text-muted mb-4 pb-2 border-b border-border">Company Identification</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label for="name" class="block text-xs font-semibold text-text mb-1">Company / Organization Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="name" 
                                id="name" 
                                value="{{ old('name') }}" 
                                required 
                                placeholder="e.g. NexaTech Solutions Inc." 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition @error('name') border-danger @enderror"
                            />
                            @error('name')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="industry" class="block text-xs font-semibold text-text mb-1">Industry / Sector</label>
                            <input 
                                type="text" 
                                name="industry" 
                                id="industry" 
                                value="{{ old('industry') }}" 
                                placeholder="e.g. Software & Web Development" 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                            @error('industry')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="classification" class="block text-xs font-semibold text-text mb-1">Initial Classification</label>
                            <select 
                                name="classification" 
                                id="classification" 
                                class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            >
                                <option value="new_company" {{ old('classification', 'new_company') === 'new_company' ? 'selected' : '' }}>New Company (Default)</option>
                                <option value="existing_partner" {{ old('classification') === 'existing_partner' ? 'selected' : '' }}>Existing Partner (Pre-verified)</option>
                            </select>
                            <p class="text-[11px] text-text-muted mt-1">Rule BR-17: New Companies can only become Existing Partners when 100% verified by OC decision.</p>
                        </div>
                    </div>
                </div>

                <!-- Location & Contact Info -->
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-text-muted mb-4 pb-2 border-b border-border">Contact & Location</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label for="address" class="block text-xs font-semibold text-text mb-1">Office Address</label>
                            <input 
                                type="text" 
                                name="address" 
                                id="address" 
                                value="{{ old('address') }}" 
                                placeholder="e.g. Unit 402, Cyber Tower, MacArthur Highway" 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                        </div>

                        <div>
                            <label for="city" class="block text-xs font-semibold text-text mb-1">City / Municipality</label>
                            <input 
                                type="text" 
                                name="city" 
                                id="city" 
                                value="{{ old('city') }}" 
                                placeholder="e.g. San Ildefonso, Bulacan" 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                        </div>

                        <div>
                            <label for="website" class="block text-xs font-semibold text-text mb-1">Official Website</label>
                            <input 
                                type="url" 
                                name="website" 
                                id="website" 
                                value="{{ old('website') }}" 
                                placeholder="https://example.com" 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                        </div>

                        <div>
                            <label for="contact_person" class="block text-xs font-semibold text-text mb-1">Contact Person / HR Representative</label>
                            <input 
                                type="text" 
                                name="contact_person" 
                                id="contact_person" 
                                value="{{ old('contact_person') }}" 
                                placeholder="e.g. Maria Santos, HR Director" 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                        </div>

                        <div>
                            <label for="contact_email" class="block text-xs font-semibold text-text mb-1">Contact Email Address</label>
                            <input 
                                type="email" 
                                name="contact_email" 
                                id="contact_email" 
                                value="{{ old('contact_email') }}" 
                                placeholder="careers@example.com" 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                        </div>

                        <div>
                            <label for="contact_phone" class="block text-xs font-semibold text-text mb-1">Contact Phone / Mobile</label>
                            <input 
                                type="text" 
                                name="contact_phone" 
                                id="contact_phone" 
                                value="{{ old('contact_phone') }}" 
                                placeholder="+63 912 345 6789" 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                        </div>
                    </div>
                </div>

                <!-- Academic Fit & Notes -->
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-text-muted mb-4 pb-2 border-b border-border">Academic & Internship Alignment</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="description" class="block text-xs font-semibold text-text mb-1">Company Overview / Description</label>
                            <textarea 
                                name="description" 
                                id="description" 
                                rows="3" 
                                placeholder="Brief summary of the company's core operations, products, or services..." 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            >{{ old('description') }}</textarea>
                        </div>

                        <div>
                            <label for="bsit_relevance_note" class="block text-xs font-semibold text-text mb-1">BSIT Curriculum Relevance Note</label>
                            <textarea 
                                name="bsit_relevance_note" 
                                id="bsit_relevance_note" 
                                rows="2" 
                                placeholder="Explain how internships at this company align with BSAU BSIT learning outcomes (e.g., Network Admin, Web Dev, Mobile Apps)..." 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            >{{ old('bsit_relevance_note') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Form Controls -->
                <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                    <a href="{{ route('coordinator.companies.index') }}" class="px-4 py-2 text-xs font-medium text-text-muted hover:text-text rounded-lg transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold bg-primary text-primary-contrast rounded-lg shadow hover:opacity-90 transition">
                        Save Company Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
