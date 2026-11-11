<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Woldia University - Dormitory Management System' }}</title>

    <!-- Google Fonts Figtree -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

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
<body class="bg-[#0B0F17] text-slate-100 antialiased min-h-screen flex flex-col selection:bg-emerald-500/30 selection:text-emerald-300 font-sans">
    <!-- Ambient top glow effect -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[350px] bg-emerald-500/10 blur-[140px] rounded-full pointer-events-none -z-10"></div>

    <div class="flex-grow flex flex-col">
        {{ $slot }}
    </div>

    <!-- Smooth Scrolling, Section Offset & Form Loading State Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    const targetId = this.getAttribute('href');
                    if (targetId && targetId !== '#') {
                        const targetEl = document.querySelector(targetId);
                        if (targetEl) {
                            e.preventDefault();
                            const headerOffset = 85;
                            const elementPosition = targetEl.getBoundingClientRect().top;
                            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                            window.scrollTo({
                                top: offsetPosition,
                                behavior: 'smooth'
                            });
                            history.pushState(null, null, targetId);
                        }
                    }
                });
            });

            // Add clean loading spinner on auth form submit to prevent duplicate clicks during email dispatch
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function () {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                        const isLinkButton = submitBtn.classList.contains('bg-transparent');
                        if (isLinkButton) {
                            submitBtn.textContent = 'Sending code...';
                        } else {
                            submitBtn.innerHTML = `
                                <svg class="animate-spin h-4 w-4 text-slate-950" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Processing...</span>
                            `;
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
