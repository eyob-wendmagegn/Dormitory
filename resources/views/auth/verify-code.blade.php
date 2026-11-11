<x-guest-layout title="Verify Security Code | Woldia University">
    <div class="min-h-screen bg-[#0B0F17] relative flex items-center justify-center p-3 sm:p-5 lg:p-6">
        <!-- Top-Right Corner Dark / Light Theme Toggle Button -->
        <button type="button" onclick="toggleTheme()" title="Toggle Dark / Light Mode" aria-label="Toggle Dark / Light Mode" class="fixed top-4 right-4 sm:top-6 sm:right-6 z-50 p-2.5 rounded-xl bg-[#111726]/85 backdrop-blur-md text-slate-300 hover:text-white border border-white/10 shadow-lg transition-all cursor-pointer">
            <svg class="w-4 h-4 theme-icon-sun" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <svg class="w-4 h-4 theme-icon-moon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        </button>

        <div class="max-w-4xl w-full grid grid-cols-1 lg:grid-cols-2 rounded-2xl sm:rounded-3xl overflow-hidden bg-[#111726]/85 backdrop-blur-xl border border-white/[0.08] shadow-2xl shadow-black/50">
            
            <!-- Left Panel (Verification Form) -->
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
                    <div class="mb-4 sm:mb-5">
                        <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Enter Verification Code</h1>
                        <p class="text-xs sm:text-sm text-slate-400 mt-1 leading-relaxed">
                            We have dispatched a 6-digit verification code to 
                            <span class="text-emerald-400 font-medium">{{ $email ?? session('verify_email') ?? session('pending_activation.email') ?? 'your university email' }}</span>.
                            Please check your inbox.
                        </p>
                    </div>

                    <!-- Session Status / Flash Messages -->
                    @if (session('status'))
                        <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-start gap-2.5">
                            <svg class="w-4 h-4 shrink-0 text-emerald-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ session('status') }}</span>
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

                    <!-- Verification Code Form -->
                    <form method="POST" action="{{ route('verification.verify') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email ?? session('verify_email') ?? session('pending_activation.email') ?? old('email') }}" />

                        <!-- 6-Digit Code Input -->
                        <div>
                            <label for="code" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                                6-Digit Verification Code
                            </label>
                            <div class="relative">
                                <input id="code" type="text" name="code" required autofocus maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
                                       placeholder="000000"
                                       class="w-full bg-[#0B101D] border border-white/10 text-white placeholder-slate-600 rounded-xl px-4 py-2.5 text-center text-xl tracking-[0.35em] font-mono font-bold transition focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500/50" />
                            </div>
                            @error('code')
                                <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold shadow-md shadow-emerald-500/20 rounded-xl px-4 py-2.5 text-sm transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                                <span>Verify Code & Complete Activation</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </div>
                    </form>

                    <!-- Resend OTP Code Form -->
                    <div class="mt-5 pt-3.5 border-t border-white/[0.06] text-center">
                        <form method="POST" action="{{ route('verification.resend') }}" class="inline">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email ?? session('verify_email') ?? session('pending_activation.email') ?? old('email') }}" />
                            <p class="text-xs text-slate-400">
                                Didn't receive the email code?
                                <button type="submit" class="font-semibold text-emerald-400 hover:text-emerald-300 ml-1 transition-colors underline decoration-emerald-500/40 cursor-pointer bg-transparent border-0 p-0">
                                    Resend 6-digit code
                                </button>
                            </p>
                        </form>
                    </div>
                </div>

                <!-- Footer Navigation Links -->
                <div class="mt-5 pt-2 flex items-center justify-between text-xs text-slate-400">
                    <a href="{{ route('login') }}" class="hover:text-white transition-colors inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Back to Sign In</span>
                    </a>
                    <a href="{{ route('landing') }}" class="hover:text-white transition-colors">
                        Home
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
                        <span>Two-Factor Email Verification</span>
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
</x-guest-layout>
