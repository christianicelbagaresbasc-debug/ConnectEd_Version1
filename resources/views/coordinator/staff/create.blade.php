<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-heading font-bold text-2xl text-text leading-tight">Provision Staff or Partner Account</h1>
            <p class="text-xs text-text-muted mt-0.5">Create pre-authorized accounts for Co-Coordinators, University Attorney, or Partner Company representatives.</p>
        </div>
    </x-slot>

    <div class="max-w-2xl bg-surface border border-border rounded-card p-6 shadow-sm">
        <form method="POST" action="{{ route('coordinator.staff.store') }}" class="space-y-4">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-text-muted">Full Name / Representative Name</label>
                <input 
                    id="name" 
                    type="text" 
                    name="name" 
                    value="{{ old('name') }}" 
                    required 
                    class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-xs focus:border-primary focus:ring-primary shadow-sm"
                    placeholder="Atty. Maria Santos or Juan Dela Cruz"
                />
                <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-text-muted">Official Email Address</label>
                <input 
                    id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-xs focus:border-primary focus:ring-primary shadow-sm"
                    placeholder="rep@partnercompany.com or legal@bsau.edu.ph"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
            </div>

            <!-- Role Selection -->
            <div>
                <label for="role" class="block text-xs font-semibold uppercase tracking-wider text-text-muted">Assigned System Role</label>
                <select 
                    id="role" 
                    name="role" 
                    required 
                    class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-xs focus:border-primary focus:ring-primary shadow-sm"
                >
                    <option value="coc" {{ old('role') === 'coc' ? 'selected' : '' }}>Co-OJT Coordinator (COC)</option>
                    <option value="ua" {{ old('role') === 'ua' ? 'selected' : '' }}>University Attorney (UA)</option>
                    <option value="pc" {{ old('role') === 'pc' ? 'selected' : '' }}>Partner Company Representative (PC)</option>
                    <option value="oc" {{ old('role') === 'oc' ? 'selected' : '' }}>OJT Coordinator (OC)</option>
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-1.5" />
            </div>

            <!-- Department / Office -->
            <div>
                <label for="department" class="block text-xs font-semibold uppercase tracking-wider text-text-muted">Department / Office (Optional)</label>
                <input 
                    id="department" 
                    type="text" 
                    name="department" 
                    value="{{ old('department') }}" 
                    class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-xs focus:border-primary focus:ring-primary shadow-sm"
                    placeholder="e.g. Legal Affairs Office or Human Resources Dept"
                />
                <x-input-error :messages="$errors->get('department')" class="mt-1.5" />
            </div>

            <!-- Job Title -->
            <div>
                <label for="job_title" class="block text-xs font-semibold uppercase tracking-wider text-text-muted">Job Title / Designation (Optional)</label>
                <input 
                    id="job_title" 
                    type="text" 
                    name="job_title" 
                    value="{{ old('job_title') }}" 
                    class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-xs focus:border-primary focus:ring-primary shadow-sm"
                    placeholder="e.g. Talent Acquisition Lead or Legal Counsel"
                />
                <x-input-error :messages="$errors->get('job_title')" class="mt-1.5" />
            </div>

            <!-- Notice on Credentials -->
            <div class="p-3 rounded-lg bg-surface-2 border border-border text-xs text-text-muted">
                <strong class="text-text font-semibold">Security Policy:</strong> A secure temporary password will be generated automatically. The user will be required to change their password immediately upon their first login.
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('coordinator.staff.index') }}" class="px-4 py-2 border border-border text-text-muted rounded-lg text-xs font-semibold hover:text-text">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-primary text-primary-contrast rounded-lg text-xs font-semibold shadow hover:opacity-95">
                    Provision Account
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
