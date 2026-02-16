<!doctype html>
<html lang="en" class="">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ShopPulse')</title>

    <!-- Tailwind CDN (NO VITE / NO NPM) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {}
            }
        }
    </script>

    <!-- Google ICONS -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- Favicon --}}
    @php
    $favicon = $appSettings->favicon_path ? asset('storage/' . $appSettings->favicon_path) : asset('favicon.ico'); // fallback
    @endphp

    <link rel="icon" type="image/png" href="{{ $favicon }}">
    <link rel="shortcut icon" href="{{ $favicon }}">

    <style>
        html,
        body {
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        }

        .overlay {
            backdrop-filter: blur(6px);
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-50">
    <div class="min-h-screen">

        <!-- TOPBAR -->
        <header
            class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/70 dark:bg-slate-950/70 dark:border-slate-800/70 backdrop-blur">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between gap-3">

                    <!-- Left -->
                    <div class="flex items-center gap-3">
                        <button id="btnOpenDrawer"
                            class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white shadow-sm hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800"
                            aria-label="Open menu">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                            @php
                            $logo = $appSettings->logo_path ? asset('storage/' . $appSettings->logo_path) : null;
                            $name = $appSettings->software_name ?? 'ShopPulse';
                            @endphp

                            <div
                                class="h-10 w-10 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-soft dark:border-slate-800 dark:bg-slate-900 grid place-items-center">
                                @if ($logo)
                                <img src="{{ $logo }}" alt="logo" class="h-full w-full object-cover">
                                @else
                                {{-- fallback gradient --}}
                                <div
                                    class="h-full w-full rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600">
                                </div>
                                @endif
                            </div>

                            <div class="leading-tight">
                                <div class="font-extrabold">{{ $name }}</div>
                                {{-- <div class="text-xs text-slate-500 dark:text-slate-400">@yield('subtitle', 'Dashboard')</div> --}}
                            </div>
                        </a>

                    </div>

                    <!-- Search (Desktop) -->
                    <div class="hidden md:flex flex-1 max-w-xl">
                        <div class="relative w-full">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <circle cx="11" cy="11" r="7"></circle>
                                    <path d="M21 21l-4.3-4.3"></path>
                                </svg>
                            </span>
                            <input
                                class="w-full rounded-2xl border border-slate-200 bg-white px-10 py-2.5 text-sm shadow-sm outline-none placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/40 dark:bg-slate-900 dark:border-slate-800"
                                placeholder="Search orders, products, customers..." />
                        </div>
                    </div>

                    <!-- Right -->
                    <div class="flex items-center gap-2">
                        <button id="btnTheme"
                            class="inline-flex h-10 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800"
                            type="button">
                            <span class="hidden sm:inline">Theme</span>
                            <span id="themeIcon" class="text-base">🌙</span>
                        </button>

                        <div class="relative">
                            <button id="btnProfile"
                                class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-2 py-1.5 shadow-sm hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800"
                                type="button">
                                <div class="h-9 w-9 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center text-white font-bold">
                                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                                </div>
                                <div class="hidden sm:block text-left leading-tight pr-1">
                                    <div class="text-sm font-semibold">{{ auth()->user()->name ?? 'Guest' }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ auth()->user()->email ?? 'guest@example.com' }}</div>
                                </div>
                                <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </button>

                            <div id="profileMenu"
                                class="hidden absolute right-0 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-soft dark:bg-slate-900 dark:border-slate-800">
                                <a class="block px-4 py-3 text-sm hover:bg-slate-50 dark:hover:bg-slate-800"
                                    href="{{ route('settings.general') }}">Settings</a>
                                <div class="h-px bg-slate-100 dark:bg-slate-800"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-3 text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30">
                                        Logout
                                    </button>
                                </form>
                                <!-- <button
                                    class="w-full text-left px-4 py-3 text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30"
                                    type="button">
                                    Logout
                                </button> -->
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Search (Mobile) -->
                <!-- <div class="md:hidden pb-3">
                    <input
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm outline-none placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/40 dark:bg-slate-900 dark:border-slate-800"
                        placeholder="Search..." />
                </div> -->
            </div>
        </header>

        <!-- PAGE LAYOUT -->
        <div class="mx-auto  px-4 sm:px-6 lg:px-8">
            <div class="flex lg:gap-6 sm:gap-0 py-6">

                <!-- SIDEBAR DESKTOP -->
                <aside class="hidden lg:block w-64 shrink-0">
                    <nav
                        class="sticky top-24 h-[calc(150vh-6rem)] overflow-y-auto rounded-2xl border border-slate-200 bg-white p-3 shadow-soft dark:bg-slate-900 dark:border-slate-800">
                        <div
                            class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Menu
                        </div>

                        @include('partials.sidebar')
                    </nav>
                </aside>

                <!-- MOBILE DRAWER -->
                <div class="lg:hidden">
                    <!-- Overlay -->
                    <div id="drawerOverlay" class="hidden fixed inset-0 z-[60] bg-slate-900/40 overlay"
                        aria-hidden="true"></div>

                    <!-- Panel -->
                    <aside id="drawerPanel"
                        class="fixed left-0 top-0 z-[70] h-full w-[86%] max-w-sm -translate-x-full transform bg-white p-4 shadow-soft transition-transform duration-300 ease-in-out dark:bg-slate-900 overflow-y-auto">
                        <div class="flex items-center justify-between">
                            {{-- <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                            <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600">
                            </div>
                            <div>
                                <div class="font-bold">ShopPulse</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">Dashboard</div>
                            </div>
                            </a> --}}

                            @php
                            $logo = $appSettings->logo_path ? asset('storage/' . $appSettings->logo_path) : null;
                            $name = $appSettings->software_name ?? 'ShopPulse';
                            @endphp

                            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                                <div
                                    class="h-10 w-10 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 grid place-items-center">
                                    @if ($logo)
                                    <img src="{{ $logo }}" alt="logo"
                                        class="h-full w-full object-cover">
                                    @else
                                    <div
                                        class="h-full w-full rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600">
                                    </div>
                                    @endif
                                </div>

                                <div>
                                    <div class="font-extrabold">{{ $name }}</div>
                                    {{-- <div class="text-xs text-slate-500 dark:text-slate-400">@yield('subtitle', 'Dashboard')</div> --}}
                                </div>
                            </a>


                            <button id="btnCloseDrawer"
                                class="h-10 w-10 rounded-2xl border border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800"
                                type="button" aria-label="Close menu">✕</button>
                        </div>

                        <div class="mt-4">
                            @include('partials.sidebar')
                        </div>
                    </aside>
                </div>


                <!-- MAIN -->
                <main class="flex-1">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight">@yield('pageTitle')</h1>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">@yield('pageDesc')</p>
                        </div>

                        @hasSection('pageActions')
                        <div class="flex flex-wrap items-center gap-2">@yield('pageActions')</div>
                        @endif
                    </div>

                    <div class="mt-6">
                        @yield('content')
                    </div>

                    <footer class="mt-10 pb-10 text-center text-xs text-slate-500 dark:text-slate-400">
                        © {{ date('Y') }} ShopPulse • Developed by Ali
                    </footer>
                </main>

            </div>
        </div>
    </div>

    @include('partials.scripts')
</body>

</html>