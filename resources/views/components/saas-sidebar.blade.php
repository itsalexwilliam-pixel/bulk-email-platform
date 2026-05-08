@php
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['label' => 'Audience', 'route' => 'contacts.index', 'icon' => 'M17 20h5V4H2v16h5m10 0v-6h-4v6m4 0h-4m-4 0h4m-4 0v-6h4'],
        ['label' => 'Groups', 'route' => 'groups.index', 'icon' => 'M4 6h16M4 12h16M4 18h7'],
        ['label' => 'Campaigns', 'route' => 'campaigns.index', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['label' => 'Templates', 'route' => 'templates.index', 'icon' => 'M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2'],
        ['label' => 'Automations', 'route' => null, 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
        ['label' => 'SMTP / Sending', 'route' => 'smtp.index', 'icon' => 'M3 8l7.89 4.26a2 2 0 002.22 0L21 8m-18 8h18a2 2 0 002-2V8a2 2 0 00-2-2H3a2 2 0 00-2 2v6a2 2 0 002 2z'],
        ['label' => 'Send Single Email', 'route' => 'single-email.create', 'icon' => 'M4 4h16v16H4V4zm2 3v1h12V7H6zm0 3v7h12v-7H6zm2 2h8v1H8v-1zm0 2h6v1H8v-1z'],
        ['label' => 'Campaign Report', 'route' => 'reports.index', 'icon' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z'],
        ['label' => 'Single Email Report', 'route' => 'reports.single-email', 'icon' => 'M4 4h16v16H4V4zm2 3v1h12V7H6zm0 3v7h12v-7H6zm2 2h8v1H8v-1zm0 2h6v1H8v-1z'],
        ['label' => 'Unsubscribes', 'route' => 'unsubscribes.index', 'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
        ['label' => 'Bounces', 'route' => 'bounces.index', 'icon' => 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z'],
        ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4z'],
    ];

    if (auth()->check() && auth()->user()->isAdmin() && Route::has('users.index')) {
        $navItems[] = [
            'label' => 'Users',
            'route' => 'users.index',
            'icon' => 'M17 20h5V4H2v16h5m10 0v-2a2 2 0 00-2-2H9a2 2 0 00-2 2v2m10 0H7m5-10a3 3 0 100-6 3 3 0 000 6z',
        ];
    }
@endphp

<aside id="saas-sidebar" class="fixed inset-y-0 left-0 z-40 w-72 bg-white border-r border-slate-200 dark:bg-slate-900 dark:border-slate-800 transition-transform duration-300">
    <div class="h-full flex flex-col">
        <div class="h-16 px-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between gap-2">
            <a href="{{ route('dashboard') }}" style="flex:1;min-width:0;display:flex;align-items:center;overflow:hidden;">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'Novelio Technologies') }}" style="max-height:40px;max-width:180px;width:auto;height:auto;object-fit:contain;object-position:left;">
            </a>
            <button type="button" id="sidebarCollapseBtn" class="hidden lg:inline-flex flex-shrink-0 p-2 rounded-md text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <nav class="flex-1 px-4 py-5 space-y-1 overflow-y-auto">
            @foreach($navItems as $item)
                @php
                    $isActive = $item['route'] ? request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])) : false;
                @endphp

                @if($item['route'])
                    <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                              {{ $isActive
                                    ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                        <svg class="w-5 h-5 {{ $isActive ? 'text-indigo-600 dark:text-indigo-300' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/>
                        </svg>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @else
                    <span class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 dark:text-slate-500 cursor-not-allowed">
                        <svg class="w-5 h-5 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/>
                        </svg>
                        <span>{{ $item['label'] }}</span>
                        <span class="ml-auto text-[10px] px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800">Soon</span>
                    </span>
                @endif
            @endforeach
        </nav>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-medium hover:bg-black dark:bg-slate-700 dark:hover:bg-slate-600 transition">
                    Logout
                </button>
            </form>
        </div>
    </div>
</aside>
