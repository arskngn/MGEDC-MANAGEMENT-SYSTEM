<x-guest-layout>
    <div class="auth-login-card overflow-hidden rounded-2xl">
        <div class="auth-login-header-wrap">
            <div class="auth-login-header-bg" aria-hidden="true"></div>
            <div class="auth-login-header-content text-white text-center pt-8 pb-10 sm:pt-10 sm:pb-12 px-5 sm:px-6 flex flex-col items-center">
                <div class="mb-3 sm:mb-4">
                    <a href="/">
                        <x-application-logo type="dark" class="h-16 w-16 sm:h-20 sm:w-20 rounded-full object-cover border-2 border-white shadow-lg" />
                    </a>
                </div>
                <h2 class="text-2xl sm:text-4xl font-bold leading-tight">
                    Welcome to {{ $generalSetting->site_title ?? 'MGEDC' }}
                </h2>
            </div>
        </div>

        <div class="auth-login-body px-5 py-6 sm:px-8 sm:py-10">
            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form id="loginForm" method="POST" action="{{ route('login') }}" data-home-url="{{ url('/') }}">
                @csrf

                <!-- Email -->
                <div class="auth-login-field">
                    <x-input-label for="email" value="Username" class="auth-login-label" />
                    <x-text-input id="email"
                        class="auth-login-input block mt-1 w-full"
                        type="email"
                        name="email"
                        placeholder="Enter your email"
                        :value="old('email')"
                        required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="auth-login-field">
                    <x-input-label for="password" value="Password" class="auth-login-label" />

                    <div class="relative mt-1">
                        <x-text-input id="password"
                            class="auth-login-input block w-full pr-11"
                            type="password"
                            name="password"
                            placeholder="Enter your password"
                            required autocomplete="current-password" />

                        <!-- Eye Icon -->
                        <div id="loginPasswordToggle" class="auth-login-toggle absolute inset-y-0 right-0 pr-3 flex items-center z-10 cursor-pointer" role="button" tabindex="-1" aria-label="Toggle password visibility (Shift)">
                            <!-- Open Eye Icon (shown when password is hidden) -->
                            <svg id="eyeIcon"
                                class="h-5 w-5"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Closed Eye Icon (shown when password is visible) -->
                            <svg id="eyeOffIcon"
                                class="h-5 w-5 hidden"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.12 9.12l.841-.84a1 1 0 00-1.414-1.415l-.84.84m7.07-7.07l.841-.84A1 1 0 0012 2.172m0 0a10 10 0 00-7.08 2.92m7.08-2.92l.84-.84a1 1 0 00-1.414-1.414l-.84.84m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </div>
                    </div>

                    <p id="caps-lock-warning"
                        class="text-red-400 text-xs mt-1.5 hidden">
                        Capslock is on
                    </p>

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Login Button -->
                <div class="auth-login-submit-wrap mt-6 sm:mt-8">
                    <button type="submit"
                        class="auth-login-submit w-full text-white py-3 text-base sm:text-lg font-semibold rounded-lg"
                        title="Shortcut: Enter">
                        {{ __('Log in') }}
                    </button>
                </div>

                <div class="auth-login-home-wrap mt-1 text-center">
                    <a id="loginHomeLink" href="{{ url('/') }}" data-back-link class="auth-login-home-link" title="Shortcut: Esc">
                        return home page
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/navigation-shortcuts.js') }}?v={{ @filemtime(public_path('js/navigation-shortcuts.js')) ?: time() }}"></script>
    <script src="{{ asset('js/login-shortcuts.js') }}?v={{ @filemtime(public_path('js/login-shortcuts.js')) ?: time() }}"></script>

    @if($errors->has('email') && str_contains($errors->first('email'), 'Please wait'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const errorText = {{ Js::from($errors->first('email')) }};
            const match = errorText.match(/\((\d+) seconds\)/);
            if (!match) return;

            let secondsLeft = parseInt(match[1]);
            const form = document.getElementById('loginForm');
            const submitBtn = form.querySelector('button[type="submit"]');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            // Disable form
            submitBtn.disabled = true;
            emailInput.disabled = true;
            passwordInput.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

            // Create countdown banner
            const banner = document.createElement('div');
            banner.id = 'lockout-banner';
            banner.className = 'mt-4 p-4 rounded-lg bg-red-50 border border-red-300 text-center';
            banner.innerHTML = `
                <div class="flex items-center justify-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="text-red-700 font-semibold text-sm">Too many failed attempts. Account temporarily locked.</span>
                </div>
                <div class="text-gray-700 text-sm">
                    You can try again in <span id="countdown-timer" class="font-mono font-bold text-red-600 text-lg">0:00</span>
                </div>`;

            const submitWrap = form.querySelector('.auth-login-submit-wrap');
            submitWrap.before(banner);

            function formatTime(s) {
                const m = Math.floor(s / 60);
                const sec = s % 60;
                return `${m}:${sec.toString().padStart(2, '0')}`;
            }

            const timerEl = document.getElementById('countdown-timer');
            timerEl.textContent = formatTime(secondsLeft);

            const interval = setInterval(function () {
                secondsLeft--;
                if (secondsLeft <= 0) {
                    clearInterval(interval);
                    banner.innerHTML = `<p class="text-green-700 font-semibold text-sm">✓ You can now try again.</p>`;
                    submitBtn.disabled = false;
                    emailInput.disabled = false;
                    passwordInput.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    timerEl.textContent = formatTime(secondsLeft);
                }
            }, 1000);
        });
    </script>
    @endif
</x-guest-layout>