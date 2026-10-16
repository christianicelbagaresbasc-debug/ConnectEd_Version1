<x-app-layout>
    @section('title', 'Edit ' . $company->name)

    <x-slot name="header">
        <div class="flex items-center gap-2 text-xs text-text-muted mb-1">
            <a href="{{ route('coordinator.companies.index') }}" class="hover:text-primary transition">Companies</a>
            <span>/</span>
            <a href="{{ route('coordinator.companies.show', $company) }}" class="hover:text-primary transition">{{ $company->name }}</a>
            <span>/</span>
            <span class="text-text font-medium">Edit</span>
        </div>
        <h1 class="text-xl font-bold text-text">Edit Company Profile</h1>
        <p class="text-xs text-text-muted mt-0.5">Update host training establishment details, contact info, and BSIT curriculum alignment.</p>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-surface border border-border rounded-xl shadow-sm p-6 sm:p-8">
            <form method="POST" action="{{ route('coordinator.companies.update', $company) }}" class="space-y-6">
                @csrf
                @method('PUT')

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
                                value="{{ old('name', $company->name) }}" 
                                required 
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
                                value="{{ old('industry', $company->industry) }}" 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                            @error('industry')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-text mb-1">Status & Classification</label>
                            <div class="flex items-center gap-2 pt-2">
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-surface-2 border border-border text-text">
                                    {{ $company->classification->label() }}
                                </span>
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-surface-2 border border-border text-text">
                                    {{ $company->verification_status->label() }}
                                </span>
                            </div>
                            <p class="text-[11px] text-text-muted mt-1">Classification is promoted via formal verification audit only.</p>
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
                                value="{{ old('address', $company->address) }}" 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                        </div>

                        <div>
                            <label for="city" class="block text-xs font-semibold text-text mb-1">City / Municipality</label>
                            <input 
                                type="text" 
                                name="city" 
                                id="city" 
                                value="{{ old('city', $company->city) }}" 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                        </div>

                        <div>
                            <label for="website" class="block text-xs font-semibold text-text mb-1">Official Website</label>
                            <input 
                                type="url" 
                                name="website" 
                                id="website" 
                                value="{{ old('website', $company->website) }}" 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                        </div>

                        <div>
                            <label for="contact_person" class="block text-xs font-semibold text-text mb-1">Contact Person / HR Representative</label>
                            <input 
                                type="text" 
                                name="contact_person" 
                                id="contact_person" 
                                value="{{ old('contact_person', $company->contact_person) }}" 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                        </div>

                        <div>
                            <label for="contact_email" class="block text-xs font-semibold text-text mb-1">Contact Email Address</label>
                            <input 
                                type="email" 
                                name="contact_email" 
                                id="contact_email" 
                                value="{{ old('contact_email', $company->contact_email) }}" 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                        </div>

                        <div>
                            <label for="contact_phone" class="block text-xs font-semibold text-text mb-1">Contact Phone / Mobile</label>
                            <input 
                                type="text" 
                                name="contact_phone" 
                                id="contact_phone" 
                                value="{{ old('contact_phone', $company->contact_phone) }}" 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                        </div>
                    </div>
                </div>

                <!-- Academic Alignment & Description -->
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-text-muted mb-4 pb-2 border-b border-border">Academic & Internship Alignment</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="description" class="block text-xs font-semibold text-text mb-1">Company Overview / Description</label>
                            <textarea 
                                name="description" 
                                id="description" 
                                rows="3" 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            >{{ old('description', $company->description) }}</textarea>
                        </div>

                        <div>
                            <label for="bsit_relevance_note" class="block text-xs font-semibold text-text mb-1">BSIT Curriculum Relevance Note</label>
                            <textarea 
                                name="bsit_relevance_note" 
                                id="bsit_relevance_note" 
                                rows="2" 
                                class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            >{{ old('bsit_relevance_note', $company->bsit_relevance_note) }}</textarea>
                        </div>

                        <div class="pt-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="hidden" name="is_active" value="0">
                                <input 
                                    type="checkbox" 
                                    name="is_active" 
                                    id="is_active" 
                                    value="1" 
                                    {{ old('is_active', $company->is_active) ? 'checked' : '' }} 
                                    class="rounded border-border text-primary focus:ring-primary"
                                />
                                <span class="text-xs font-semibold text-text">Company is active for internship placements</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Controls -->
                <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                    <a href="{{ route('coordinator.companies.show', $company) }}" class="px-4 py-2 text-xs font-medium text-text-muted hover:text-text rounded-lg transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold bg-primary text-primary-contrast rounded-lg shadow hover:opacity-90 transition">
                        Update Company Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
