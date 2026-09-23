<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Nikah Connect - Muslim Matrimonial Platform')</title>

    <!-- Theme Initialization (Prevent FOUC) -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Google Fonts: Outfit & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        h1, h2, h3, h4, .font-heading { font-family: 'Outfit', sans-serif; }
        .bg-emerald-gradient {
            background: linear-gradient(135deg, #064e3b 0%, #065f46 60%, #047857 100%);
        }
        .bg-gold-gradient {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        }
        .glass-header {
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(12px);
        }
        .dark .glass-header {
            background: rgba(15, 23, 42, 0.92);
        }
        .photo-blur {
            filter: blur(14px);
            transition: filter 0.3s ease;
        }
        .photo-blur:hover {
            filter: blur(10px);
        }
    </style>
</head>
<body class="h-full flex flex-col text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 antialiased selection:bg-emerald-500 selection:text-white transition-colors duration-200">

    <!-- Main Navigation Header -->
    <header class="sticky top-0 z-40 border-b border-slate-200/80 dark:border-slate-800 glass-header shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand Logo -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group">
                        <div class="w-10 h-10 rounded-xl bg-emerald-gradient flex items-center justify-center text-white shadow-md shadow-emerald-900/20 group-hover:scale-105 transition transform">
                            <svg class="w-6 h-6 text-amber-300" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="font-heading font-extrabold text-xl tracking-tight text-emerald-950 dark:text-emerald-100 flex items-center gap-1.5">
                                Nikah<span class="text-amber-500">Connect</span>
                            </span>
                            <span class="text-[10px] uppercase font-bold tracking-widest text-emerald-700 dark:text-emerald-400 block -mt-1">Halal Matrimonial</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center gap-1">
                    <a href="{{ route('discovery.index') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:text-emerald-700 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-slate-800 transition">
                        Find Matches
                    </a>
                    @auth
                        <a href="{{ route('interests.index') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:text-emerald-700 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-slate-800 transition flex items-center gap-1.5">
                            Interests
                            @if(Auth::user()->receivedInterests()->where('status', 'pending')->count() > 0)
                                <span class="bg-amber-500 text-white text-xs px-1.5 py-0.5 rounded-full font-bold">
                                    {{ Auth::user()->receivedInterests()->where('status', 'pending')->count() }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('chat.index') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:text-emerald-700 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-slate-800 transition">
                            Messages
                        </a>
                        @if(Auth::user()->isWali() || Auth::user()->waliLinksAsWali()->exists())
                            <a href="{{ route('wali.dashboard') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium text-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 transition flex items-center gap-1">
                                <span>🛡️ Wali Console</span>
                            </a>
                        @endif
                        @if(Auth::user()->isAdmin() || Auth::user()->isModerator())
                            <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 dark:hover:bg-rose-900/60 transition flex items-center gap-1">
                                <span>⚡ Admin Panel</span>
                            </a>
                        @endif
                    @endauth
                    <a href="{{ route('subscription.pricing') }}" class="px-3.5 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:text-emerald-700 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-slate-800 transition">
                        Plans & Pricing
                    </a>
                </nav>

                <!-- User Profile / Auth Area & Theme Toggle -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Dark / Light Mode Switcher Button -->
                    <button type="button"
                            id="theme-toggle"
                            aria-label="Toggle Dark / Light Mode"
                            title="Toggle Dark / Light Mode"
                            class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 transition focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <!-- Sun Icon (Shown in Dark Mode) -->
                        <svg id="theme-toggle-light-icon" class="hidden w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                        </svg>
                        <!-- Moon Icon (Shown in Light Mode) -->
                        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5 text-slate-600 dark:text-slate-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                        </svg>
                    </button>

                    @auth
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2.5 p-1.5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition focus:outline-none">
                                <div class="w-9 h-9 rounded-full bg-emerald-100 dark:bg-emerald-950 border border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-200 font-bold flex items-center justify-center text-sm shadow-xs">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <div class="hidden md:block text-left">
                                    <div class="text-xs font-bold text-slate-800 dark:text-slate-100 leading-tight flex items-center gap-1">
                                        {{ Str::limit(Auth::user()->name, 14) }}
                                        @if(Auth::user()->is_verified)
                                            <svg class="w-3.5 h-3.5 text-blue-500 fill-current inline" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-medium capitalize">
                                        {{ Auth::user()->role }} · <span class="text-amber-600 dark:text-amber-400 font-semibold">{{ Auth::user()->plan }}</span>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-100 dark:border-slate-800 py-2 z-50 animate-in fade-in slide-in-from-top-2">
                                <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800">
                                    <p class="text-xs font-semibold text-slate-900 dark:text-white">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ Auth::user()->email }}</p>
                                </div>
                                <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-slate-800 hover:text-emerald-700 dark:hover:text-emerald-400">
                                    👤 My Profile & Photos
                                </a>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-slate-800 hover:text-emerald-700 dark:hover:text-emerald-400">
                                    ✏️ Edit Deen & Preferences
                                </a>
                                <a href="{{ route('wali.link') }}" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-slate-800 hover:text-emerald-700 dark:hover:text-emerald-400">
                                    🛡️ Link Guardian (Wali)
                                </a>
                                <a href="{{ route('subscription.pricing') }}" class="block px-4 py-2 text-sm text-amber-700 dark:text-amber-400 font-medium hover:bg-amber-50 dark:hover:bg-slate-800">
                                    ⭐ Membership Plan ({{ ucfirst(Auth::user()->plan) }})
                                </a>
                                <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-slate-800">
                                        🚪 Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-700 dark:text-slate-200 hover:text-emerald-700 dark:hover:text-emerald-400 px-3 py-2">
                            Log in
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-900/10 transition">
                            Create Profile
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Alerts -->
    @if(session('success'))
        <div class="bg-emerald-50 border-b border-emerald-200 text-emerald-800 px-4 py-3 text-sm flex items-center justify-between max-w-7xl mx-auto w-full my-2 rounded-xl">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-amber-50 border-b border-amber-200 text-amber-800 px-4 py-3 text-sm flex items-center justify-between max-w-7xl mx-auto w-full my-2 rounded-xl">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('warning') }}</span>
            </div>
        </div>
    @endif

    @if(session('info'))
        <div class="bg-sky-50 border-b border-sky-200 text-sky-800 px-4 py-3 text-sm flex items-center justify-between max-w-7xl mx-auto w-full my-2 rounded-xl">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-sky-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('info') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-sm max-w-7xl mx-auto w-full my-2 rounded-xl">
            <p class="font-semibold mb-1">Please correct the following:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Content Area -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Global Footer -->
    <footer class="bg-slate-900 text-slate-400 text-sm border-t border-slate-800 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-gradient flex items-center justify-center text-white">
                            <span class="font-bold text-amber-300">☪</span>
                        </div>
                        <span class="font-heading font-bold text-xl text-white">Nikah Connect</span>
                    </div>
                    <p class="text-slate-400 text-sm max-w-sm leading-relaxed">
                        A modern, Halal matchmaking platform for Muslims seeking blessed marriage (Nikah). Grounded in Islamic jurisprudence, guardian supervision, and strict photo privacy controls.
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Islamic Integrity</h4>
                    <ul class="space-y-2 text-xs">
                        <li>🛡️ Guardian (Wali) Chaperoning</li>
                        <li>🔒 Blurred Photo Privacy</li>
                        <li>🚫 No Unsolicited Dating</li>
                        <li>🛡️ Anti-Harassment Scanning</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Governance & Privacy</h4>
                    <ul class="space-y-2 text-xs">
                        <li>GDPR Right to Erasure & Deletion</li>
                        <li>OWASP ASVS Security Baseline</li>
                        <li>Verified Government ID (KYC)</li>
                        <li>PCI-DSS Encrypted Payments</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-800 pt-6 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} Nikah Connect. All rights reserved.</p>
                <div class="flex items-center gap-4 text-slate-400">
                    <a href="#" class="hover:text-white transition">Privacy Policy</a>
                    <span>•</span>
                    <a href="#" class="hover:text-white transition">Terms of Service</a>
                    <span>•</span>
                    <a href="#" class="hover:text-white transition">Community Guidelines</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        (function() {
            const themeToggleBtn = document.getElementById('theme-toggle');
            const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
            const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

            function syncThemeIcons() {
                const isDark = document.documentElement.classList.contains('dark');
                if (isDark) {
                    if (themeToggleLightIcon) themeToggleLightIcon.classList.remove('hidden');
                    if (themeToggleDarkIcon) themeToggleDarkIcon.classList.add('hidden');
                } else {
                    if (themeToggleLightIcon) themeToggleLightIcon.classList.add('hidden');
                    if (themeToggleDarkIcon) themeToggleDarkIcon.classList.remove('hidden');
                }
            }

            syncThemeIcons();

            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', function() {
                    const isDark = document.documentElement.classList.toggle('dark');
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                    syncThemeIcons();
                });
            }
        })();
    </script>
</body>
</html>
