<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Novelio Technologies') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <style>
        /* ── Theme: Light (default) ────────────────────────────── */
        body.theme-light { --accent: #4f46e5; --accent-hover: #4338ca; }

        /* ── Theme: Dark ───────────────────────────────────────── */
        body.theme-dark  { --accent: #818cf8; --accent-hover: #6366f1; }

        /* ── Theme: Pro Teal ───────────────────────────────────── */
        body.theme-teal  { --accent: #0d9488; --accent-hover: #0f766e; }

        /* ── Theme: Midnight Navy ──────────────────────────────── */
        body.theme-navy {
            --accent: #3b82f6;
            --accent-hover: #2563eb;
            background-color: #0f172a !important;
            color: #cbd5e1 !important;
        }
        body.theme-navy .bg-white,
        body.theme-navy .dark\:bg-slate-900 { background-color: #1e293b !important; }
        body.theme-navy .bg-slate-50,
        body.theme-navy .dark\:bg-slate-950 { background-color: #0f172a !important; }
        body.theme-navy .border-slate-200,
        body.theme-navy .dark\:border-slate-800 { border-color: #334155 !important; }
        body.theme-navy .text-slate-900,
        body.theme-navy .dark\:text-white { color: #f1f5f9 !important; }
        body.theme-navy .text-slate-500,
        body.theme-navy .dark\:text-slate-400 { color: #94a3b8 !important; }
        body.theme-navy .bg-indigo-600 { background-color: #3b82f6 !important; }
        body.theme-navy .hover\:bg-slate-100:hover,
        body.theme-navy .dark\:hover\:bg-slate-800:hover { background-color: #334155 !important; }
        body.theme-navy .bg-white\/90,
        body.theme-navy .dark\:bg-slate-900\/90 { background-color: rgba(30,41,59,0.95) !important; }
        body.theme-navy .bg-slate-50\/70,
        body.theme-navy .dark\:bg-slate-800\/50 { background-color: rgba(15,23,42,0.7) !important; }

        /* ── Theme: Deep Emerald ───────────────────────────────── */
        body.theme-emerald {
            --accent: #10b981;
            --accent-hover: #059669;
            background-color: #0a1f18 !important;
            color: #d1fae5 !important;
        }
        body.theme-emerald .bg-white,
        body.theme-emerald .dark\:bg-slate-900 { background-color: #0d2b1f !important; }
        body.theme-emerald .bg-slate-50,
        body.theme-emerald .dark\:bg-slate-950 { background-color: #0a1f18 !important; }
        body.theme-emerald .border-slate-200,
        body.theme-emerald .dark\:border-slate-800 { border-color: #1a4a36 !important; }
        body.theme-emerald .text-slate-900,
        body.theme-emerald .dark\:text-white { color: #ecfdf5 !important; }
        body.theme-emerald .text-slate-500,
        body.theme-emerald .dark\:text-slate-400 { color: #6ee7b7 !important; }
        body.theme-emerald .bg-indigo-600 { background-color: #10b981 !important; }
        body.theme-emerald .hover\:bg-slate-100:hover,
        body.theme-emerald .dark\:hover\:bg-slate-800:hover { background-color: #1a4a36 !important; }
        body.theme-emerald .bg-white\/90,
        body.theme-emerald .dark\:bg-slate-900\/90 { background-color: rgba(13,43,31,0.95) !important; }
        body.theme-emerald .bg-slate-50\/70,
        body.theme-emerald .dark\:bg-slate-800\/50 { background-color: rgba(10,31,24,0.7) !important; }

        /* ── Theme: Royal Purple ───────────────────────────────── */
        body.theme-purple {
            --accent: #a855f7;
            --accent-hover: #9333ea;
            background-color: #120a1e !important;
            color: #e9d5ff !important;
        }
        body.theme-purple .bg-white,
        body.theme-purple .dark\:bg-slate-900 { background-color: #1c1030 !important; }
        body.theme-purple .bg-slate-50,
        body.theme-purple .dark\:bg-slate-950 { background-color: #120a1e !important; }
        body.theme-purple .border-slate-200,
        body.theme-purple .dark\:border-slate-800 { border-color: #3b1f5e !important; }
        body.theme-purple .text-slate-900,
        body.theme-purple .dark\:text-white { color: #faf5ff !important; }
        body.theme-purple .text-slate-500,
        body.theme-purple .dark\:text-slate-400 { color: #c4b5fd !important; }
        body.theme-purple .bg-indigo-600 { background-color: #a855f7 !important; }
        body.theme-purple .hover\:bg-slate-100:hover,
        body.theme-purple .dark\:hover\:bg-slate-800:hover { background-color: #3b1f5e !important; }
        body.theme-purple .bg-white\/90,
        body.theme-purple .dark\:bg-slate-900\/90 { background-color: rgba(28,16,48,0.95) !important; }
        body.theme-purple .bg-slate-50\/70,
        body.theme-purple .dark\:bg-slate-800\/50 { background-color: rgba(18,10,30,0.7) !important; }

        /* ── Theme: Charcoal ───────────────────────────────────── */
        body.theme-charcoal {
            --accent: #f59e0b;
            --accent-hover: #d97706;
            background-color: #111111 !important;
            color: #d4d4d4 !important;
        }
        body.theme-charcoal .bg-white,
        body.theme-charcoal .dark\:bg-slate-900 { background-color: #1c1c1c !important; }
        body.theme-charcoal .bg-slate-50,
        body.theme-charcoal .dark\:bg-slate-950 { background-color: #111111 !important; }
        body.theme-charcoal .border-slate-200,
        body.theme-charcoal .dark\:border-slate-800 { border-color: #2e2e2e !important; }
        body.theme-charcoal .text-slate-900,
        body.theme-charcoal .dark\:text-white { color: #f5f5f5 !important; }
        body.theme-charcoal .text-slate-500,
        body.theme-charcoal .dark\:text-slate-400 { color: #a3a3a3 !important; }
        body.theme-charcoal .bg-indigo-600 { background-color: #f59e0b !important; }
        body.theme-charcoal .hover\:bg-slate-100:hover,
        body.theme-charcoal .dark\:hover\:bg-slate-800:hover { background-color: #2e2e2e !important; }
        body.theme-charcoal .bg-white\/90,
        body.theme-charcoal .dark\:bg-slate-900\/90 { background-color: rgba(28,28,28,0.95) !important; }
        body.theme-charcoal .bg-slate-50\/70,
        body.theme-charcoal .dark\:bg-slate-800\/50 { background-color: rgba(17,17,17,0.7) !important; }
    </style>
    <script>
        (function () {
            var stored = localStorage.getItem('app_theme');
            var allowed = ['light', 'dark', 'teal', 'navy', 'emerald', 'purple', 'charcoal'];
            var theme = allowed.includes(stored) ? stored : 'light';

            /* Dark-mode Tailwind prefix only for dark/navy/emerald/purple/charcoal */
            var darkThemes = ['dark', 'navy', 'emerald', 'purple', 'charcoal'];
            document.documentElement.classList.toggle('dark', darkThemes.includes(theme));

            document.addEventListener('DOMContentLoaded', function () {
                document.body.classList.remove(
                    'theme-light','theme-dark','theme-teal',
                    'theme-navy','theme-emerald','theme-purple','theme-charcoal'
                );
                document.body.classList.add('theme-' + theme);
            });
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    boxShadow: {
                        soft: '0 10px 30px rgba(2, 6, 23, 0.06)',
                    }
                }
            }
        };
    </script>
</head>
<body class="h-full theme-light bg-slate-50 text-slate-700 dark:bg-slate-950 dark:text-slate-200 transition-colors duration-300">
<div class="min-h-screen">
    <x-saas-sidebar />

    <div id="app-shell" class="lg:pl-72 transition-all duration-300">
        <x-saas-navbar />

        <main class="px-4 sm:px-6 py-6">
            @if(session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 text-sm dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-300">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 px-4 py-3 text-sm dark:bg-rose-500/10 dark:border-rose-500/20 dark:text-rose-300">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 px-4 py-3 text-sm dark:bg-rose-500/10 dark:border-rose-500/20 dark:text-rose-300">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<div id="toastContainer" class="fixed top-4 right-4 z-[60] space-y-2"></div>

<script>
(function () {
    const root = document.documentElement;
    const body = document.body;
    const allowedThemes = ['light', 'dark', 'teal', 'navy', 'emerald', 'purple', 'charcoal'];
    const darkThemes = ['dark', 'navy', 'emerald', 'purple', 'charcoal'];

    function normalizeTheme(theme) {
        return allowedThemes.includes(theme) ? theme : 'light';
    }

    window.applyAppTheme = function (theme) {
        const normalized = normalizeTheme(theme);

        localStorage.setItem('app_theme', normalized);
        root.classList.toggle('dark', darkThemes.includes(normalized));

        body.classList.remove(
            'theme-light', 'theme-dark', 'theme-teal',
            'theme-navy', 'theme-emerald', 'theme-purple', 'theme-charcoal'
        );
        body.classList.add(`theme-${normalized}`);

        const selector = document.getElementById('themeSelector');
        if (selector && selector.value !== normalized) {
            selector.value = normalized;
        }
    };

    const initialTheme = normalizeTheme(localStorage.getItem('app_theme') || 'light');
    window.applyAppTheme(initialTheme);

    const selector = document.getElementById('themeSelector');
    if (selector) {
        selector.value = initialTheme;
        selector.addEventListener('change', function () {
            window.applyAppTheme(selector.value);
        });
    }

    const sidebar = document.getElementById('saas-sidebar');
    const mobileToggle = document.getElementById('mobileSidebarToggle');
    const desktopCollapse = document.getElementById('sidebarCollapseBtn');
    const appShell = document.getElementById('app-shell');

    function closeMobileSidebar() {
        if (window.innerWidth < 1024) {
            sidebar?.classList.add('-translate-x-full');
        }
    }

    if (window.innerWidth < 1024) {
        sidebar?.classList.add('-translate-x-full');
    }

    mobileToggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('-translate-x-full');
    });

    desktopCollapse?.addEventListener('click', () => {
        const collapsed = sidebar?.classList.toggle('lg:w-20');
        if (collapsed) {
            appShell?.classList.remove('lg:pl-72');
            appShell?.classList.add('lg:pl-20');
        } else {
            appShell?.classList.add('lg:pl-72');
            appShell?.classList.remove('lg:pl-20');
        }
    });

    document.addEventListener('click', (e) => {
        if (window.innerWidth < 1024 && sidebar && mobileToggle) {
            if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                closeMobileSidebar();
            }
        }
    });

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', function () {
            if (form.hasAttribute('data-no-global-processing')) {
                return;
            }

            const method = (form.getAttribute('method') || 'GET').toUpperCase();
            const isMutating = ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method);

            if (!isMutating) {
                return;
            }

            if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                return;
            }

            const btn = form.querySelector('button[type="submit"]:not([data-no-global-processing])');
            if (btn) {
                btn.dataset.originalText = btn.innerHTML;
                btn.disabled = true;
                btn.classList.add('opacity-70', 'cursor-not-allowed');
                btn.innerHTML = 'Processing...';
            }
        });
    });
})();
</script>
@stack('scripts')
</body>
</html>
