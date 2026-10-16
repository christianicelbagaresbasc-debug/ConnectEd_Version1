<x-guest-layout>
    <div class="mb-6">
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-bold uppercase tracking-wider bg-warning/15 text-warning mb-2">
            Action Required
        </div>
        <h1 class="text-2xl font-bold font-heading text-text">Change Your Password</h1>
        <p class="text-sm text-text-muted mt-1">For your security, you must update your temporary password before accessing the system.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 text-xs font-semibold text-success bg-success/10 p-3 rounded-lg border border-success/20">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.forced-update') }}" class="space-y-4">
        @csrf

        <!-- Current Temporary Password -->
        <div>
            <label for="current_password" class="block text-xs font-semibold uppercase tracking-wider text-text-muted">Current Temporary Password</label>
            <input 
                id="current_password" 
                class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-sm focus:border-primary focus:ring-primary shadow-sm" 
                type="password" 
                name="current_password" 
                required 
                autofocus 
                placeholder="Enter temporary password"
            />
            <x-input-error :messages="$errors->get('current_password')" class="mt-1.5" />
        </div>

        <!-- New Password -->
        <div>
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-text-muted">New Password (Min. 10 chars, mixed case + numbers)</label>
            <input 
                id="password" 
                class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-sm focus:border-primary focus:ring-primary shadow-sm" 
                type="password" 
                name="password" 
                required 
                placeholder="••••••••••••"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Confirm New Password -->
        <div>
            <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-text-muted">Confirm New Password</label>
            <input 
                id="password_confirmation" 
                class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-sm focus:border-primary focus:ring-primary shadow-sm" 
                type="password" 
                name="password_confirmation" 
                required 
                placeholder="••••••••••••"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <!-- Submit Button -->
        <button 
            type="submit" 
            class="w-full py-2.5 px-4 rounded-lg bg-primary text-primary-contrast font-semibold text-sm shadow hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition"
        >
            Update Password & Continue
        </button>
    </form>
</x-guest-layout>
