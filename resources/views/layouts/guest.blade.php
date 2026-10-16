<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ConnectEd') }} — BSAU ICS OJT Placement</title>

        <!-- Blocking Theme Script to prevent flash of unstyled theme -->
        <script>
            (function() {
                const theme = localStorage.getItem('connected_theme') || 'system';
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (theme === 'dark' || (theme === 'system' && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>

        <!-- Self-hosted Assets & Fonts via Vite -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full bg-bg text-text antialiased font-sans flex flex-col justify-between">
        <!-- Guest Top Navigation Bar -->
        <header class="w-full border-b border-border bg-surface/80 backdrop-blur-md sticky top-0 z-30">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="/" class="flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-primary rounded-lg p-1">
                    <x-application-logo />
                </a>

                <div class="flex items-center gap-4">
                    <x-theme-toggle />
                    @if (Route::has('login'))
                        <div class="flex items-center gap-2">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-primary hover:underline">
                                    Dashboard &rarr;
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-sm font-medium text-text-muted hover:text-text px-3 py-1.5 rounded-lg hover:bg-surface-2 transition">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="text-sm font-medium bg-primary text-primary-contrast px-3.5 py-1.5 rounded-lg shadow-sm hover:opacity-95 transition">
                                        Student Register
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </header>

        <!-- Main Card Container -->
        <main class="flex-1 flex flex-col items-center justify-center p-4 sm:p-6 lg:p-8">
            <div class="w-full max-w-md bg-surface border border-border shadow-sm rounded-card p-6 sm:p-8">
                {{ $slot }}
            </div>
        </main>

        <!-- Institutional Footer -->
        <footer class="w-full border-t border-border py-4 bg-surface text-center text-xs text-text-muted">
            <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
                <span>&copy; {{ date('Y') }} Bulacan State Agricultural University &bull; Institute of Computer Studies</span>
                <span class="text-[11px]">Skill-Based OJT Placement & Company Verification System</span>
            </div>
        </footer>
    </body>
</html>
