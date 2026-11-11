@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} | Woldia University Dormitory System</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">

    <!-- Google Fonts Figtree -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Zero-Flash Theme Initialization (Default: Dark) -->
    <script>
        (function () {
            const saved = localStorage.getItem('theme') || 'dark';
            const html = document.documentElement;
            if (saved === 'light') {
                html.classList.remove('dark');
                html.classList.add('light');
            } else {
                html.classList.add('dark');
                html.classList.remove('light');
            }
        })();

        window.toggleTheme = function () {
            const html = document.documentElement;
            const isLight = html.classList.contains('light');
            if (isLight) {
                html.classList.remove('light');
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                html.classList.remove('dark');
                html.classList.add('light');
                localStorage.setItem('theme', 'light');
            }
            window.dispatchEvent(new CustomEvent('theme-changed', {
                detail: { theme: isLight ? 'dark' : 'light' }
            }));
        };
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0B0F17] text-slate-100 antialiased min-h-screen selection:bg-emerald-500/30 selection:text-emerald-200 font-sans"
      x-data="{ sidebarOpen: false }">

    <!-- Ambient refined atmospheric mesh glows -->
    <div class="fixed top-0 right-1/4 w-[650px] h-[350px] bg-emerald-500/[0.04] blur-[150px] rounded-full pointer-events-none -z-10"></div>
    <div class="fixed bottom-0 left-1/4 w-[600px] h-[300px] bg-indigo-500/[0.03] blur-[160px] rounded-full pointer-events-none -z-10"></div>

    <!-- Mobile Drawer Backdrop Overlay -->
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-30 md:hidden"
         style="display: none;">
    </div>

    <!-- 1. Fixed Collapsible Sidebar (Frosted dark surface) -->
    <x-sidebar />

    <!-- 2. Main Application Wrapper (Offset by sidebar width md:pl-64 and fixed header pt-16) -->
    <div class="md:pl-64 flex flex-col min-h-screen pt-16">
        <!-- Pinned Header (Frosted glass) -->
        <x-header :title="$title" />

        <!-- Notification / Flash Messages -->
        @if (session('success') || session('error'))
            <div class="px-3 sm:px-5 pt-2.5 w-full">
                @if (session('success'))
                    <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-xs font-medium text-emerald-300 flex items-center justify-between shadow-xl backdrop-blur-md">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]"></span>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white transition-colors">&times;</button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-xs font-medium text-rose-300 flex items-center justify-between shadow-xl backdrop-blur-md">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-rose-400 shadow-[0_0_8px_rgba(244,63,94,0.8)]"></span>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-white transition-colors">&times;</button>
                    </div>
                @endif
            </div>
        @endif

        <!-- Main Content Area -->
        <main id="main-content" class="app-main flex-1 px-3 sm:px-5 pt-2.5 sm:pt-3 pb-4 w-full overflow-x-clip">
            {{ $slot }}
        </main>

        <!-- Reusable Shared Footer -->
        <x-footer />
    </div>
</body>
</html>
