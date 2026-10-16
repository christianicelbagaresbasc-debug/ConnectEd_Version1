<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold font-heading text-text">Welcome back</h1>
        <p class="text-sm text-text-muted mt-1">Sign in to your ConnectEd account to continue.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-text">Email Address</label>
            <input 
                id="email" 
                class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-sm focus:border-primary focus:ring-primary shadow-sm" 
                type="email" 
                name="email" 
                value="{{ old('email') }}" 
                required 
                autofocus 
                autocomplete="username" 
                placeholder="e.g. oc@bsau.edu.ph"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="block text-sm font-medium text-text">Password</label>
                <span class="text-xs text-text-muted">
                    Reset via OJT Office
                </span>
            </div>
            <input 
                id="password" 
                class="mt-1 block w-full rounded-lg border-border bg-surface text-text text-sm focus:border-primary focus:ring-primary shadow-sm"
                type="password"
                name="password"
                required 
                autocomplete="current-password" 
                placeholder="••••••••••••"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center">
                <input 
                    id="remember_me" 
                    type="checkbox" 
                    class="rounded border-border text-primary shadow-sm focus:ring-primary bg-surface" 
                    name="remember"
                >
                <span class="ms-2 text-xs text-text-muted">Remember this device</span>
            </label>
        </div>

        <!-- Submit Button -->
        <button 
            type="submit" 
            class="w-full py-2.5 px-4 rounded-lg bg-primary text-primary-contrast font-semibold text-sm shadow hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition"
        >
            Sign In
        </button>
    </form>

    <!-- Synthetic Demo Accounts Helper Callout (Dev / Demo) -->
    <div class="mt-6 pt-5 border-t border-border" x-data="{ showDemo: false }">
        <button 
            type="button" 
            @click="showDemo = !showDemo"
            class="w-full flex items-center justify-between text-xs text-text-muted hover:text-text font-medium"
        >
            <span>Demo credentials (all roles)</span>
            <svg class="w-4 h-4 transition-transform duration-200" :class="showDemo ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="showDemo" class="mt-3 text-xs space-y-2 bg-surface-2 p-3 rounded-lg border border-border" style="display: none;">
            <div class="grid grid-cols-2 gap-1.5 font-mono text-[11px]">
                <div><span class="font-bold text-primary">OC:</span> oc@bsau.edu.ph</div>
                <div><span class="font-bold text-primary">COC:</span> coc@bsau.edu.ph</div>
                <div><span class="font-bold text-primary">UA:</span> ua@bsau.edu.ph</div>
                <div><span class="font-bold text-primary">PC:</span> pc@techsolutions.example.test</div>
                <div class="col-span-2"><span class="font-bold text-primary">Student:</span> student@bsau.edu.ph</div>
            </div>
            <div class="text-[10px] text-text-muted italic pt-1 border-t border-border/50">
                Default password: <code class="font-bold text-text">ConnectEd2026!</code>
            </div>
        </div>
    </div>
</x-guest-layout>
