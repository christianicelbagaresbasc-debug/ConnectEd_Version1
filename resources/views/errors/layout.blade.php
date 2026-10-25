<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') &bull; ConnectEd</title>

    <script>
        (function() {
            var theme = localStorage.getItem('connected_theme') || 'system';
            var isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-surface-2 text-text font-sans antialiased flex flex-col justify-between">
    <!-- Header -->
    <header class="p-6 flex items-center justify-between border-b border-border bg-surface/80 backdrop-blur">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center text-white font-bold text-sm shadow-sm">
                CE
            </div>
            <div>
                <span class="font-bold text-sm text-text">ConnectEd</span>
                <span class="text-xs text-text-muted block">BSAU ICS OJT Placement</span>
            </div>
        </div>

        <div>
            <x-theme-toggle />
        </div>
    </header>

    <!-- Content -->
    <main class="flex-grow flex items-center justify-center p-6">
        <div class="max-w-md w-full text-center space-y-6">
            <div class="w-16 h-16 rounded-2xl mx-auto flex items-center justify-center @yield('icon-bg', 'bg-primary/10 text-primary')">
                @yield('icon')
            </div>

            <div class="space-y-2">
                <span class="text-xs font-bold tracking-widest uppercase text-text-muted">@yield('code') Error</span>
                <h1 class="text-2xl font-bold text-text">@yield('heading')</h1>
                <p class="text-sm text-text-muted leading-relaxed">
                    @yield('message')
                </p>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ url('/') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs font-semibold bg-primary text-white hover:bg-primary-600 transition shadow-sm">
                    Return to Home
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs font-semibold bg-surface text-text border border-border hover:bg-surface-3 transition">
                        My Dashboard &rarr;
                    </a>
                @endauth
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="p-6 text-center text-xs text-text-muted border-t border-border bg-surface/40">
        &copy; 2026 Bulacan State Agricultural University &bull; Institute of Computer Studies &bull; ConnectEd
    </footer>
</body>
</html>
