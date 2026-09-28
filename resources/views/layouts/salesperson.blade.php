<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{
        darkMode: localStorage.getItem('dark_mode') === 'true' || (localStorage.getItem('dark_mode') === null && '{{ $generalSetting->default_theme ?? 'light' }}' === 'dark'),
        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('dark_mode', this.darkMode);
            if (this.darkMode) {
                document.documentElement.classList.add('dark');
                document.documentElement.style.colorScheme = 'dark';
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.style.colorScheme = 'light';
            }
        },
        mobileMenuOpen: false
      }"
      x-init="
        if (darkMode) {
            document.documentElement.classList.add('dark');
            document.documentElement.style.colorScheme = 'dark';
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.style.colorScheme = 'light';
        }
        document.documentElement.classList.remove('theme-loading');
      "
      :class="{ 'dark': darkMode }">
    <head>
        @include('layouts.partials.theme-init')
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="theme-color" content="#F15A22">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $generalSetting->site_title ?? config('app.name', 'Laravel') }}</title>

        @if($generalSetting && $generalSetting->favicon)
            <link rel="icon" type="image/x-icon" href="{{ asset($generalSetting->favicon) }}">
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Real-time WebSockets Config -->
        <script>
            window.__REVERB_CONFIG__ = {
                key:    @js(config('broadcasting.connections.reverb.client.key') ?: config('broadcasting.connections.reverb.key') ?: 'lxh3qzvrykrfuhwp1nnf'),
                host:   @js(config('broadcasting.connections.reverb.client.host') ?: config('broadcasting.connections.reverb.options.host') ?: 'reverb-production-a38d.up.railway.app'),
                port:   @js(config('broadcasting.connections.reverb.client.port') ?: 443),
                scheme: @js(config('broadcasting.connections.reverb.client.scheme') ?: 'https'),
            };
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            [x-cloak] { display: none !important; }
            html.dark { color-scheme: dark; }
            
            /* ── Gradient Utilities from Appearance Settings ── */
            @php
                $appearanceService = app(\App\Services\AppearanceSettingService::class);
                $appearance = $appearanceService->forSystem();
                $gradientFrom = $appearance['colors'][0] ?? '#F15A22';
                $gradientTo = $appearance['colors'][1] ?? '#0A0E27';
                $isSolidColorApp = (strtolower(trim($gradientFrom)) === strtolower(trim($gradientTo)));
            @endphp
            
            :root {
                --gradient-from: {{ $gradientFrom }};
                --gradient-to: {{ $gradientTo }};
                --accent-dark: color-mix(in srgb, {{ $gradientFrom }} 65%, #000000);
            }

            @if($isSolidColorApp)
            /* ── Solid-color-mode: ensure button contrast ── */

            /* Outlined buttons: use a darkened accent for text & border so
               light solid colors (yellow, lime, etc.) stay readable on white bg */
            main a[class*="border-[#5542ff]"][class*="text-[#5542ff]"],
            main button[class*="border-[#5542ff]"][class*="text-[#5542ff]"],
            main a[class*="border-[#4634ff]"][class*="text-[#4634ff]"],
            main button[class*="border-[#4634ff]"][class*="text-[#4634ff]"] {
                color: var(--accent-dark) !important;
                border-color: var(--accent-dark) !important;
            }

            /* "More" dropdown button icons */
            main button[class*="border-[#5542ff]"] svg[class*="text-[#5542ff]"],
            main button[class*="border-[#4634ff]"] svg[class*="text-[#4634ff]"] {
                color: var(--accent-dark) !important;
            }

            /* Customer name links that use text-[#5542ff] */
            main .text-\[\#5542ff\],
            main [class~="text-[#5542ff]"] {
                color: var(--accent-dark) !important;
            }

            /* Solid filled buttons: ensure white text is always readable */
            main button[class*="bg-[#5542ff]"],
            main a[class*="bg-[#5542ff]"],
            main button[class*="bg-[#4634ff]"],
            main a[class*="bg-[#4634ff]"] {
                color: #ffffff !important;
                text-shadow: 0 1px 2px rgba(0,0,0,0.15);
            }

            /* Table header text in solid mode */
            thead.bg-gray-50 th, thead.bg-gray-100 th {
                text-shadow: 0 1px 2px rgba(0,0,0,0.15);
            }
            @endif
            
            /* Apply global system gradient to primary buttons */
            .bg-\[\#4634ff\], button.bg-\[\#4634ff\], a.bg-\[\#4634ff\] {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%) !important;
                border-color: transparent !important;
                color: white !important;
            }
            .hover\:bg-\[\#3828cc\]:hover {
                background: linear-gradient(135deg, var(--gradient-to) 0%, var(--gradient-from) 100%) !important;
                opacity: 0.9;
            }

            /* Apply global system gradient to data table headers */
            thead.bg-gray-50 th, thead.bg-gray-100 th,
            thead tr.bg-\[\#0a1233\] th,
            main tr.bg-\[\#0a1233\] {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%) !important;
                color: rgba(255, 255, 255, 0.95) !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
            }
            .dark thead.bg-gray-700 th, .dark thead.bg-gray-800 th {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%) !important;
                color: rgba(255, 255, 255, 0.95) !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
            }
            
            .gradient-bg {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%);
            }
            
            .gradient-text {
                color: var(--gradient-from);
            }
            
            .gradient-border {
                border-color: var(--gradient-from);
            }
            
            .gradient-hover:hover {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%);
            }

            /* Replace hardcoded color #5542ff with gradient */
            [class~="border-[#5542ff]"] {
                border-color: var(--gradient-from) !important;
            }
            
            [class~="text-[#5542ff]"] {
                color: var(--gradient-from) !important;
            }
            
            [class~="focus:ring-[#5542ff]"]:focus {
                --tw-ring-color: var(--gradient-from) !important;
            }
            
            [class~="focus:border-[#5542ff]"]:focus {
                border-color: var(--gradient-from) !important;
            }
            
            [class~="ring-[#5542ff]"] {
                --tw-ring-color: var(--gradient-from) !important;
            }
            
            [class~="hover:text-[#5542ff]"]:hover {
                color: var(--gradient-from) !important;
            }
            
            [class~="hover:bg-[#5542ff]"]:hover {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%) !important;
            }

            /* bg-[#5542ff] solid backgrounds → gradient */
            button[class~="bg-[#5542ff]"],
            a[class~="bg-[#5542ff]"],
            input[type="submit"][class~="bg-[#5542ff]"] {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%) !important;
            }

            /* bg-[#4634ff] solid backgrounds → gradient */
            button[class~="bg-[#4634ff]"],
            a[class~="bg-[#4634ff]"],
            label[class~="bg-[#4634ff]"],
            input[type="submit"][class~="bg-[#4634ff]"],
            div[class~="bg-[#4634ff]"] {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%) !important;
            }

            /* shadow-[#4634ff] → gradient-from shadow */
            [class~="shadow-[#4634ff]"] {
                --tw-shadow-color: var(--gradient-from) !important;
            }

            /* Hover states for #4634ff and #3828cc and #4736d6 */
            [class~="hover:bg-[#4634ff]"]:hover,
            [class~="hover:bg-[#3828cc]"]:hover,
            [class~="hover:bg-[#4736d6]"]:hover,
            [class~="hover:bg-[#3b2ad6]"]:hover {
                background: linear-gradient(135deg, var(--gradient-to) 0%, var(--gradient-from) 100%) !important;
            }

            /* Border/text for #4634ff */
            [class*="border-[#4634ff]"] {
                border-color: var(--gradient-from) !important;
            }
            [class*="text-[#4634ff]"] {
                color: var(--gradient-from) !important;
            }
            [class*="hover:bg-[#4634ff]"]:hover {
                color: #ffffff !important;
            }
            [class*="hover:text-[#4634ff]"]:hover,
            .group:hover [class*="group-hover:text-[#4634ff]"] {
                color: var(--gradient-from) !important;
            }

            /* bg-[#5542ff]/10 and /5 tints → gradient-from tinted */
            [class*="bg-[#5542ff]/5"],
            [class*="bg-[#5542ff]/10"],
            [class*="hover:bg-[#5542ff]/5"]:hover,
            [class*="hover:bg-[#5542ff]/10"]:hover,
            [class*="hover:bg-[#5542ff]/20"]:hover {
                background-color: color-mix(in srgb, var(--gradient-from) 10%, transparent) !important;
            }

            /* file input accent */
            [class*="file:bg-[#5542ff]"] {
                --tw-file-bg: color-mix(in srgb, var(--gradient-from) 10%, transparent);
            }
            [class*="file:text-[#5542ff]"] {
                --tw-file-text: var(--gradient-from);
            }

            /* border-[#5542ff]/30 and /40 tints */
            [class*="border-[#5542ff]/30"],
            [class*="border-[#5542ff]/40"] {
                border-color: color-mix(in srgb, var(--gradient-from) 35%, transparent) !important;
            }

            /* focus:ring-[#5542ff] */
            [class*="focus:ring-[#5542ff]"]:focus {
                --tw-ring-color: var(--gradient-from) !important;
            }
            [class*="focus:border-[#5542ff]"]:focus {
                border-color: var(--gradient-from) !important;
            }

            /* ── Tailwind blue utility → gradient overrides ── */

            /* bg-blue-600 / bg-blue-700 buttons and table headers → gradient */
            main button.bg-blue-600,
            main a.bg-blue-600,
            main button.bg-blue-700,
            main a.bg-blue-700,
            main tr.bg-blue-600,
            main thead.bg-blue-600,
            main button.bg-blue-500,
            main a.bg-blue-500 {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%) !important;
                color: #ffffff !important;
            }

            /* hover:bg-blue-700 / hover:bg-blue-900 / hover:bg-blue-600 */
            main [class*="hover:bg-blue-700"]:hover,
            main [class*="hover:bg-blue-900"]:hover,
            main [class*="hover:bg-blue-600"]:hover {
                background: linear-gradient(135deg, var(--gradient-to) 0%, var(--gradient-from) 100%) !important;
                color: #ffffff !important;
            }
            main [class*="hover:bg-blue-700"]:hover *,
            main [class*="hover:bg-blue-900"]:hover *,
            main [class*="hover:bg-blue-600"]:hover * {
                color: #ffffff !important;
                stroke: #ffffff !important;
            }

            /* bg-[#0a1233] on buttons → gradient */
            main button[class*="bg-[#0a1233]"],
            main a[class*="bg-[#0a1233]"],
            main input[type="submit"][class*="bg-[#0a1233]"] {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%) !important;
            }

            /* border-blue-500 → gradient-from */
            main [class*="border-blue-500"] {
                border-color: var(--gradient-from) !important;
            }

            /* text-blue-600 on buttons/links → gradient-from */
            main button[class*="text-blue-600"],
            main a[class*="text-blue-600"] {
                color: var(--gradient-from) !important;
            }

            /* hover:bg-blue-50 on bordered buttons */
            main button[class*="hover:bg-blue-50"]:hover,
            main a[class*="hover:bg-blue-50"]:hover {
                background-color: color-mix(in srgb, var(--gradient-from) 8%, transparent) !important;
            }

            /* focus:ring-blue-500 → gradient-from */
            main [class*="focus:ring-blue-500"]:focus {
                --tw-ring-color: var(--gradient-from) !important;
            }

            /* bg-blue-50 conditional states (e.g. active filters) */
            main [class*="bg-blue-50"][class*="text-blue-700"] {
                background-color: color-mix(in srgb, var(--gradient-from) 10%, transparent) !important;
                color: var(--gradient-from) !important;
            }

            /* Notification badge header avatar fallback */
            .bg-blue-600:not(.dark *):not(main tr):not(main thead) {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%) !important;
            }

            /* bg-[#4634ff] table header rows → gradient */
            main tr[class*="bg-[#4634ff]"],
            main thead[class*="bg-[#4634ff]"] {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%) !important;
            }

            /* Non-gradient dark mode table headers override → use gradient instead of fixed #5542ff */
            .dark main thead tr:not(.gradient-bg) {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%) !important;
            }
            .dark main thead tr:not(.gradient-bg) th {
                background: transparent !important;
                color: #ffffff !important;
            }

            /* Search highlight active → gradient */
            main [class*="bg-[#5542ff]"][class*="text-white"]:not(button):not(a) {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%) !important;
            }

            /* Dark mode checkbox accent → gradient-from */
            .dark main input[type="checkbox"],
            .dark main input[type="radio"] {
                color: var(--gradient-from) !important;
            }
            .dark main input[type="checkbox"]:checked,
            .dark main input[type="radio"]:checked {
                background-color: var(--gradient-from) !important;
                border-color: var(--gradient-from) !important;
            }

            /* Global Dark Mode Overrides */

            /* ── Base surfaces ── */
            .dark main .bg-white:not(.no-dark):not(button):not(a),
            .dark main [class*="bg-gray-50"]:not(thead):not(thead *):not(input):not(select):not(textarea):not(span),
            .dark main .bg-gray-100:not(input):not(select):not(textarea):not(span),
            .dark main .bg-gray-200:not(span),
            .dark main div.bg-white:not(.no-dark):not(button):not(a),
            .dark main section.bg-white:not(.no-dark):not(button):not(a) {
                background-color: #1f2937 !important;
                color: #f3f4f6 !important;
                border-color: rgba(255,255,255,0.1) !important;
                box-shadow: none !important;
            }
            .dark .bg-white:not(.no-dark):not(button):not(a):not(main *) {
                background-color: #111827 !important;
                color: #f3f4f6 !important;
            }

            /* ── Typography ── */
            .dark main h1, .dark main h2, .dark main h3, .dark main h4, .dark main h5, .dark main h6,
            .dark main .text-gray-800, .dark main .text-gray-900,
            .dark main [class*="text-[#0a1233]"],
            .dark main .text-\[\#0a1233\], .dark main .text-\[\#1a234a\], .dark main .text-\[\#2a345a\] {
                color: #ffffff !important;
            }
            .dark main .text-gray-400, .dark main .text-gray-500,
            .dark main .text-gray-600, .dark main .text-gray-700 {
                color: #9ca3af !important;
            }
            .dark main .text-gray-300 {
                color: #d1d5db !important;
            }
            .dark main label, .dark main legend {
                color: #e5e7eb !important;
            }
            .dark main .placeholder\:text-gray-400::placeholder,
            .dark main input::placeholder, .dark main textarea::placeholder {
                color: #6b7280 !important;
            }

            /* ── Shadows ── */
            .dark main .shadow-sm, .dark main .shadow, .dark main .shadow-md,
            .dark main .shadow-lg, .dark main .shadow-xl {
                box-shadow: none !important;
            }

            /* ── Borders ── */
            .dark main .border, .dark main .border-gray-200,
            .dark main .border-gray-100, .dark main .border-gray-300 {
                border-color: rgba(255,255,255,0.1) !important;
            }
            .dark main .divide-y > :not([hidden]) ~ :not([hidden]) {
                border-color: #374151 !important;
            }

            /* ── Tables ── */
            .dark main tr {
                background-color: transparent !important;
            }
            .dark main tr:hover {
                background-color: rgba(55, 65, 81, 0.5) !important;
            }
            
            /* Gradient header rows - highest priority */
            .dark main tr.gradient-bg {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%) !important;
                color: #ffffff !important;
            }
            .dark main tr.gradient-bg th {
                color: #ffffff !important;
                background: transparent !important;
                border-color: rgba(255,255,255,0.1) !important;
            }
            .dark main thead th {
                color: #ffffff !important;
            }
            
            /* Non-gradient table headers */
            .dark main thead tr:not(.gradient-bg) {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%) !important;
            }
            .dark main thead tr:not(.gradient-bg) th {
                background: transparent !important;
                color: #ffffff !important;
            }
            /* Also handle dark-themed table headers (bg-[#0a1233]) */
            .dark main tr.bg-\[\#0a1233\], .dark main .bg-\[\#0a1233\] {
                background-color: #0a1233 !important;
                color: #ffffff !important;
            }

            /* ── Form inputs ── */
            .dark main input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="file"]),
            .dark main select,
            .dark main textarea {
                background-color: #111827 !important;
                color: #ffffff !important;
                border-color: #374151 !important;
            }
            .dark main select option {
                background-color: #1f2937 !important;
                color: #ffffff !important;
            }
            .dark main input[type="file"] {
                color: #d1d5db !important;
            }
            .dark main input[type="checkbox"], .dark main input[type="radio"] {
                background-color: #374151 !important;
                border-color: #4b5563 !important;
                color: var(--gradient-from) !important;
            }
            .dark main input[type="checkbox"]:checked, .dark main input[type="radio"]:checked {
                background-color: var(--gradient-from) !important;
                border-color: var(--gradient-from) !important;
            }
            /* Input addon/prefix spans (currency symbols, search icons, etc.) */
            .dark main span.bg-gray-100,
            .dark main span.bg-gray-50,
            .dark main div.overflow-hidden > span.bg-gray-100 {
                background-color: #1f2937 !important;
                color: #9ca3af !important;
                border-color: #374151 !important;
            }

            /* ── Buttons & links ── */
            .dark main a.bg-white.border, .dark main button.bg-white.border {
                background-color: transparent !important;
                color: #e5e7eb !important;
            }
            .dark main a.bg-white:not(.no-dark), .dark main button.bg-white:not(.no-dark) {
                background-color: rgba(255,255,255,0.05) !important;
                color: #e5e7eb !important;
            }

            /* ── Form containers (forms with bg-white or bg-gray-50/XX) ── */
            .dark main form.bg-white,
            .dark main form[class*="bg-gray-50"],
            .dark main .role-permissions-section,
            .dark main .sticky {
                background-color: #1f2937 !important;
                color: #ffffff !important;
                border-color: rgba(255,255,255,0.1) !important;
            }

            /* ── Search/autocomplete dropdowns (absolute-positioned bg-white panels) ── */
            .dark main .absolute.bg-white,
            .dark main .absolute [class*="bg-white"],
            .dark main div[class*="absolute"][class*="bg-white"],
            .dark main .z-50.bg-white,
            .dark main .z-50 [class*="bg-white"] {
                background-color: #1f2937 !important;
                color: #f3f4f6 !important;
                border-color: #374151 !important;
            }
            .dark main .absolute.bg-white button:hover,
            .dark main .absolute.bg-white a:hover,
            .dark main .absolute [class*="bg-white"] button:hover,
            .dark main .absolute [class*="bg-white"] a:hover,
            .dark main .z-50.bg-white button:hover,
            .dark main .z-50.bg-white a:hover,
            .dark main .z-50 [class*="bg-white"] button:hover,
            .dark main .z-50 [class*="bg-white"] a:hover {
                background-color: #374151 !important;
            }
            .dark main .hover\:bg-gray-50:hover {
                background-color: rgba(55, 65, 81, 0.5) !important;
            }

            /* ── Modal backdrop — keep semi-transparent ── */
            .dark .fixed.inset-0 {
                background-color: transparent !important;
                border: none !important;
            }
            .dark .fixed.inset-0 > .absolute.inset-0,
            .dark .fixed.inset-0 div.fixed.inset-0[class*="bg-gray"],
            .dark .fixed.inset-0 div.fixed.inset-0[class*="bg-black"] {
                background-color: rgba(0, 0, 0, 0.6) !important;
                border: none !important;
            }
            /* Modal content panel — dark surface */
            .dark .fixed.inset-0 > div.bg-white,
            .dark .fixed.inset-0 .bg-white.rounded-lg,
            .dark .fixed.inset-0 .bg-white.rounded-xl,
            .dark main .fixed.inset-0 > div.bg-white,
            .dark main .fixed.inset-0 .bg-white.rounded-lg,
            .dark main .fixed.inset-0 .bg-white.rounded-xl {
                background-color: #1f2937 !important;
                color: #f3f4f6 !important;
                border: 1px solid rgba(255,255,255,0.1) !important;
            }

            /* ── Status badges — preserve colored backgrounds ── */
            .dark main .bg-red-50, .dark main .text-red-600,
            .dark main .border-red-200, .dark main .text-red-500 {
                color: #ef4444 !important;
            }
            .dark main .bg-green-50, .dark main .text-green-600, .dark main .border-green-200 {
                color: #22c55e !important;
            }
            .dark main .bg-blue-50, .dark main .text-blue-600, .dark main .border-blue-200 {
                color: #3b82f6 !important;
            }
            .dark main .bg-amber-50, .dark main .text-amber-600, .dark main .border-amber-200 {
                color: #f59e0b !important;
            }
            .dark main .bg-orange-50, .dark main .text-orange-700, .dark main .border-orange-200 {
                color: #f97316 !important;
            }
            /* Keep colored badge backgrounds muted */
            .dark main .bg-emerald-100, .dark main .bg-green-100 {
                background-color: rgba(34,197,94,0.15) !important;
                color: #4ade80 !important;
            }
            .dark main .bg-red-100 {
                background-color: rgba(239,68,68,0.15) !important;
                color: #f87171 !important;
            }
            .dark main .bg-amber-100, .dark main .bg-yellow-100 {
                background-color: rgba(245,158,11,0.15) !important;
                color: #fbbf24 !important;
            }
            .dark main .bg-blue-100 {
                background-color: rgba(59,130,246,0.15) !important;
                color: #60a5fa !important;
            }
            .dark main .bg-orange-100, .dark main .text-orange-700, .dark main .border-orange-200 {
                background-color: rgba(241, 90, 34, 0.15) !important;
                color: #f97316 !important;
            }

            /* ── Pagination ── */
            .dark main .pagination, .dark main nav[role="navigation"] {
                background-color: transparent !important;
            }
            .dark main .pagination a, .dark main .pagination span,
            .dark main nav[role="navigation"] a, .dark main nav[role="navigation"] span {
                background-color: #1f2937 !important;
                border-color: #374151 !important;
                color: #f3f4f6 !important;
            }
            
            /* Salesperson-specific styles */
            .safe-area-top { 
                padding-top: max(0.5rem, env(safe-area-inset-top, 0.5rem)); 
            }
            .safe-area-bottom { 
                padding-bottom: max(0.5rem, env(safe-area-inset-bottom, 0.5rem)); 
            }
            .bottom-nav-shadow {
                box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            }
            .card-shadow {
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            }
            .pulse-btn {
                animation: pulse 2s infinite;
            }
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }
            .slide-up {
                animation: slideUp 0.3s ease-out;
            }
            @keyframes slideUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 min-h-screen safe-area-bottom">
        <!-- Top Header (Matches Admin Topbar Design) -->
        <header class="bg-white dark:bg-gray-900 sticky top-0 z-40 flex items-center justify-between px-4 sm:px-8 py-3 lg:h-16 lg:py-0 border-b border-gray-200 dark:border-transparent shadow-sm dark:shadow-none transition-colors duration-200 safe-area-top">
            <div class="flex items-center justify-between w-full max-w-7xl mx-auto">
                <!-- Left: Brand Logo & Title -->
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group min-w-0 transition-opacity hover:opacity-90">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0 bg-gray-50 dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
                        @if($generalSetting && $generalSetting->logo)
                            <img src="{{ asset($generalSetting->logo) }}" alt="{{ $generalSetting->site_title }}" class="w-7 h-7 object-contain">
                        @elseif($generalSetting && $generalSetting->logo_light)
                            <img src="{{ asset($generalSetting->logo_light) }}" alt="{{ $generalSetting->site_title }}" class="w-7 h-7 object-contain">
                        @else
                            <div class="w-7 h-7 rounded-lg gradient-bg flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0H7m9 0v-5m0 0h-3m-5 0v5m0 0v-5" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <span class="font-extrabold text-base sm:text-lg tracking-tight text-gray-900 dark:text-white truncate block max-w-[150px] sm:max-w-xs md:max-w-md leading-tight">
                            {{ $generalSetting->site_title ?? config('app.name', 'Laravel') }}
                        </span>
                        <span class="text-[10px] sm:text-[11px] font-semibold text-ink-muted tracking-wider uppercase hidden sm:block leading-none mt-0.5">Sales Portal</span>
                    </div>
                </a>
                
                <!-- Right: Action Icons & Profile (Matching Admin) -->
                <div class="flex items-center space-x-3 sm:space-x-5">
                    <!-- Theme Toggle -->
                    <button @click="toggleDarkMode()" 
                            class="p-1.5 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" 
                            title="Toggle Dark/Light Mode"
                            aria-label="Toggle theme">
                        <!-- Sun Icon (Shown in Dark Mode) -->
                        <svg x-show="darkMode" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 18v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707m12.728 12.728A9 9 0 115.636 5.636a9 9 0 0112.728 12.728z" />
                        </svg>
                        <!-- Moon Icon (Shown in Light Mode) -->
                        <svg x-show="!darkMode" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div class="relative" x-data="{ openNotifications: false }">
                        @php $unreadCount = \App\Models\SystemNotification::getUnreadCount(auth()->id()); @endphp
                        <button @click="openNotifications = !openNotifications" 
                                class="p-1.5 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 relative transition-colors"
                                title="Notifications"
                                aria-label="Notifications">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c.538-.214 1.055-.595 1.436-1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if($unreadCount > 0)
                                <span class="absolute top-0.5 right-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center ring-2 ring-white dark:ring-gray-900 shadow-sm">
                                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                </span>
                            @endif
                        </button>
                        
                        <!-- Notifications Dropdown Panel -->
                        <div x-show="openNotifications" 
                             x-cloak 
                             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-200"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                             @click.away="openNotifications = false"
                             class="fixed inset-x-3 top-16 sm:inset-x-auto sm:absolute sm:right-0 sm:top-full sm:mt-2 sm:w-96 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700/80 z-[100] flex flex-col overflow-hidden backdrop-blur-md"
                             style="max-height: min(520px, calc(100vh - 85px));"
                             x-data="{
                                 markAllRead() {
                                     fetch('{{ route('notifications.mark-all-as-read') }}', {
                                         method: 'POST',
                                         headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                                     }).then(() => window.location.reload());
                                 },
                                 deleteAll() {
                                     if (!confirm('Delete all notifications? This cannot be undone.')) return;
                                     fetch('{{ route('notifications.delete-all') }}', {
                                         method: 'POST',
                                         headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                                     }).then(() => window.location.reload());
                                 }
                             }">
                            <!-- Header -->
                            <div class="px-4 py-3.5 border-b border-gray-100 dark:border-gray-700/80 bg-gradient-to-r from-gray-50/80 to-white/80 dark:from-gray-800 dark:to-gray-800/90 backdrop-blur-sm">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-blue-600 dark:bg-blue-400"></div>
                                        <h3 class="font-bold text-ink-heading text-sm tracking-tight">
                                            Notifications
                                        </h3>
                                        @if($unreadCount > 0)
                                            <span class="px-2 py-0.5 text-[10px] font-extrabold bg-blue-100 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 rounded-full">
                                                {{ $unreadCount }} new
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1">
                                        @if($unreadCount > 0)
                                        <button @click.stop="markAllRead()"
                                                title="Mark all as read"
                                                class="flex items-center gap-1 text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 px-2.5 py-1 rounded-lg transition-all duration-150">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Mark read</span>
                                        </button>
                                        @endif
                                        <button @click.stop="deleteAll()"
                                                title="Delete all notifications"
                                                class="flex items-center gap-1 text-[11px] font-semibold text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 px-2 py-1 rounded-lg transition-all duration-150">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            @php $notifications = \App\Models\SystemNotification::getRecentNotifications(10, Auth::id()); @endphp
                            
                            @if($notifications->count() > 0)
                                <div class="overflow-y-auto flex-1 divide-y divide-gray-100 dark:divide-gray-700/60" style="max-height: 380px;">
                                    @foreach($notifications as $notification)
                                        @php
                                            $title_lower = strtolower($notification->title);
                                            $message_lower = strtolower($notification->message);
                                            $notificationLink = route('notifications.index');
                                            
                                            if (strpos($title_lower, 'sale') !== false || strpos($title_lower, 'approval') !== false) {
                                                $notificationLink = route('sales.all');
                                            } elseif (strpos($title_lower, 'stock') !== false || strpos($message_lower, 'stock') !== false || strpos($message_lower, 'low stock') !== false) {
                                                $notificationLink = route('products.all');
                                            }
                                        @endphp
                                        <a href="{{ $notificationLink }}" 
                                           class="group block p-3.5 transition-all duration-150 relative {{ $notification->is_read ? 'bg-white dark:bg-gray-800 hover:bg-gray-50/80 dark:hover:bg-gray-700/40 opacity-70 hover:opacity-100' : 'bg-orange-50/30 dark:bg-orange-950/10 hover:bg-orange-50/60 dark:hover:bg-orange-950/20' }}">
                                            @if(!$notification->is_read)
                                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-orange-500 to-amber-500"></div>
                                            @endif
                                            <div class="flex gap-3 items-start">
                                                <!-- Icon Indicator based on type/severity -->
                                                <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5 {{ $notification->severity === 'danger' ? 'bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-400' : ($notification->severity === 'warning' ? 'bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400' : 'bg-blue-100 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400') }}">
                                                    @if($notification->severity === 'danger')
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                        </svg>
                                                    @elseif($notification->severity === 'warning')
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between gap-1 mb-0.5">
                                                        <p class="font-semibold text-ink-heading text-xs leading-snug truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                                            {{ $notification->title }}
                                                        </p>
                                                        <span class="text-[10px] text-ink-muted whitespace-nowrap flex-shrink-0">
                                                            {{ $notification->created_at->diffForHumans(null, true, true) }}
                                                        </span>
                                                    </div>
                                                    <p class="text-gray-600 dark:text-gray-300 text-xs line-clamp-2 leading-relaxed">
                                                        {{ $notification->message }}
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-8 text-center bg-white dark:bg-gray-800">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-700/60 text-ink-muted flex items-center justify-center mx-auto mb-2.5">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c.538-.214 1.055-.595 1.436-1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                    </div>
                                    <p class="text-ink-body text-xs font-semibold">No notifications</p>
                                    <p class="text-ink-muted text-[11px] mt-0.5">You're all caught up!</p>
                                </div>
                            @endif
                            <!-- Footer -->
                            <a href="{{ route('notifications.index') }}" class="block bg-gray-50/80 dark:bg-gray-800/90 hover:bg-gray-100 dark:hover:bg-gray-700 text-center text-xs font-bold text-blue-600 dark:text-blue-400 py-3 border-t border-gray-100 dark:border-gray-700/70 transition-colors uppercase tracking-wider">
                                View all notifications
                            </a>
                        </div>
                    </div>
                    
                    <!-- Settings -->
                    <a href="{{ route('settings.index') }}" 
                       class="p-1.5 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" 
                       title="Settings" 
                       aria-label="Settings">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.065 2.572c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.572-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.065-2.572c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>
                    
                    <!-- Profile Dropdown (Matches Admin avatar pill & name style) -->
                    <div class="relative" x-data="{ openProfile: false }" @click.away="openProfile = false">
                        <button @click="openProfile = !openProfile" 
                                class="flex items-center text-sm font-medium text-gray-700 dark:text-white hover:text-gray-900 dark:hover:text-gray-300 focus:outline-none transition duration-150 ease-in-out p-1 -mr-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
                                aria-label="User menu">
                            <div class="flex items-center space-x-2.5">
                                <div class="h-8 w-8 rounded-full flex items-center justify-center overflow-hidden border-2 border-orange-400/30 shadow-sm transition-all text-white font-extrabold text-xs tracking-tighter"
                                     style="background: hsl({{ hexdec(substr(md5(Auth::user()->name), 0, 4)) % 360 }}, 65%, 45%) !important;">
                                    @if(Auth::user()->image)
                                        <img src="{{ asset(Auth::user()->image) }}" class="h-full w-full object-cover">
                                    @else
                                        {{ collect(explode(' ', Auth::user()->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') }}
                                    @endif
                                </div>
                                <div class="hidden sm:block text-left leading-none">
                                    <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white block truncate max-w-[120px]">{{ Auth::user()->name }}</span>
                                    <span class="text-[10px] text-ink-muted mt-0.5 block">{{ Auth::user()->role->name ?? 'Salesperson' }}</span>
                                </div>
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>
                        
                        <!-- Profile Dropdown Menu -->
                        <div x-show="openProfile" x-cloak 
                             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-200"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                             class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden z-50">
                            <div class="p-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                                <p class="font-bold text-ink-heading text-sm truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-ink-muted truncate mt-0.5">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2.5 text-sm text-ink-body hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2.5 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 border-t border-gray-100 dark:border-gray-700/60 transition-colors">
                                        <svg class="w-4 h-4 mr-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="px-4 py-6 pb-24">
            {{ $slot }}
        </main>

        <!-- Bottom Navigation -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 bottom-nav-shadow z-40 safe-area-bottom">
            <div class="flex items-center justify-around py-2">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-2 px-3 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'text-orange-500' : 'text-gray-500' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-xs mt-1 font-medium">Home</span>
                </a>
                
                <a href="{{ route('products.all') }}" class="flex flex-col items-center py-2 px-3 rounded-xl transition-all {{ request()->routeIs('products.*') ? 'text-orange-500' : 'text-gray-500' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span class="text-xs mt-1 font-medium">Products</span>
                </a>
                
                <a href="{{ route('sales.create') }}" class="relative -mt-6">
                    <div class="w-12 h-12 gradient-bg rounded-full flex items-center justify-center shadow-lg pulse-btn">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                </a>
                
                <a href="{{ route('customers.index') }}" class="flex flex-col items-center py-2 px-3 rounded-xl transition-all {{ request()->routeIs('customers.*') ? 'text-orange-500' : 'text-gray-500' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="text-xs mt-1 font-medium">Customers</span>
                </a>
                
                <a href="{{ route('sales.all') }}" class="flex flex-col items-center py-2 px-3 rounded-xl transition-all {{ request()->routeIs('sales.*') && !request()->routeIs('sales.create') ? 'text-orange-500' : 'text-gray-500' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span class="text-xs mt-1 font-medium">History</span>
                </a>
            </div>
        </nav>

        <!-- Toast Container -->
        <div x-data="{ show: false, message: '', type: 'success' }" 
             x-show="show"
             x-transition:enter="transform ease-out duration-300"
             x-transition:enter-start="translate-y-2 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transform ease-in duration-200"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="translate-y-2 opacity-0"
             class="fixed top-20 left-4 right-4 z-50"
             x-cloak>
            <div :class="{
                'bg-green-500 text-white': type === 'success',
                'bg-red-500 text-white': type === 'error',
                'bg-blue-500 text-white': type === 'info'
            }" class="rounded-xl px-4 py-3 shadow-lg">
                <p class="font-medium" x-text="message"></p>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>

