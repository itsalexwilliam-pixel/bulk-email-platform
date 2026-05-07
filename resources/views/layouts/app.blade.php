<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Bulk Mailer') }}</title>
    <script>
        (function () {
            var stored = localStorage.getItem('app_theme');
            var allowed = ['light', 'dark', 'teal'];
            var theme = allowed.includes(stored) ? stored : 'light';

            document.documentElement.classList.toggle('dark', theme === 'dark');

            document.addEventListener('DOMContentLoaded', function () {
                document.body.classList.remove('theme-light', 'theme-dark', 'theme-teal');
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
    const allowedThemes = ['light', 'dark', 'teal'];

    function normalizeTheme(theme) {
        return allowedThemes.includes(theme) ? theme : 'light';
    }

    window.applyAppTheme = function (theme) {
        const normalized = normalizeTheme(theme);

        localStorage.setItem('app_theme', normalized);
        root.classList.toggle('dark', normalized === 'dark');

        body.classList.remove('theme-light', 'theme-dark', 'theme-teal');
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
