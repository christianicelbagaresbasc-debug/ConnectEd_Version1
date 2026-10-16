<x-app-layout>
    @section('title', 'Suggest a Company')

    <x-slot name="header">
        <div class="flex items-center gap-2 text-xs text-text-muted mb-1">
            <a href="{{ route('student.companies.index') }}" class="hover:text-primary transition">Partner Companies</a>
            <span>/</span>
            <a href="{{ route('student.suggestions.index') }}" class="hover:text-primary transition">Suggestions</a>
            <span>/</span>
            <span class="text-text font-medium">New Suggestion</span>
        </div>
        <h1 class="text-xl font-bold text-text">Suggest an Internship Host Company</h1>
        <p class="text-xs text-text-muted mt-0.5">Recommend an employer or tech organization for BSAU ICS internship partnership and verification.</p>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-surface border border-border rounded-xl shadow-sm p-6 sm:p-8">
            <!-- Institutional Notice -->
            <div class="mb-6 p-4 rounded-xl bg-primary/10 border border-primary/20 flex items-start gap-3">
                <svg class="w-5 h-5 text-primary flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                <div class="text-xs text-text leading-relaxed">
                    <strong class="font-semibold text-primary">How Verification Works:</strong>
                    Your suggestion will be queued for review by the OJT Coordinator. If preliminary criteria are met, the coordinator will contact the company to verify its statutory permits (SEC/DTI, BIR, Mayor's Permit) and execute a Memorandum of Agreement (MOA).
                </div>
            </div>

            <form method="POST" action="{{ route('student.suggestions.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="company_name" class="block text-xs font-semibold text-text mb-1">Company / Organization Name <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="company_name" 
                        id="company_name" 
                        value="{{ old('company_name') }}" 
                        required 
                        placeholder="e.g. Innovatech Solutions Philippines" 
                        class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition @error('company_name') border-danger @enderror"
                    />
                    @error('company_name')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="contact_details" class="block text-xs font-semibold text-text mb-1">Contact Details (Email, Phone, Address, or Person)</label>
                    <textarea 
                        name="contact_details" 
                        id="contact_details" 
                        rows="3" 
                        placeholder="e.g. Contact Person: Jane Dela Cruz (HR)&#10;Email: hr@innovatech.ph&#10;Address: Clark Freeport Zone, Pampanga" 
                        class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition @error('contact_details') border-danger @enderror"
                    >{{ old('contact_details') }}</textarea>
                    @error('contact_details')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-xs font-semibold text-text mb-1">Why do you recommend this company? (Alignment with BSIT)</label>
                    <textarea 
                        name="notes" 
                        id="notes" 
                        rows="3" 
                        placeholder="Describe the nature of work, why you'd like to intern here, or if you have already communicated with their HR team..." 
                        class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition @error('notes') border-danger @enderror"
                    >{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 border-t border-border flex items-center justify-end gap-3">
                    <a href="{{ route('student.suggestions.index') }}" class="px-4 py-2 text-xs font-medium text-text-muted hover:text-text rounded-lg transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold bg-primary text-primary-contrast rounded-lg shadow hover:opacity-90 transition">
                        Submit Company Suggestion
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
