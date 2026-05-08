<header class="sticky top-0 z-30 h-16 bg-white/90 dark:bg-slate-900/90 backdrop-blur border-b border-slate-200 dark:border-slate-800 px-4 sm:px-6">
    <div class="h-full flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button type="button" id="mobileSidebarToggle" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-white">@yield('page_title', 'Dashboard')</h1>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">

            <label for="themeSelector" class="sr-only">Select theme</label>
            <select id="themeSelector" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/40">
                <optgroup label="Light">
                    <option value="light">☀ Light</option>
                </optgroup>
                <optgroup label="Dark">
                    <option value="dark">🌙 Dark</option>
                    <option value="navy">🌊 Midnight Navy</option>
                    <option value="emerald">🌿 Deep Emerald</option>
                    <option value="purple">💜 Royal Purple</option>
                    <option value="charcoal">⚫ Charcoal</option>
                </optgroup>
                <optgroup label="Coloured">
                    <option value="teal">🩵 Pro Teal</option>
                </optgroup>
            </select>

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
