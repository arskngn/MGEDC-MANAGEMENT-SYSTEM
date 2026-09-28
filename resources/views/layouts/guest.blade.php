@php
    $appearanceService = app(\App\Services\AppearanceSettingService::class);
    $systemAppearance = $pageAppearance ?? $appearanceService->forSystem();
    $gradientFrom = $systemAppearance['colors'][0] ?? '#F15A22';
    $gradientTo = $systemAppearance['colors'][1] ?? '#0A0E27';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $generalSetting->site_title ?? config('app.name', 'Laravel') }}</title>

        @if(isset($generalSetting) && $generalSetting->favicon)
            <link rel="icon" type="image/x-icon" href="{{ asset($generalSetting->favicon) }}">
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('css/auth-login.css') }}?v={{ @filemtime(public_path('css/auth-login.css')) ?: time() }}">

        <style>
            :root {
                --login-gradient-from: {{ $gradientFrom }};
                --login-gradient-to: {{ $gradientTo }};
                --login-shadow: 0 4px 22px {{ $gradientFrom }}66;
                --login-shadow-hover: 0 6px 30px {{ $gradientTo }}80;
            }
            
            /* Apply global system gradient to primary buttons */
            .bg-\[\#4634ff\], button.bg-\[\#4634ff\], a.bg-\[\#4634ff\] {
                background: linear-gradient(135deg, var(--login-gradient-from) 0%, var(--login-gradient-to) 100%) !important;
                border-color: transparent !important;
                color: white !important;
            }
            .hover\:bg-\[\#3828cc\]:hover {
                background: linear-gradient(135deg, var(--login-gradient-to) 0%, var(--login-gradient-from) 100%) !important;
                opacity: 0.9;
            }

            body.guest-gradient-page {
                margin: 0;
                min-height: 100vh;
                background: #0a0e27;
            }
            #guestWebGLApp {
                position: fixed;
                inset: 0;
                width: 100%;
                height: 100%;
                z-index: 0;
                pointer-events: none;
            }
            .guest-gradient-page .guest-content {
                position: relative;
                z-index: 10;
            }
            .appearance-preview-canvas {
                position: absolute !important;
                inset: 0 !important;
                width: 100% !important;
                height: 100% !important;
            }
        </style>
    </head>
    <body class="font-sans text-gray-200 antialiased min-h-screen guest-gradient-page{{ request()->routeIs('login') ? ' is-login-page' : '' }}">
        <div id="guestWebGLApp" aria-hidden="true"></div>

        <div class="guest-content min-h-screen min-h-[100dvh] flex flex-col justify-center items-center px-4 py-6 sm:p-0">
            <div class="w-full max-w-[22rem] sm:max-w-md sm:mt-6">
                {{ $slot }}
            </div>
        </div>

        <script>
            window.PAGE_APPEARANCE = @json($systemAppearance);
        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
        <script src="{{ asset('js/welcome-background.js') }}?v={{ @filemtime(public_path('js/welcome-background.js')) ?: time() }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const mount = document.getElementById('guestWebGLApp');
                if (!mount || !window.LiquidGradientApp) return;

                const guestApp = new window.LiquidGradientApp({ mountElement: mount });
                if (window.PAGE_APPEARANCE && window.applyPageAppearance) {
                    window.applyPageAppearance(guestApp, window.PAGE_APPEARANCE);
                }
                guestApp.render();
                (function tick() {
                    guestApp.render();
                    requestAnimationFrame(tick);
                })();

                const passwordInput = document.getElementById('password');
                const togglePassword = document.getElementById('togglePassword');
                const capsLockWarning = document.getElementById('caps-lock-warning');

                if (togglePassword && passwordInput) {
                    togglePassword.addEventListener('click', function () {
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordInput.setAttribute('type', type);
                        this.querySelector('path:last-child').setAttribute('d', type === 'password' ? 'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' : 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .946-3.11 3.56-5.604 6.837-6.182m1.5-1.5A12.006 12.006 0 0112 5c4.478 0 8.268 2.943 9.542 7a11.94 11.94 0 01-2.05 3.572m-4.242 4.242A9.95 9.95 0 0112 19c-1.539 0-3.01-.357-4.375-1.002m1.5-1.5l-1.5 1.5m0 0l-1.5-1.5m1.5 1.5V12m0 0l1.5 1.5m-1.5-1.5L12 9m0 0l1.5-1.5M12 9l-1.5-1.5');
                    });
                }

                if (passwordInput && capsLockWarning) {
                    passwordInput.addEventListener('keyup', function (event) {
                        capsLockWarning.style.display = event.getModifierState('CapsLock') ? 'block' : 'none';
                    });
                }

                const passwordConfirmationInput = document.getElementById('password_confirmation');
                const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');

                if (togglePasswordConfirmation && passwordConfirmationInput) {
                    togglePasswordConfirmation.addEventListener('click', function () {
                        const type = passwordConfirmationInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordConfirmationInput.setAttribute('type', type);
                        this.querySelector('path:last-child').setAttribute('d', type === 'password' ? 'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' : 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .946-3.11 3.56-5.604 6.837-6.182m1.5-1.5A12.006 12.006 0 0112 5c4.478 0 8.268 2.943 9.542 7a11.94 11.94 0 01-2.05 3.572m-4.242 4.242A9.95 9.95 0 0112 19c-1.539 0-3.01-.357-4.375-1.002m1.5-1.5l-1.5 1.5m0 0l-1.5-1.5m1.5 1.5V12m0 0l1.5 1.5m-1.5-1.5L12 9m0 0l1.5-1.5M12 9l-1.5-1.5');
                    });
                }
            });
        </script>
    </body>
</html>
