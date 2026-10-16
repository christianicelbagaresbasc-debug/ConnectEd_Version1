<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'ConnectEd') }} — BSAU ICS Skill-Based OJT Placement</title>

        <!-- Blocking Theme Script -->
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
        <!-- Top Navbar -->
        <header class="w-full border-b border-border bg-surface/85 backdrop-blur-md sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="/" class="flex items-center gap-2">
                    <x-application-logo />
                </a>

                <div class="flex items-center gap-4">
                    <x-theme-toggle />
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 bg-primary text-primary-contrast rounded-lg text-sm font-semibold shadow hover:opacity-95 transition">
                                Go to Dashboard &rarr;
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-text px-3.5 py-2 rounded-lg hover:bg-surface-2 transition">
                                Sign In
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-primary text-primary-contrast rounded-lg text-sm font-semibold shadow hover:opacity-95 transition">
                                    Student Registration
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </header>

        <!-- Hero Section (LinkedIn / Modern Institutional Style) -->
        <main class="flex-1">
            <div class="relative overflow-hidden py-16 sm:py-24">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                        <!-- Hero Text -->
                        <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-primary/10 text-primary border border-primary/20">
                                <span>Academic Year {{ config('connected.current_school_year', '2026-2027') }}</span>
                                <span class="w-1.5 h-1.5 rounded-full bg-accent"></span>
                                <span>ICS Capstone System</span>
                            </div>

                            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold font-heading text-text tracking-tight leading-tight">
                                Connect<span class="text-primary">Ed</span>: Skill-Based OJT Placement & Company Verification
                            </h1>

                            <p class="text-lg text-text-muted max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                                A centralized, transparent platform connecting BSAU Institute of Computer Studies (ICS) students with accredited IT industry partners. Featuring multi-party MOA lifecycle tracking, verifiable company accreditation, and skill-aligned opportunity recommendations.
                            </p>

                            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                                <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-lg bg-primary text-primary-contrast font-semibold shadow-md hover:opacity-95 transition">
                                    Access Portal
                                </a>
                                <a href="#features" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-lg bg-surface border border-border text-text font-semibold hover:bg-surface-2 transition">
                                    Explore Modules &darr;
                                </a>
                            </div>

                            <!-- Trust Badges -->
                            <div class="pt-6 border-t border-border flex flex-wrap items-center justify-center lg:justify-start gap-6 text-xs text-text-muted">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Verified Industry Partners</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Multi-Party MOA Tracking</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Zero Algorithmic Student Ranking</span>
                                </div>
                            </div>
                        </div>

                        <!-- Hero Preview Visual Card -->
                        <div class="lg:col-span-5">
                            <div class="bg-surface border border-border rounded-card p-6 shadow-xl space-y-4">
                                <div class="flex items-center justify-between pb-3 border-b border-border">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full bg-danger/80"></div>
                                        <div class="w-3 h-3 rounded-full bg-warning/80"></div>
                                        <div class="w-3 h-3 rounded-full bg-success/80"></div>
                                    </div>
                                    <span class="text-xs font-mono text-text-muted">portal.bsau.edu.ph/connected</span>
                                </div>

                                <!-- Sample Recommendation Card -->
                                <div class="p-4 rounded-lg bg-surface-2 border border-border space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-success/15 text-success">
                                            92% Skill Match
                                        </span>
                                        <span class="text-xs text-text-muted font-medium">3 slots available</span>
                                    </div>
                                    <div>
                                        <h2 class="font-heading font-semibold text-base text-text">Full-Stack Web Development Intern</h2>
                                        <p class="text-xs text-text-muted">TechSolutions Inc. &bull; San Jose del Monte, Bulacan (Hybrid)</p>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-primary/10 text-primary">PHP / Laravel</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-primary/10 text-primary">PostgreSQL</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-primary/10 text-primary">Tailwind CSS</span>
                                    </div>
                                </div>

                                <!-- Sample MOA Tracker Stepper -->
                                <div class="p-4 rounded-lg bg-surface-2 border border-border space-y-2">
                                    <div class="text-xs font-semibold text-text">MOA Custody Tracking</div>
                                    <div class="flex items-center justify-between text-[11px] text-text-muted">
                                        <span class="text-success font-semibold">&check; Draft</span>
                                        <span class="text-success font-semibold">&check; Legal Review</span>
                                        <span class="text-primary font-bold">&bull; For Signature</span>
                                        <span>Executed</span>
                                    </div>
                                    <div class="w-full bg-border rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-primary h-1.5 rounded-full" style="width: 75%"></div>
                                    </div>
                                </div>

                                <div class="text-center pt-2">
                                    <span class="text-xs text-text-muted">Demonstration Mode &bull; Synthetic Seed Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Grid Section -->
            <section id="features" class="py-16 bg-surface-2/50 border-t border-border">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-3xl mx-auto space-y-3 mb-12">
                        <h2 class="text-3xl font-bold font-heading text-text">Comprehensive OJT Management Architecture</h2>
                        <p class="text-sm text-text-muted">Designed specifically to institutionalize the requirements of Bulacan State Agricultural University.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-surface border border-border p-6 rounded-card shadow-sm space-y-3">
                            <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold">01</div>
                            <h3 class="font-heading font-semibold text-lg text-text">Company Verification</h3>
                            <p class="text-xs text-text-muted leading-relaxed">
                                Dynamic table-driven checklist vetting business registrations (SEC/DTI/Mayor's Permit), partner legitimacy, and curriculum relevance before approval.
                            </p>
                        </div>

                        <div class="bg-surface border border-border p-6 rounded-card shadow-sm space-y-3">
                            <div class="w-10 h-10 rounded-lg bg-accent/10 text-accent flex items-center justify-center font-bold">02</div>
                            <h3 class="font-heading font-semibold text-lg text-text">Flexible MOA Tracking</h3>
                            <p class="text-xs text-text-muted leading-relaxed">
                                Multi-party Memorandum of Agreement tracking across University Attorney, Coordinators, Students, and Companies with immutable custody history.
                            </p>
                        </div>

                        <div class="bg-surface border border-border p-6 rounded-card shadow-sm space-y-3">
                            <div class="w-10 h-10 rounded-lg bg-success/10 text-success flex items-center justify-center font-bold">03</div>
                            <h3 class="font-heading font-semibold text-lg text-text">Transparent Matching</h3>
                            <p class="text-xs text-text-muted leading-relaxed">
                                Rule-based match scores calculated for student eyes only. Companies review complete applicant profiles without discriminatory ranking.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="w-full border-t border-border py-6 bg-surface text-xs text-text-muted">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <x-application-logo class="scale-90" />
                </div>
                <span>&copy; {{ date('Y') }} Bulacan State Agricultural University &bull; Institute of Computer Studies</span>
                <span class="text-[11px] font-mono">ConnectEd v1.0.0</span>
            </div>
        </footer>
    </body>
</html>
