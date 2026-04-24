<header class="sticky top-0 z-30 h-16 bg-white/90 dark:bg-slate-900/90 backdrop-blur border-b border-slate-200 dark:border-slate-800 px-4 sm:px-6">
    <div class="h-full flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button type="button" id="mobileSidebarToggle" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-white">@yield('page_title', 'Dashboard')</h1>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            <button type="button" class="relative p-2 rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0h6z"/></svg>
                <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-rose-500"></span>
            </button>

            <button type="button" id="themeToggle" class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800 transition" title="Toggle dark mode">
                <svg id="themeIconSun" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v1m0 16v1m8.66-9h-1M4.34 12h-1m14.95 6.36l-.7-.7M6.4 6.4l-.7-.7m12.59 0l-.7.7M6.4 17.6l-.7.7M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
                <svg id="themeIconMoon" class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z"/></svg>
            </button>

            <details class="relative">
                <summary class="list-none cursor-pointer">
                    <div class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <div class="w-8 h-8 rounded-full bg-indigo-600 text-white text-xs font-semibold grid place-items-center">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="hidden sm:block text-left">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100 leading-tight">{{ auth()->user()->name ?? 'User' }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ auth()->user()->email ?? '' }}</p>
                        </div>
                    </div>
                </summary>
                <div class="absolute right-0 mt-2 w-56 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg p-2">
                    <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">Profile</a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-1">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10">
                            Logout
                        </button>
                    </form>
                </div>
            </details>
        </div>
    </div>
</header>
