<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold font-heading text-text">Student Registration</h1>
        <p class="text-sm text-text-muted mt-1">Create your BSAU ICS OJT student account.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="{ showPrivacyModal: false }">
        @csrf

        <!-- Full Name -->
        <div>
            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-text-muted">Full Name</label>
            <input 
                id="name" 
                class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-sm focus:border-primary focus:ring-primary shadow-sm" 
                type="text" 
                name="name" 
                value="{{ old('name') }}" 
                required 
                autofocus 
                placeholder="Juan Dela Cruz"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        <!-- Student Number & Program -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="student_number" class="block text-xs font-semibold uppercase tracking-wider text-text-muted">Student Number</label>
                <input 
                    id="student_number" 
                    class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-sm focus:border-primary focus:ring-primary shadow-sm" 
                    type="text" 
                    name="student_number" 
                    value="{{ old('student_number') }}" 
                    required 
                    placeholder="e.g. 2023-10024"
                />
                <x-input-error :messages="$errors->get('student_number')" class="mt-1.5" />
            </div>

            <div>
                <label for="program" class="block text-xs font-semibold uppercase tracking-wider text-text-muted">Degree Program</label>
                <select 
                    id="program" 
                    name="program" 
                    class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-sm focus:border-primary focus:ring-primary shadow-sm"
                    required
                >
                    @foreach($allowedPrograms as $prog)
                        <option value="{{ $prog }}" {{ old('program') === $prog ? 'selected' : '' }}>{{ $prog }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('program')" class="mt-1.5" />
            </div>
        </div>

        <!-- Year Level & Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="year_level" class="block text-xs font-semibold uppercase tracking-wider text-text-muted">Year Level</label>
                <input 
                    id="year_level" 
                    class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-sm focus:border-primary focus:ring-primary shadow-sm" 
                    type="text" 
                    name="year_level" 
                    value="{{ old('year_level', '4th Year') }}" 
                    required 
                />
                <x-input-error :messages="$errors->get('year_level')" class="mt-1.5" />
            </div>

            <div>
                <label for="section" class="block text-xs font-semibold uppercase tracking-wider text-text-muted">Section (Optional)</label>
                <input 
                    id="section" 
                    class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-sm focus:border-primary focus:ring-primary shadow-sm" 
                    type="text" 
                    name="section" 
                    value="{{ old('section') }}" 
                    placeholder="e.g. 4-A"
                />
                <x-input-error :messages="$errors->get('section')" class="mt-1.5" />
            </div>
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-text-muted">Institutional or Personal Email</label>
            <input 
                id="email" 
                class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-sm focus:border-primary focus:ring-primary shadow-sm" 
                type="email" 
                name="email" 
                value="{{ old('email') }}" 
                required 
                autocomplete="username" 
                placeholder="student@bsau.edu.ph"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-text-muted">Password (Min. 10 chars, mixed case + numbers)</label>
            <input 
                id="password" 
                class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-sm focus:border-primary focus:ring-primary shadow-sm"
                type="password"
                name="password"
                required 
                autocomplete="new-password" 
                placeholder="••••••••••••"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-text-muted">Confirm Password</label>
            <input 
                id="password_confirmation" 
                class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-sm focus:border-primary focus:ring-primary shadow-sm"
                type="password"
                name="password_confirmation"
                required 
                autocomplete="new-password" 
                placeholder="••••••••••••"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <!-- Data Privacy Consent Checkbox (RA 10173) -->
        <div class="pt-2">
            <label class="flex items-start gap-2.5 cursor-pointer">
                <input 
                    type="checkbox" 
                    name="privacy_consent" 
                    value="1" 
                    {{ old('privacy_consent') ? 'checked' : '' }}
                    class="rounded border-border text-primary shadow-sm focus:ring-primary bg-surface mt-0.5"
                    required
                >
                <span class="text-xs text-text-muted leading-relaxed">
                    I agree to the <button type="button" @click="showPrivacyModal = true" class="text-primary underline font-medium">BSAU ICS Data Privacy Notice (RA 10173)</button> and consent to the collection and processing of my academic records for OJT placement coordination.
                </span>
            </label>
            <x-input-error :messages="$errors->get('privacy_consent')" class="mt-1.5" />
        </div>

        <!-- Notice of Coordinator Approval -->
        <div class="p-3 rounded-lg bg-surface-2 border border-border text-xs text-text-muted">
            <strong class="text-text font-semibold">Note:</strong> Newly created student accounts undergo verification by the BSAU OJT Coordinator before login is enabled.
        </div>

        <!-- Submit Button -->
        <button 
            type="submit" 
            class="w-full py-2.5 px-4 rounded-lg bg-primary text-primary-contrast font-semibold text-sm shadow hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition"
        >
            Complete Registration
        </button>

        <div class="text-center pt-2">
            <span class="text-xs text-text-muted">Already have an account?</span>
            <a href="{{ route('login') }}" class="text-xs text-primary font-semibold hover:underline ml-1">Sign In</a>
        </div>

        <!-- Privacy Notice Modal -->
        <div 
            x-show="showPrivacyModal" 
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60"
            style="display: none;"
        >
            <div @click.outside="showPrivacyModal = false" class="bg-surface border border-border rounded-card max-w-lg w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-border">
                    <h3 class="font-heading font-bold text-lg text-text">Data Privacy Notice</h3>
                    <button type="button" @click="showPrivacyModal = false" class="text-text-muted hover:text-text">&times;</button>
                </div>
                <div class="text-xs text-text-muted space-y-2 max-h-60 overflow-y-auto pr-2">
                    <p>In compliance with the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong>, the Bulacan State Agricultural University (BSAU) Institute of Computer Studies (ICS) is committed to protecting your personal data.</p>
                    <p><strong>Purpose of Collection:</strong> Data collected during registration (name, student number, email, contact number, degree program, and skills) is strictly utilized for student verification, skill-based internship matching, Memorandum of Agreement (MOA) processing, and placement monitoring.</p>
                    <p><strong>Data Minimization & Security:</strong> No unnecessary government identifiers or sensitive financial information are collected. All records are protected under server-side role-based authorization.</p>
                    <p><strong>Retention Policy:</strong> All records are securely archived in compliance with BSAU academic audit guidelines.</p>
                </div>
                <div class="pt-3 border-t border-border flex justify-end">
                    <button type="button" @click="showPrivacyModal = false" class="px-4 py-2 bg-primary text-primary-contrast text-xs font-semibold rounded-lg shadow hover:opacity-95">
                        I Understand
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-guest-layout>
