<x-guest-layout>
    <!-- Session Status / Alert -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if ($errors->any())
    <div class="mb-4 p-3.5 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs shadow-lg animate-shake">
        <div class="flex items-center gap-2 font-semibold mb-1">
            <span>⚠️</span>
            <span>{{ __('Gagal Masuk') }}</span>
        </div>
        <ul class="list-disc list-inside space-y-0.5 text-rose-200">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div x-data="{ showPassword: false }">
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-semibold text-ocean-300 mb-1.5">
                    {{ __('Alamat Email / Akun') }}
                </label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 z-20 pointer-events-none text-cyan-400 flex items-center justify-center filter drop-shadow">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <input id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="user@nagakecil.site"
                        class="w-full pl-11 pr-4 py-2.5 text-sm rounded-xl bg-slate-900/70 border border-white/20 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/50 focus:border-cyan-400 transition-all" />
                </div>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-ocean-300 mb-1.5">
                    {{ __('Kata Sandi') }}
                </label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 z-20 pointer-events-none text-cyan-400 flex items-center justify-center filter drop-shadow">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M7 11V7a5 5 0 0110 0v4" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <input id="password"
                        :type="showPassword ? 'text' : 'password'"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full pl-11 pr-11 py-2.5 text-sm rounded-xl bg-slate-900/70 border border-white/20 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/50 focus:border-cyan-400 transition-all" />

                    {{-- Toggle Password Button --}}
                    <button type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-3.5 z-20 text-slate-400 hover:text-white transition-colors focus:outline-none"
                        :title="showPassword ? 'Sembunyikan Kata Sandi' : 'Tampilkan Kata Sandi'">
                        <template x-if="!showPassword">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </template>
                        <template x-if="showPassword">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </template>
                    </button>
                </div>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between text-xs pt-1">
                <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none text-slate-300">
                    <input id="remember_me"
                        type="checkbox"
                        class="w-4 h-4 rounded bg-slate-800 border-white/20 text-ocean-500 focus:ring-ocean-400 focus:ring-offset-0 focus:ring-1 cursor-pointer"
                        name="remember">
                    <span>{{ __('Ingat Saya') }}</span>
                </label>

                @if (Route::has('password.request'))
                <a class="text-ocean-400 hover:text-ocean-300 hover:underline transition-colors" href="{{ route('password.request') }}">
                    {{ __('Lupa kata sandi?') }}
                </a>
                @endif
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" class="btn-submit">
                    <span>{{ __('Masuk ke Sistem') }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>

            <div class="relative my-4 flex items-center">
                <div class="flex-grow border-t border-slate-700"></div>
                <span class="flex-shrink-0 mx-4 text-xs text-slate-400">Atau masuk dengan</span>
                <div class="flex-grow border-t border-slate-700"></div>
            </div>

            <div class="pt-2">
                <a href="{{ route('auth.google.redirect') }}" class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-slate-300 bg-slate-800/50 border border-slate-600 rounded-xl hover:bg-slate-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4" />
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05" />
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
                    </svg>
                    <span>SSO with Google</span>
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>