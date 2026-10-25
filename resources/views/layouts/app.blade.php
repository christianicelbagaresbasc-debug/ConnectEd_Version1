<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ConnectEd') }} — @yield('title', 'BSAU ICS OJT Placement')</title>

        <!-- Blocking Theme Script to eliminate flash of unstyled theme (FOUC) -->
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
    <body class="h-full bg-bg text-text antialiased font-sans" x-data="{ sidebarOpen: false, userDropdown: false }">
        <div class="min-h-full flex">
            <!-- Mobile Off-Canvas Backdrop -->
            <div 
                x-show="sidebarOpen" 
                x-transition:enter="transition-opacity ease-linear duration-200" 
                x-transition:enter-start="opacity-0" 
                x-transition:enter-end="opacity-100" 
                x-transition:leave="transition-opacity ease-linear duration-200" 
                x-transition:leave-start="opacity-100" 
                x-transition:leave-end="opacity-0" 
                @click="sidebarOpen = false" 
                class="fixed inset-0 z-40 bg-black/60 lg:hidden"
                style="display: none;"
            ></div>

            <!-- Role-Aware Sidebar -->
            <aside 
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                class="fixed inset-y-0 left-0 z-50 w-64 bg-surface border-r border-border transition-transform duration-200 ease-in-out flex flex-col justify-between"
            >
                <div class="flex-1 flex flex-col overflow-y-auto">
                    <!-- Brand Header -->
                    <div class="h-16 flex items-center justify-between px-6 border-b border-border">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                            <x-application-logo />
                        </a>
                        <button 
                            @click="sidebarOpen = false" 
                            type="button" 
                            class="lg:hidden p-1.5 rounded-lg text-text-muted hover:text-text hover:bg-surface-2"
                            aria-label="Close sidebar"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- User Mini-Profile Card in Sidebar (LinkedIn-style) -->
                    @auth
                    <div class="p-4 mx-3 my-3 bg-surface-2 border border-border rounded-lg flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary/10 border border-primary/20 flex items-center justify-center font-bold text-primary text-sm flex-shrink-0">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-text truncate leading-snug">{{ Auth::user()->name }}</div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-primary/10 text-primary">
                                    {{ Auth::user()->role->value }}
                                </span>
                                <span class="text-[11px] text-text-muted truncate">
                                    {{ Auth::user()->role->label() }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endauth

                    <!-- Navigation Links Grouped by Role -->
                    <nav class="flex-1 px-3 py-2 space-y-1">
                        @php
                            $role = Auth::user()?->role?->value ?? 'guest';
                        @endphp

                        <!-- Common Dashboard -->
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-primary text-primary-contrast shadow-sm font-semibold' : 'text-text hover:bg-surface-2' }}">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            <span>Dashboard</span>
                        </a>

                        <!-- Role: OC Navigation Items -->
                        @if($role === 'oc')
                            <div class="pt-2 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-text-muted">Coordination</div>
                            <a href="{{ route('coordinator.students.pending') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('coordinator.students.pending') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Student Approvals</span>
                            </a>
                            <a href="{{ route('coordinator.students.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('coordinator.students.index') || request()->routeIs('coordinator.students.show') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Students Directory</span>
                            </a>
                            <a href="{{ route('tasks.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('tasks.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Tasks & Delegation</span>
                            </a>
                            <a href="{{ route('coordinator.staff.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('coordinator.staff.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Staff Accounts</span>
                            </a>
                            <a href="{{ route('coordinator.companies.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('coordinator.companies.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Company Records</span>
                            </a>
                            <a href="{{ route('coordinator.suggestions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('coordinator.suggestions.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Company Suggestions</span>
                            </a>
                            <a href="{{ route('moas.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('moas.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>MOA Tracking</span>
                            </a>
                            <a href="{{ route('opportunities.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('opportunities.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Opportunities</span>
                            </a>
                            <a href="{{ route('coordinator.skills.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('coordinator.skills.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Skills Catalog</span>
                            </a>
                            <a href="{{ route('coordinator.applications.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('coordinator.applications.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Applications Queue</span>
                            </a>
                            <a href="{{ route('coordinator.placements.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('coordinator.placements.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Placements</span>
                            </a>
                            <div class="pt-2 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-text-muted">System & Records</div>
                            <a href="{{ route('document-library.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('document-library.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Document Library</span>
                            </a>
                            <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('reports.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>OJT Reports</span>
                            </a>
                            <a href="{{ route('coordinator.archive.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('coordinator.archive.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Archive Center</span>
                            </a>
                            <a href="{{ route('coordinator.audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('coordinator.audit-logs.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Audit Log</span>
                            </a>

                        <!-- Role: COC Navigation Items -->
                        @elseif($role === 'coc')
                            <div class="pt-2 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-text-muted">My Assignments</div>
                            <a href="{{ route('coordinator.students.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('coordinator.students.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Students Directory</span>
                            </a>
                            <a href="{{ route('tasks.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('tasks.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>My Tasks</span>
                            </a>
                            <a href="{{ route('opportunities.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('opportunities.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Opportunities</span>
                            </a>
                            <a href="{{ route('coordinator.applications.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('coordinator.applications.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Applications Queue</span>
                            </a>
                            <a href="{{ route('moas.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('moas.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Student MOAs</span>
                            </a>
                            <a href="{{ route('coordinator.placements.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('coordinator.placements.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Placements & Reports</span>
                            </a>
                            <div class="pt-2 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-text-muted">Records</div>
                            <a href="{{ route('document-library.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('document-library.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Document Library</span>
                            </a>
                            <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('reports.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>OJT Reports</span>
                            </a>

                        <!-- Role: UA Navigation Items -->
                        @elseif($role === 'ua')
                            <div class="pt-2 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-text-muted">Legal Review</div>
                            <a href="{{ route('attorney.moas.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('attorney.moas.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>MOA Legal Queue</span>
                            </a>
                            <a href="{{ route('moas.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('moas.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>All Agreements</span>
                            </a>
                            <a href="{{ route('document-library.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('document-library.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Document Library</span>
                            </a>

                        <!-- Role: PC Navigation Items -->
                        @elseif($role === 'pc')
                            <div class="pt-2 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-text-muted">Company Portal</div>
                            <a href="{{ route('opportunities.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('opportunities.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>OJT Opportunities</span>
                            </a>
                            <a href="{{ route('company.applications.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('company.applications.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Applicants Review</span>
                            </a>
                            <a href="{{ route('company.placements.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('company.placements.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Active Interns</span>
                            </a>
                            <a href="{{ route('document-library.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('document-library.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Document Library</span>
                            </a>

                        <!-- Role: Student Navigation Items -->
                        @elseif($role === 'student')
                            <div class="pt-2 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-text-muted">Internship Hub</div>
                            <a href="{{ route('student.profile.skills') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('student.profile.skills') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Profile & Skills</span>
                            </a>
                            <a href="{{ route('student.recommendations.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('student.recommendations.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Best-Fit Recommendations</span>
                            </a>
                            <a href="{{ route('opportunities.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('opportunities.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Browse Opportunities</span>
                            </a>
                            <a href="{{ route('student.companies.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('student.companies.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Partner Companies</span>
                            </a>
                            <a href="{{ route('student.applications.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('student.applications.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>My Applications</span>
                            </a>
                            <a href="{{ route('document-library.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('document-library.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>My Documents</span>
                            </a>
                            <a href="{{ route('student.suggestions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('student.suggestions.*') ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text hover:bg-surface-2' }}">
                                <span>Suggest a Company</span>
                            </a>
                        @endif

                    </nav>
                </div>

                <!-- Sidebar Footer Status -->
                <div class="p-4 border-t border-border text-xs text-text-muted bg-surface-2/50">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-[11px]">Academic Year:</span>
                        <span class="font-semibold text-text text-[11px]">{{ config('connected.current_school_year', '2026-2027') }}</span>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 lg:pl-64 flex flex-col min-h-screen">
                <!-- Sticky Topbar (LinkedIn-style) -->
                <header class="h-16 bg-surface/85 backdrop-blur-md border-b border-border sticky top-0 z-30 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
                    <!-- Left: Mobile Menu Toggle -->
                    <div class="flex items-center gap-3">
                        <button 
                            @click="sidebarOpen = true" 
                            type="button" 
                            class="lg:hidden p-2 rounded-lg text-text-muted hover:text-text hover:bg-surface-2 focus:outline-none focus:ring-2 focus:ring-primary"
                            aria-label="Open sidebar"
                        >
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                        <span class="text-xs font-semibold uppercase tracking-wider text-text-muted hidden sm:inline">
                            BSAU ICS ConnectEd
                        </span>
                    </div>

                    <!-- Right Controls: Theme Toggle, Notifications Bell, User Menu -->
                    <div class="flex items-center gap-3">
                        <!-- Theme Toggle Component -->
                        <x-theme-toggle />

                        <!-- Notification Bell (FR-17, BR-02) -->
                        @php
                            $unreadNotificationsCount = Auth::user() ? Auth::user()->unreadNotifications()->count() : 0;
                        @endphp
                        <a 
                            href="{{ route('notifications.index') }}" 
                            class="relative p-2 rounded-lg text-text-muted hover:text-text hover:bg-surface-2 focus:outline-none focus:ring-2 focus:ring-primary transition"
                            title="Notifications"
                            aria-label="View notifications"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                            @if($unreadNotificationsCount > 0)
                                <span class="absolute top-1 right-1 min-w-[16px] h-4 px-1 rounded-full bg-accent text-[10px] font-bold text-white flex items-center justify-center ring-2 ring-surface">
                                    {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                                </span>
                            @endif
                        </a>


                        <!-- User Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open" 
                                @click.outside="open = false" 
                                type="button" 
                                class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-surface-2 focus:outline-none focus:ring-2 focus:ring-primary transition"
                            >
                                <div class="w-8 h-8 rounded-full bg-primary/10 border border-primary/20 flex items-center justify-center font-bold text-primary text-xs">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                </div>
                                <span class="hidden md:inline text-xs font-semibold text-text max-w-[120px] truncate">
                                    {{ Auth::user()->name }}
                                </span>
                                <svg class="w-4 h-4 text-text-muted hidden md:inline" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <div 
                                x-show="open" 
                                x-transition:enter="transition ease-out duration-100" 
                                x-transition:enter-start="transform opacity-0 scale-95" 
                                x-transition:enter-end="transform opacity-100 scale-100" 
                                x-transition:leave="transition ease-in duration-75" 
                                x-transition:leave-start="transform opacity-100 scale-100" 
                                x-transition:leave-end="transform opacity-0 scale-95" 
                                class="absolute right-0 mt-2 w-48 bg-surface border border-border shadow-lg rounded-card py-1 z-50"
                                style="display: none;"
                            >
                                <div class="px-4 py-2 border-b border-border text-xs text-text-muted">
                                    Signed in as <strong class="text-text">{{ Auth::user()->role->label() }}</strong>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-xs font-medium text-text hover:bg-surface-2 transition">
                                    Account Settings
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs font-medium text-danger hover:bg-surface-2 transition">
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Header (if defined) -->
                @if (isset($header))
                    <div class="bg-surface border-b border-border py-4 px-4 sm:px-6 lg:px-8">
                        <div class="max-w-7xl mx-auto">
                            {{ $header }}
                        </div>
                    </div>
                @endif

                <!-- Page Body Slot -->
                <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                    {{ $slot }}
                </main>

                <!-- Footer -->
                <footer class="border-t border-border py-4 px-4 sm:px-6 lg:px-8 text-center text-xs text-text-muted bg-surface/50">
                    &copy; {{ date('Y') }} Bulacan State Agricultural University &bull; Institute of Computer Studies &bull; ConnectEd
                </footer>
            </div>
        </div>
    </body>
</html>
