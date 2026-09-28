<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mindoro Golden Eagle Distribution Corporation</title>
    @if($generalSetting && $generalSetting->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset($generalSetting->favicon) }}">
    @endif
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}?v={{ @filemtime(public_path('css/welcome.css')) ?: time() }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
</head>
<body class="is-landing-page">
    <div id="customCursor" class="custom-cursor" aria-hidden="true"></div>
    <script>
        (function () {
            var cursor = document.getElementById('customCursor');
            if (!cursor) return;
            document.documentElement.classList.add('has-custom-cursor');
            document.body.appendChild(cursor);
            window.addEventListener('mousemove', function (e) {
                cursor.classList.add('is-active');
                cursor.style.transform = 'translate3d(' + e.clientX + 'px,' + e.clientY + 'px,0) translate(-50%,-50%)';
            }, { passive: true });
            document.addEventListener('mouseover', function (e) {
                var interactive = e.target.closest('a, button, input[type="color"]');
                if (interactive) {
                    cursor.style.width = '50px';
                    cursor.style.height = '50px';
                    cursor.style.borderWidth = '3px';
                } else {
                    cursor.style.width = '40px';
                    cursor.style.height = '40px';
                    cursor.style.borderWidth = '2px';
                }
            }, { passive: true });
        })();
    </script>

    <div id="colorAdjusterPanel" class="color-adjuster-panel">
        <div class="color-adjuster-panel__header">
            <h2>Color Adjuster</h2>
            <button type="button" id="closeAdjusterBtn" class="color-adjuster-panel__close" aria-label="Close color adjuster">&times;</button>
        </div>
        <div class="color-adjuster-panel__body">
            @foreach(range(1, 6) as $i)
            <div class="color-row">
                <label for="colorPicker{{ $i }}">Color {{ $i }}</label>
                <div class="color-row__controls">
                    <input type="color" id="colorPicker{{ $i }}" value="#F15A22">
                    <input type="text" id="colorValue{{ $i }}" value="#F15A22" readonly>
                    <button type="button" class="copy-btn" data-copy="{{ $i }}">Copy</button>
                </div>
            </div>
            @endforeach
            <button type="button" id="exportAllBtn" class="export-all-btn">Export All Colors</button>
        </div>
    </div>

    <div class="landing-page">
        <header class="landing-header">
            <div class="landing-header__inner">
                <div class="landing-header__actions">
                    <a href="https://www.facebook.com/mgedcorp" target="_blank" rel="noopener noreferrer" class="landing-header__icon" data-landing-shortcut="facebook" aria-label="Facebook (shortcut: F)">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    <a href="https://mail.google.com/mail/u/0/#inbox?compose=DmwnWsCPcSJpNkzSDxTSlKxMJqhdvLHgRxcXwVJxcDQKBJKPGNLFRTkVbZhwZpLCwpxgQNqGVwNV" target="_blank" rel="noopener noreferrer" class="landing-header__icon" data-landing-shortcut="gmail" aria-label="Email us (shortcut: G)">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </a>
                    <a href="{{ route('login') }}" class="landing-login-btn" data-landing-shortcut="login" title="Login (shortcut: Enter)">Login</a>
                </div>
            </div>
        </header>

        <main class="landing-hero">
            <div class="landing-hero__content">
                <h1 class="landing-hero__title">Mindoro Golden Eagle Distribution Corporation</h1>
                <p class="landing-hero__subtitle">We are the exclusive of some products here in Mindoro.</p>
            </div>
        </main>

        <footer class="landing-tools">
            <button type="button" id="toggleAdjusterBtn" class="toggle-adjuster-btn" title="Adjust colors (shortcut: A)">Adjust Colors</button>
            <div class="color-controls" role="group" aria-label="Color schemes">
                @foreach(range(1, 8) as $scheme)
                <button type="button" class="color-btn{{ $scheme === 1 ? ' active' : '' }}" data-scheme="{{ $scheme }}" title="Scheme {{ $scheme }} (shortcut: {{ $scheme }})">Scheme {{ $scheme }}</button>
                @endforeach
            </div>
        </footer>
    </div>

    <script>
        window.PAGE_APPEARANCE = @json($pageAppearance ?? ['scheme' => 1, 'colors' => []]);
    </script>
    <script src="{{ asset('js/welcome-background.js') }}?v={{ filemtime(public_path('js/welcome-background.js')) }}"></script>
    <script src="{{ asset('js/welcome-shortcuts.js') }}?v={{ filemtime(public_path('js/welcome-shortcuts.js')) }}"></script>
</body>
</html>
