<x-guest-layout title="Sign In | Woldia University Dormitory System">
    <div class="min-h-screen bg-[#0B0F17] relative flex items-center justify-center p-3 sm:p-5 lg:p-6">
        <!-- Top-Right Corner Dark / Light Theme Toggle Button -->
        <button type="button" onclick="toggleTheme()" title="Toggle Dark / Light Mode" aria-label="Toggle Dark / Light Mode" class="fixed top-4 right-4 sm:top-6 sm:right-6 z-50 p-2.5 rounded-xl bg-[#111726]/85 backdrop-blur-md text-slate-300 hover:text-white border border-white/10 shadow-lg transition-all cursor-pointer">
            <svg class="w-4 h-4 theme-icon-sun" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <svg class="w-4 h-4 theme-icon-moon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        </button>

        <div class="max-w-4xl w-full grid grid-cols-1 lg:grid-cols-2 rounded-2xl sm:rounded-3xl overflow-hidden bg-[#111726]/85 backdrop-blur-xl border border-white/[0.08] shadow-2xl shadow-black/50">
            
            <!-- Left Panel (Authentication Form) -->
            <div class="p-6 sm:p-8 lg:p-9 flex flex-col justify-between">
                <div>
                    <!-- Brand Header -->
                    <a href="{{ route('landing') }}" class="inline-flex items-center gap-2.5 mb-5 group">
                        <img src="{{ asset('images/logo.jpg') }}" alt="Woldia University" class="w-9 h-9 sm:w-10 sm:h-10 object-contain rounded-xl border border-white/10 p-1 bg-white/5 shadow-md group-hover:border-emerald-500/40 transition-colors" />
                        <div>
                            <div class="text-[10px] font-semibold uppercase tracking-wider text-emerald-400">Woldia University</div>
                            <div class="text-xs sm:text-sm font-bold text-white tracking-tight">Dormitory System</div>
                        </div>
                    </a>

                    <!-- Title & Subtitle -->
                    <div class="mb-5">
                        <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Welcome Back</h1>
                        <p class="text-xs sm:text-sm text-slate-400 mt-1 leading-relaxed">Sign in to your account to manage housing, curfews, or view your room details.</p>
                    </div>

                    <!-- Session Status / Flash Messages -->
                    @if (session('status'))
                        <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-start gap-2.5">
                            <svg class="w-4 h-4 shrink-0 text-emerald-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-start gap-2.5">
                            <svg class="w-4 h-4 shrink-0 text-emerald-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs space-y-1">
                            @foreach ($errors->all() as $error)
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ $error }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Sign In Form -->
                    <form method="POST" action="{{ route('login') }}" autocomplete="off" class="space-y-3.5">
                        @csrf

                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">
                                Email Address
                            </label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="off" readonly
                                   onfocus="this.removeAttribute('readonly');"
                                   onmousedown="this.removeAttribute('readonly');"
                                   ontouchstart="this.removeAttribute('readonly');"
                                   placeholder="Enter your email"
                                   class="w-full bg-[#0B101D] border border-white/10 text-white placeholder-slate-500 rounded-xl px-3.5 py-2.5 text-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500/50" />
                            @error('email')
                                <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="password" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                    Password
                                </label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-[11px] text-slate-400 hover:text-emerald-400 transition-colors">
                                        Forgot password?
                                    </a>
                                @endif
                            </div>
                            <div class="relative">
                                <input id="password" type="password" name="password" required autocomplete="new-password" readonly
                                       onfocus="this.removeAttribute('readonly');"
                                       onmousedown="this.removeAttribute('readonly');"
                                       ontouchstart="this.removeAttribute('readonly');"
                                       placeholder="Enter your password"
                                       class="w-full bg-[#0B101D] border border-white/10 text-white placeholder-slate-500 rounded-xl px-3.5 py-2.5 pr-10 text-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500/50" />
                                <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition p-1 cursor-pointer">
                                    <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center justify-between pt-0.5">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" name="remember" class="w-3.5 h-3.5 rounded bg-[#0B101D] border-white/10 text-emerald-500 focus:ring-emerald-500/20" />
                                <span class="text-xs text-slate-400">Remember this device</span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-1">
                            <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold shadow-md shadow-emerald-500/20 rounded-xl px-4 py-2.5 text-sm transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                                <span>Sign In</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </form>

                    <!-- Register Link -->
                    <div class="mt-5 pt-3.5 border-t border-white/[0.06] text-center">
                        <p class="text-xs text-slate-400">
                            Don't have an account?
                            <a href="{{ route('register') }}" class="font-semibold text-emerald-400 hover:text-emerald-300 ml-1 transition-colors underline decoration-emerald-500/40">
                                Claim / Register Account
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Footer Link -->
                <div class="mt-4 text-center">
                    <a href="{{ route('landing') }}" class="text-xs text-slate-400 hover:text-white transition-colors inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Back to Home</span>
                    </a>
                </div>
            </div>

            <!-- Right Panel (Clean Campus Showcase) -->
            <div class="relative hidden lg:flex flex-col justify-between p-8 bg-cover bg-center overflow-hidden border-l border-white/[0.06] theme-preserve-dark"
                 style="background-image: url('{{ asset('images/landing.jpg') }}');">
                <div class="absolute inset-0 bg-gradient-to-t from-[#0B0F17]/90 via-[#0B0F17]/30 to-emerald-950/20"></div>

                <div class="relative z-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#0B0F17]/75 border border-emerald-400/30 text-emerald-300 text-[11px] font-medium backdrop-blur-md shadow-lg">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Campus Resident Portal • Woldia University</span>
                    </div>
                </div>

                <div class="relative z-10">
                    <div class="text-[11px] text-slate-300/80 flex items-center justify-between px-1 drop-shadow-md">
                        <span class="font-medium">Woldia University • Open Mind, Open Eyes</span>
                        <span>Academic Year 2026/27</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Toggle Password Visibility Script -->
    <script>
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const eyeOpen = btn.querySelector('.eye-open');
            const eyeClosed = btn.querySelector('.eye-closed');
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }
    </script>
</x-guest-layout>
