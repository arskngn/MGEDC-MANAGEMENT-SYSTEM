<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>"
      x-data="{ 
        darkMode: localStorage.getItem('dark_mode') === 'true' || (localStorage.getItem('dark_mode') === null && '<?php echo e($generalSetting->default_theme ?? 'light'); ?>' === 'dark'),
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
        }
      }"
      x-init="
        if (darkMode) {
            document.documentElement.classList.add('dark');
            document.documentElement.style.colorScheme = 'dark';
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.style.colorScheme = 'light';
        }
        document.documentElement.classList.remove('theme-loading', 'sidebar-loading');
      "
      :class="{ 'dark': darkMode }"
      data-user-authenticated="1">
    <head>
        <?php echo $__env->make('layouts.partials.theme-init', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e($generalSetting->site_title ?? config('app.name', 'Laravel')); ?></title>

        <!-- Favicon -->
        <?php if($generalSetting && $generalSetting->favicon): ?>
            <link rel="icon" type="image/x-icon" href="<?php echo e(asset($generalSetting->favicon)); ?>">
        <?php endif; ?>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Real-time WebSockets Config -->
        <script>
            window.__REVERB_CONFIG__ = {
                key:    <?php echo \Illuminate\Support\Js::from(config('broadcasting.connections.reverb.client.key') ?: config('broadcasting.connections.reverb.key') ?: 'lxh3qzvrykrfuhwp1nnf')->toHtml() ?>,
                host:   <?php echo \Illuminate\Support\Js::from(config('broadcasting.connections.reverb.client.host') ?: config('broadcasting.connections.reverb.options.host') ?: 'reverb-production-a38d.up.railway.app')->toHtml() ?>,
                port:   <?php echo \Illuminate\Support\Js::from(config('broadcasting.connections.reverb.client.port') ?: 443)->toHtml() ?>,
                scheme: <?php echo \Illuminate\Support\Js::from(config('broadcasting.connections.reverb.client.scheme') ?: 'https')->toHtml() ?>,
            };
        </script>

        <!-- Scripts -->
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <style>
            [x-cloak] { display: none !important; }

            /* Keep mobile sidebar off-screen only until Alpine is ready (do not override open state) */
            @media (max-width: 1023px) {
                body:not([data-alpine-mounted]) .sidebar-panel-preload {
                    transform: translateX(-100%);
                }
            }
            
            /* Tell browser to render native controls (select dropdowns, date pickers, scrollbars) in dark */
            html.dark {
                color-scheme: dark;
            }

            /* Global Dark Mode Overrides — surfaces, inks, strokes.
             * Uses the semantic tokens defined in resources/css/app.css so all
             * surfaces + text layers have a clear hierarchical contrast
             * (heading AAA, body AAA, muted AAA, subtle AA) in both themes. */

            /* ── Base surfaces ── */
            .dark main .bg-white:not(.no-dark):not(button):not(a),
            .dark main [class*="bg-gray-50"]:not(thead):not(thead *):not(input):not(select):not(textarea):not(span),
            .dark main .bg-gray-100:not(input):not(select):not(textarea):not(span),
            .dark main .bg-gray-200:not(span),
            .dark main div.bg-white:not(.no-dark):not(button):not(a),
            .dark main section.bg-white:not(.no-dark):not(button):not(a) {
                background-color: var(--surface-panel) !important;
                color: var(--ink-body) !important;
                border-color: var(--stroke-body) !important;
                box-shadow: none !important;
            }
            .dark .bg-white:not(.no-dark):not(button):not(a):not(main *) {
                background-color: var(--surface-body) !important;
                color: var(--ink-body) !important;
            }

            /* ── Typography — 4 distinct ink layers, each with verified WCAG contrast:
             *   heading (16.72:1), body (8.24:1), muted (6.94:1), subtle (4.31:1).
             *   The old rule collapsed gray-400…700 → a SINGLE #9ca3af, which
             *   erased hierarchy AND made muted body text fail AA (4.21:1).     */
            .dark main h1, .dark main h2, .dark main h3, .dark main h4, .dark main h5, .dark main h6,
            .dark main .text-gray-800, .dark main .text-gray-900,
            .dark main [class*="text-[#0a1233]"],
            .dark main .text-\[\#0a1233\], .dark main .text-\[\#1a234a\], .dark main .text-\[\#2a345a\] {
                color: var(--ink-heading) !important;
            }
            /* Body / "primary" text — gray-700 (light) maps to ink-body (dark). */
            .dark main .text-gray-700,
            .dark main .text-gray-800,
            .dark main .text-gray-900 {
                color: var(--ink-body) !important;
            }
            /* Muted helper text — gray-500/600 (light) → ink-muted (dark, AAA 6.94:1) */
            .dark main .text-gray-500,
            .dark main .text-gray-600 {
                color: var(--ink-muted) !important;
            }
            /* Subtle decorative only — gray-400 (light) → ink-subtle (dark, AA 4.31:1).
             * Keep this for XS icon text / timestamps, NOT for body content. */
            .dark main .text-gray-400 {
                color: var(--ink-subtle) !important;
            }
            .dark main .text-gray-300 {
                color: var(--ink-muted) !important;
            }
            .dark main label, .dark main legend {
                color: var(--ink-body) !important;
            }
            .dark main .placeholder\:text-gray-400::placeholder,
            .dark main input::placeholder, .dark main textarea::placeholder {
                color: var(--ink-placeholder) !important;
            }

            /* ── Shadows ── */
            .dark main .shadow-sm, .dark main .shadow, .dark main .shadow-md,
            .dark main .shadow-lg, .dark main .shadow-xl {
                box-shadow: none !important;
            }

            /* ── Borders & dividers ── */
            .dark main .border, .dark main .border-gray-200,
            .dark main .border-gray-100, .dark main .border-gray-300 {
                border-color: var(--stroke-body) !important;
            }
            .dark main .divide-y > :not([hidden]) ~ :not([hidden]) {
                border-color: var(--stroke-subtle) !important;
            }

            /* ── Tables ── */
            .dark main tr {
                background-color: transparent !important;
            }
            .dark main tr:hover {
                background-color: var(--surface-hover) !important;
            }

            /* Dark-mode text/link colors inside data rows (tbody, cards, grid tiles).
             *
             * PROBLEM FIXED here (sales table, customers table, etc.):
             * Authors frequently sprinkle the raw purple `text-[#5542ff]` for links
             * (customer names, invoice numbers, "View" links) without a `dark:` variant,
             * which renders as deep purple on dark panels (~2.12:1 contrast — fails AA).
             *
             * Override strategy: bump every `[*]:text-[#5542ff]` inside dark main rows +
             * all inline anchor tags NOT on a button-style background to indigo-300
             * (#A5B4FC — 7.9:1 on #111827, WCAG AAA) — bright enough while still being
             * clearly a link color vs the surrounding white body ink. */
            .dark main td[class*="text-[#5542ff]"],
            .dark main div[class*="text-[#5542ff]"],
            .dark main span[class*="text-[#5542ff]"],
            .dark main p[class*="text-[#5542ff]"],
            .dark main li[class*="text-[#5542ff]"],
            .dark main a:not([class*="bg-"]):not([class*="text-white"]):not([class*="text-gray-100"]):not(.inline-flex.border):not(.gradient-bg) {
                color: #A5B4FC !important; /* indigo-300 — WCAG AAA on #111827 */
            }
            /* Dim helper "indigo-400" dark fallback often used alongside links
             * — upgrade to same AAA indigo-300 for visual consistency. */
            .dark main [class*="dark:text-indigo-400"],
            .dark main [class*="dark:text-indigo-300"] {
                color: #A5B4FC !important;
            }

            /* Gradient header rows - highest priority.
             * Text must be pure #FFFFFF regardless of light/dark because the gradient
             * uses the user's saturated accent colors (blue/green/orange/purple) —
             * the previous --ink-inverse in dark mode resolved to #0B1220 (near-black)
             * which completely vanished on dark gradients. */
            .dark main tr.gradient-bg,
            main tr.gradient-bg {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%) !important;
                color: #FFFFFF !important;
            }
            .dark main tr.gradient-bg th,
            main tr.gradient-bg th {
                color: #FFFFFF !important;
                text-shadow: 0 1px 1px rgba(0, 0, 0, 0.18) !important;
                background: transparent !important;
                border-color: rgba(255, 255, 255, 0.14) !important;
            }
            .dark main thead th,
            main thead th {
                color: #FFFFFF !important;
            }

            /* Non-gradient table headers — also render as accent gradient. */
            .dark main thead tr:not(.gradient-bg),
            main thead tr:not(.gradient-bg) {
                background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%) !important;
            }
            .dark main thead tr:not(.gradient-bg) th,
            main thead tr:not(.gradient-bg) th {
                background: transparent !important;
                color: #FFFFFF !important;
                text-shadow: 0 1px 1px rgba(0, 0, 0, 0.18) !important;
            }


            /* ── Form inputs ── */
            .dark main input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="file"]),
            .dark main select,
            .dark main textarea {
                background-color: var(--surface-panel) !important;
                color: var(--ink-heading) !important;
                border-color: var(--stroke-strong) !important;
            }
            .dark main select option {
                background-color: var(--surface-inset) !important;
                color: var(--ink-heading) !important;
            }
            .dark main input[type="file"] {
                color: var(--ink-muted) !important;
            }
            .dark main input[type="checkbox"], .dark main input[type="radio"] {
                background-color: var(--surface-inset) !important;
                border-color: var(--stroke-strong) !important;
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
                background-color: var(--surface-inset) !important;
                color: var(--ink-muted) !important;
                border-color: var(--stroke-strong) !important;
            }

            /* ── Buttons & links ── */
            .dark main a.bg-white.border, .dark main button.bg-white.border {
                background-color: transparent !important;
                color: var(--ink-body) !important;
            }
            .dark main a.bg-white:not(.no-dark), .dark main button.bg-white:not(.no-dark) {
                background-color: rgba(255,255,255,0.05) !important;
                color: var(--ink-body) !important;
            }

            /* ── Form containers (forms with bg-white or bg-gray-50/XX) ── */
            .dark main form.bg-white,
            .dark main form[class*="bg-gray-50"],
            .dark main .role-permissions-section,
            .dark main .sticky {
                background-color: var(--surface-inset) !important;
                color: var(--ink-heading) !important;
                border-color: var(--stroke-body) !important;
            }

            /* ── Search/autocomplete dropdowns (absolute-positioned bg-white panels) ── */
            .dark main .absolute.bg-white,
            .dark main .absolute [class*="bg-white"],
            .dark main div[class*="absolute"][class*="bg-white"],
            .dark main .z-50.bg-white,
            .dark main .z-50 [class*="bg-white"] {
                background-color: var(--surface-elevated) !important;
                color: var(--ink-body) !important;
                border-color: var(--stroke-strong) !important;
            }
            .dark main .absolute.bg-white button:hover,
            .dark main .absolute.bg-white a:hover,
            .dark main .absolute [class*="bg-white"] button:hover,
            .dark main .absolute [class*="bg-white"] a:hover,
            .dark main .z-50.bg-white button:hover,
            .dark main .z-50.bg-white a:hover,
            .dark main .z-50 [class*="bg-white"] button:hover,
            .dark main .z-50 [class*="bg-white"] a:hover {
                background-color: var(--surface-hover) !important;
            }
            .dark main .hover\:bg-gray-50:hover {
                background-color: var(--surface-hover) !important;
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
                background-color: var(--surface-elevated) !important;
                color: var(--ink-body) !important;
                border: 1px solid var(--stroke-body) !important;
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
                background-color: var(--surface-inset) !important;
                border-color: var(--stroke-strong) !important;
                color: var(--ink-body) !important;
            }

            /* ── Gradient Utilities from Appearance Settings ── */
            <?php
                $appearanceService = app(\App\Services\AppearanceSettingService::class);
                $appearance = $appearanceService->forSystem();
                $gradientFrom = $appearance['colors'][0] ?? '#F15A22';
                $gradientTo = $appearance['colors'][1] ?? '#0A0E27';
                $isSolidColorApp = (strtolower(trim($gradientFrom)) === strtolower(trim($gradientTo)));
            ?>
            
            :root {
                --gradient-from: <?php echo e($gradientFrom); ?>;
                --gradient-to: <?php echo e($gradientTo); ?>;
                --accent-dark: color-mix(in srgb, <?php echo e($gradientFrom); ?> 65%, #000000);
            }

            <?php if($isSolidColorApp): ?>
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
            <?php endif; ?>
            
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

            /* ── Solid blue → gradient for buttons, headers, accents ── */

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
        </style>
    </head>
    <body class="font-sans antialiased bg-[#f4f7f6] dark:bg-gray-900 transition-colors duration-200 overflow-hidden" 
          x-data="{ 
            sidebarOpen: false, 
            sidebarCollapsed: (() => { 
              const isSmallScreen = window.innerWidth < 1024; 
              return isSmallScreen ? false : (localStorage.getItem('sidebar_collapsed') === 'true'); 
            })()
          }"
          x-init="
            sidebarOpen = false;
            document.body.setAttribute('data-alpine-mounted', '');
            document.documentElement.classList.remove('theme-loading', 'sidebar-loading');
            if (sidebarCollapsed) {
                document.documentElement.classList.add('sidebar-collapsed');
                document.documentElement.classList.remove('sidebar-expanded');
            } else {
                document.documentElement.classList.add('sidebar-expanded');
                document.documentElement.classList.remove('sidebar-collapsed');
            }
            $watch('sidebarCollapsed', value => {
                localStorage.setItem('sidebar_collapsed', value);
                if (value) {
                    document.documentElement.classList.add('sidebar-collapsed');
                    document.documentElement.classList.remove('sidebar-expanded');
                } else {
                    document.documentElement.classList.add('sidebar-expanded');
                    document.documentElement.classList.remove('sidebar-collapsed');
                }
            });
          "
          @resize.window="sidebarCollapsed = (window.innerWidth < 1024) ? false : sidebarCollapsed">
        <div class="h-screen flex overflow-hidden">
            <?php echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <!-- Page Content -->
            <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden group/content transition-all duration-300"
                 :class="sidebarCollapsed ? 'lg:pl-0' : 'lg:pl-0'">
                <style>
                    .content-scrollbar::-webkit-scrollbar {
                        width: 6px;
                    }
                    .content-scrollbar::-webkit-scrollbar-track {
                        background: transparent;
                    }
                    .content-scrollbar::-webkit-scrollbar-thumb {
                        background: transparent;
                        border-radius: 10px;
                        transition: background 0.3s ease;
                    }
                    .group\/content:hover .content-scrollbar::-webkit-scrollbar-thumb {
                        background: #cbd5e1;
                    }
                    .content-scrollbar::-webkit-scrollbar-thumb:hover {
                        background: #94a3b8 !important;
                    }
                    /* For Firefox */
                    .content-scrollbar {
                        scrollbar-width: none;
                    }
                    .group\/content:hover .content-scrollbar {
                        scrollbar-width: thin;
                        scrollbar-color: #cbd5e1 transparent;
                    }
                </style>
                <!-- Top Header -->
                <header class="bg-white dark:bg-gray-900 flex-none flex flex-col lg:flex-row lg:h-16 lg:items-center justify-between px-4 sm:px-8 py-4 lg:py-0 border-b border-gray-200 dark:border-transparent shadow-sm dark:shadow-none z-20">
                    <!-- Top Row: Hamburger & Search -->
                    <div class="flex items-center space-x-4 flex-1 w-full lg:w-auto">
                        <!-- Mobile Hamburger -->
                        <button @click="sidebarOpen = true" class="lg:hidden p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline-none transition-colors">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div class="flex-1 lg:max-w-xs relative" x-data="{ 
                            searchQuery: '', 
                            searchResults: [],
                            searchOpen: false,
                            searchReady: false,
                            selectedIndex: -1,
                            allPages: [
                                { group: 'Dashboard', name: 'Dashboard', route: '<?php echo e(route('dashboard')); ?>', perm: 'Dashboard', icon: '<path d=\'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6\' />' },
                                { group: 'Manage Sales', name: 'All Sales', route: '<?php echo e(route('sales.all')); ?>', perm: 'All Sales', icon: '<path d=\'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z\' />' },
                                { group: 'Manage Sales', name: 'Sales Return', route: '<?php echo e(route('sales.return')); ?>', perm: 'All Sales Return', icon: '<path d=\'M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z\' />' },
                                { group: 'Manage Purchases', name: 'All Purchases', route: '<?php echo e(route('purchases.all')); ?>', perm: 'All Purchases', icon: '<path d=\'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z\' />' },
                                { group: 'Manage Purchases', name: 'Add Purchase', route: '<?php echo e(route('purchases.create')); ?>', perm: 'New Purchase', icon: '<path d=\'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z\' />' },
                                { group: 'Manage Purchases', name: 'Purchases Return', route: '<?php echo e(route('purchases.return')); ?>', perm: 'All Purchase Return', icon: '<path d=\'M10 19l-7-7m0 0l7-7m-7 7h18\' />' },
                                { group: 'Manage Products', name: 'Products', route: '<?php echo e(route('products.all')); ?>', perm: 'All Product', icon: '<path d=\'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4\' />' },
                                { group: 'Manage Products', name: 'Categories', route: '<?php echo e(route('categories.index')); ?>', perm: 'All Categorys', icon: '<path d=\'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z\' />' },
                                { group: 'Manage Products', name: 'Brands', route: '<?php echo e(route('brands.index')); ?>', perm: 'All Brands', icon: '<path d=\'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z\' />' },
                                { group: 'Manage Products', name: 'Units', route: '<?php echo e(route('units.index')); ?>', perm: 'All Unit', icon: '<path d=\'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10\' />' },

                                { group: 'Manage Customers', name: 'All Customers', route: '<?php echo e(route('customers.index')); ?>', perm: 'All Customers', icon: '<path d=\'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z\' />' },
                                { group: 'Manage Suppliers', name: 'All Suppliers', route: '<?php echo e(route('suppliers.index')); ?>', perm: 'All Suppliers', icon: '<path d=\'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z\' /><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0\' />' },
                                { group: 'Manage Staff', name: 'All Staff', route: '<?php echo e(route('staff.index')); ?>', perm: 'All Staffs', icon: '<path d=\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\' />' },
                                { group: 'Manage Staff', name: 'Roles', route: '<?php echo e(route('staff.roles.index')); ?>', perm: 'Roles Index', icon: '<path d=\'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z\' />' },
                                { group: 'Adjustments', name: 'All Adjustments', route: '<?php echo e(route('adjustments.index')); ?>', perm: 'All Adjustments', icon: '<path d=\'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3\' />' },

                                { group: 'Manage Expenses', name: 'Expense Types', route: '<?php echo e(route('expense_types.index')); ?>', perm: 'All Expense Types', icon: '<path d=\'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z\' />' },
                                { group: 'Manage Expenses', name: 'Expenses', route: '<?php echo e(route('expenses.index')); ?>', perm: 'All Expenses', icon: '<path d=\'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z\' />' },
                                { group: 'Report', name: 'Generated Reports', route: '<?php echo e(route('reports.generated')); ?>', perm: null, icon: '<path d=\'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\' />' },
                                { group: 'Payment Report', name: 'Supplier Payments', route: '<?php echo e(route('reports.payments.supplier')); ?>', perm: 'Supplier Payment Report', icon: '<path d=\'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\' />' },
                                { group: 'Payment Report', name: 'Customer Payments', route: '<?php echo e(route('reports.payments.customer')); ?>', perm: 'Customer Payment Report', icon: '<path d=\'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\' />' },
                                { group: 'Stock Report', name: 'Stock Report', route: '<?php echo e(route('reports.stock')); ?>', perm: 'Stock Report', icon: '<path d=\'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z\' />' },
                                { group: 'System Setting', name: 'General Setting', route: '<?php echo e(route('settings.general')); ?>', perm: 'Setting Index', icon: '<path d=\'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z\' /><path d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\' />' },
                                { group: 'System Setting', name: 'Logo and Favicon', route: '<?php echo e(route('settings.logo-favicon')); ?>', perm: 'Setting Logo Icon', icon: '<path d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\' />' },
                                { group: 'System Setting', name: 'System Configuration', route: '<?php echo e(route('settings.configuration')); ?>', perm: 'Setting System Configuration', icon: '<path d=\'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z\' />' },
                                { group: 'System Setting', name: 'Notification Setting', route: '<?php echo e(route('settings.notification')); ?>', perm: 'Email Notification Setting', icon: '<path d=\'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9\' />' },
                                { group: 'Extra', name: 'Application', route: '<?php echo e(route('extra.application')); ?>', perm: 'System Info', icon: '<path d=\'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z\' />' },
                                { group: 'Extra', name: 'Server', route: '<?php echo e(route('extra.server')); ?>', perm: 'System Server Info', icon: '<path d=\'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01\' />' },
                                { group: 'Extra', name: 'Cache', route: '<?php echo e(route('extra.cache')); ?>', perm: 'System Optimize', icon: '<path d=\'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4\' />' }
                            ],
                            initSearch() {
                                this.searchOpen = false;
                                this.searchResults = [];
                                this.selectedIndex = -1;
                                this.$nextTick(() => {
                                    this.searchReady = true;
                                });
                            },
                            performSearch() {
                                if (!this.searchReady) {
                                    return;
                                }

                                const userPermissions = <?php echo \Illuminate\Support\Js::from(auth()->user()->role ? auth()->user()->role->permissions->pluck('name')->unique() : collect())->toHtml() ?>;
                                const isAdmin = <?php echo \Illuminate\Support\Js::from(auth()->user()->hasRole('admin'))->toHtml() ?>;
                                const query = this.searchQuery.trim().toLowerCase();
                                
                                let filtered = this.allPages.filter(page => {
                                    return isAdmin || userPermissions.includes(page.perm);
                                }).sort((a, b) => a.name.localeCompare(b.name));

                                if (query.length < 1) {
                                    this.searchResults = filtered;
                                } else {
                                    this.searchResults = filtered.filter(page => {
                                        return page.name.toLowerCase().includes(query) || page.group.toLowerCase().includes(query);
                                    });
                                }
                                
                                this.searchOpen = this.searchResults.length > 0;
                                this.selectedIndex = this.searchResults.length > 0 ? 0 : -1;
                            },
                            nextResult() {
                                 if (this.searchResults.length > 0 && this.selectedIndex < this.searchResults.length - 1) {
                                     this.selectedIndex++;
                                     this.scrollToSelected();
                                 }
                             },
                             previousResult() {
                                 if (this.searchResults.length > 0 && this.selectedIndex > 0) {
                                     this.selectedIndex--;
                                     this.scrollToSelected();
                                 }
                             },
                            selectResult() {
                                if (this.selectedIndex >= 0 && this.searchResults[this.selectedIndex]) {
                                    window.location.href = this.searchResults[this.selectedIndex].route;
                                }
                            },
                            scrollToSelected() {
                                this.$nextTick(() => {
                                    const el = this.$refs.resultsList.children[this.selectedIndex];
                                    if (el) {
                                        el.scrollIntoView({ block: 'nearest' });
                                    }
                                });
                            }
                        }" x-init="initSearch()">
                            <div class="relative group" 
                                 @keydown.window.ctrl.k.prevent="$refs.searchInput.focus(); performSearch()"
                                 @keydown.window.cmd.k.prevent="$refs.searchInput.focus(); performSearch()">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                    <svg class="h-4 w-4 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                                <input x-model="searchQuery" 
                                    x-ref="searchInput"
                                    @input.debounce.200ms="performSearch()" 
                                    @focus="if ($event.isTrusted) performSearch()"
                                    @click.away="searchOpen = false"
                                    @keydown.escape="searchOpen = false"
                                    @keydown.down.prevent="nextResult()"
                                    @keydown.up.prevent="previousResult()"
                                    @keydown.enter.prevent="selectResult()"
                                    class="block w-full pl-9 pr-3 lg:pr-20 py-2 border border-gray-200 dark:border-gray-700 rounded-lg leading-5 bg-gray-50 dark:bg-gray-800 text-ink-body placeholder-gray-500 focus:outline-none focus:bg-white dark:focus:bg-gray-700 focus:ring-1 focus:ring-blue-500 text-sm transition-all duration-200" placeholder="Search here..." type="search">
                                
                                <div class="absolute inset-y-0 right-0 pr-3 hidden lg:flex items-center pointer-events-none group-focus-within:opacity-0 transition-opacity duration-200">
                                    <div class="flex items-center gap-1">
                                        <kbd class="px-1.5 py-0.5 text-[10px] font-semibold text-ink-muted bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md">Ctrl</kbd>
                                        <span class="text-ink-muted text-[10px]">+</span>
                                        <kbd class="px-1.5 py-0.5 text-[10px] font-semibold text-ink-muted bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md">K</kbd>
                                    </div>
                                </div>
                            </div>

                            <!-- Search Results Dropdown -->
                            <div x-cloak x-show="searchOpen && searchResults.length > 0" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="absolute mt-2 w-full bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden z-[100] flex flex-col">
                                
                                <div class="overflow-y-auto max-h-[320px] custom-scrollbar p-2 space-y-1" x-ref="resultsList">
                                    <template x-for="(result, index) in searchResults" :key="result.route">
                                        <a :href="result.route" 
                                           class="flex items-center gap-4 px-4 py-3 rounded-lg transition-all duration-150"
                                           :class="selectedIndex === index ? 'bg-[#5542ff] text-white' : 'hover:bg-gray-50 dark:hover:bg-gray-700 text-ink-body'"
                                           @mouseenter="selectedIndex = index">
                                            <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-xl transition-colors"
                                                 :class="selectedIndex === index ? 'bg-white/20' : 'bg-gray-100 dark:bg-gray-700 text-[#5542ff] dark:text-blue-400'">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-html="result.icon"></svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-[14px] font-bold truncate" x-text="result.name"></div>
                                                <div class="text-[11px] uppercase tracking-wider font-semibold opacity-70 truncate" x-text="result.group"></div>
                                            </div>
                                            <div x-show="selectedIndex === index" class="flex-shrink-0">
                                                <svg class="w-4 h-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </div>
                                        </a>
                                    </template>
                                </div>

                                <!-- Dropdown Footer (desktop only — keyboard hints) -->
                                <div class="hidden lg:flex items-center gap-6 px-4 py-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 text-[11px] text-gray-500 font-medium">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
                                        <span>to select</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="flex items-center gap-1">
                                            <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-sm text-gray-400">↑</kbd>
                                            <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-sm text-gray-400">↓</kbd>
                                        </div>
                                        <span>to navigate</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-sm text-gray-400">esc</kbd>
                                        <span>to close</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Row (Mobile) / Right Side (Desktop): Icons & Profile -->
                    <div class="flex items-center justify-between lg:justify-end space-x-6 mt-4 lg:mt-0 w-full lg:w-auto">
                        <!-- Left Group: Notifications & Settings -->
                        <div class="flex items-center space-x-6">
                            <!-- Theme Toggle -->
                            <button @click="toggleDarkMode()" class="p-1 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline-none transition-colors" title="Toggle Dark/Light Mode">
                                <!-- Sun Icon (Shown in Dark Mode) -->
                                <svg x-show="darkMode" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 18v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707m12.728 12.728A9 9 0 115.636 5.636a9 9 0 0112.728 12.728z" />
                                </svg>
                                <!-- Moon Icon (Shown in Light Mode) -->
                                <svg x-show="!darkMode" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                            </button>

                            <!-- Notifications -->
                            <?php if (isset($component)) { $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown','data' => ['align' => 'right','width' => 'w-80 sm:w-96','closeOnClick' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'right','width' => 'w-80 sm:w-96','closeOnClick' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
                                 <?php $__env->slot('trigger', null, []); ?> 
                                    <div class="relative">
                                        <?php $unreadCount = \App\Models\SystemNotification::getUnreadCount(auth()->id()); ?>
                                        <button class="p-1 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline-none transition-colors">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                            </svg>
                                        </button>
                                        <?php if($unreadCount > 0): ?>
                                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center" data-notification-badge>
                                                <?php echo e($unreadCount > 99 ? '99+' : $unreadCount); ?>

                                            </span>
                                        <?php endif; ?>
                                    </div>
                                 <?php $__env->endSlot(); ?>

                                 <?php $__env->slot('content', null, []); ?> 
                                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700/80 overflow-hidden w-full flex flex-col backdrop-blur-md" style="max-height: min(520px, calc(100vh - 85px));" @click.stop>
                                        <div class="px-4 py-3.5 border-b border-gray-100 dark:border-gray-700/80 flex justify-between items-center bg-gradient-to-r from-gray-50/80 to-white/80 dark:from-gray-800 dark:to-gray-800/90 backdrop-blur-sm">
                                            <div class="flex items-center gap-2">
                                                <div class="w-2 h-2 rounded-full bg-blue-600 dark:bg-blue-400"></div>
                                                <h3 class="font-bold text-ink-heading text-sm tracking-tight">Notifications</h3>
                                                <?php if($unreadCount > 0): ?>
                                                    <span class="px-2 py-0.5 text-[10px] font-extrabold bg-blue-100 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 rounded-full">
                                                        <?php echo e($unreadCount); ?> new
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="relative" x-data="{ openBulk: false }">
                                                <button @click.stop="openBulk = !openBulk" @click.away="openBulk = false" class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full transition-colors">
                                                    <svg class="w-5 h-5 text-ink-muted" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                                    </svg>
                                                </button>
                                                <div x-show="openBulk" x-cloak class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg z-[200] py-1" @click.stop>
                                                    <button onclick="markAllNotificationsAsRead(this)" class="w-full text-left px-4 py-2 text-sm text-ink-body hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center">
                                                        <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        Mark all as read
                                                    </button>
                                                    <button onclick="deleteAllNotifications(this)" class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        Delete all
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php $notifications = \App\Models\SystemNotification::getRecentNotifications(15, Auth::id()); ?>
                                        
                                        <?php if($notifications->count() > 0): ?>
                                            <div class="overflow-y-auto flex-1 min-h-[100px] bg-white dark:bg-gray-800" id="notification-list">
                                                <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $title_lower = strtolower($notification->title);
                                                        $message_lower = strtolower($notification->message);
                                                        $notificationLink = '/notifications';
                                                        
                                                        if (strpos($title_lower, 'sale') !== false || strpos($title_lower, 'approval') !== false) {
                                                            $notificationLink = '/sales/all';
                                                        } elseif (strpos($title_lower, 'stock') !== false || strpos($message_lower, 'stock') !== false || strpos($message_lower, 'low stock') !== false) {
                                                            $notificationLink = '/products/all';
                                                        }
                                                    ?>
                                                    <div class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors flex items-start gap-2 <?php echo e($notification->is_read ? 'bg-white dark:bg-gray-800' : ($notification->severity === 'danger' ? 'bg-red-50/50 dark:bg-red-900/10' : ($notification->severity === 'warning' ? 'bg-amber-50/50 dark:bg-amber-900/10' : 'bg-blue-50/50 dark:bg-blue-900/10'))); ?>" data-notification-id="<?php echo e($notification->id); ?>" data-is-read="<?php echo e($notification->is_read ? 'true' : 'false'); ?>">
                                                        
                                                        <!-- Clickable content area -->
                                                        <div class="flex-1 p-3 cursor-pointer hover:opacity-75 transition-opacity" onclick="notificationClickHandler('<?php echo e($notification->id); ?>', '<?php echo e($notificationLink); ?>', event)">
                                                            <div class="flex gap-3">
                                                                <div class="flex-shrink-0 pt-1">
                                                                    <?php if(!$notification->is_read): ?>
                                                                        <div class="w-2 h-2 bg-blue-600 rounded-full" data-unread-indicator></div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <p class="font-bold text-ink-heading text-sm truncate <?php echo e($notification->is_read ? 'opacity-60' : ''); ?>"><?php echo e($notification->title); ?></p>
                                                                    <p class="text-ink-muted text-xs line-clamp-2 mt-0.5 <?php echo e($notification->is_read ? 'opacity-60' : ''); ?>"><?php echo e($notification->message); ?></p>
                                                                    <p class="text-ink-muted text-[10px] mt-1 flex items-center">
                                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                        <?php echo e($notification->created_at->diffForHumans()); ?>

                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Menu button -->
                                                        <div class="flex-shrink-0 p-3 relative" onclick="event.stopPropagation();">
                                                            <button onclick="toggleNotificationMenu('menu-<?php echo e($notification->id); ?>', 'btn-<?php echo e($notification->id); ?>', event)" class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full transition-colors">
                                                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                                                </svg>
                                                            </button>
                                                            <div id="menu-<?php echo e($notification->id); ?>" class="hidden absolute right-0 mt-1 w-40 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-xl z-[210] py-1">
                                                                <button onclick="markAsRead('<?php echo e($notification->id); ?>', this)" 
                                                                        class="w-full text-left px-3 py-2 text-xs text-ink-body hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center <?php echo e($notification->is_read ? 'hidden' : ''); ?>"
                                                                        data-mark-read-btn>
                                                                    <svg class="w-3.5 h-3.5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                                    Mark as read
                                                                </button>
                                                                <button onclick="deleteNotification('<?php echo e($notification->id); ?>', this)" class="w-full text-left px-3 py-2 text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center">
                                                                    <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                    Delete
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="p-12 text-center bg-white dark:bg-gray-800" id="empty-notification-state">
                                                <div class="text-gray-200 dark:text-gray-700 mb-3">
                                                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0l-8 4-8-4m8 4v6" />
                                                    </svg>
                                                </div>
                                                <p class="text-ink-muted text-sm font-medium">Inbox is empty</p>
                                            </div>
                                        <?php endif; ?>
                                        <a href="<?php echo e(route('notifications.index')); ?>" class="block bg-gray-50 dark:bg-gray-800/80 hover:bg-gray-100 dark:hover:bg-gray-700 text-center text-xs font-bold text-blue-600 dark:text-blue-400 py-3 border-t border-gray-100 dark:border-gray-700 transition-colors uppercase tracking-widest">
                                            View All
                                        </a>
                                    </div>
                                 <?php $__env->endSlot(); ?>
                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $attributes = $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $component = $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>

                            <!-- Settings -->
                            <?php if(auth()->user()->hasPermission('Setting Index')): ?>
                                <a href="<?php echo e(route('settings.index')); ?>" class="p-1 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline-none transition-colors">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Right Group: Profile Dropdown -->
                        <?php if (isset($component)) { $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown','data' => ['align' => 'right','width' => '48']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'right','width' => '48']); ?>
                             <?php $__env->slot('trigger', null, []); ?> 
                                <button class="flex items-center text-sm font-medium text-gray-700 dark:text-white hover:text-gray-900 dark:hover:text-gray-300 focus:outline-none transition duration-150 ease-in-out">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-8 w-8 rounded-full flex items-center justify-center overflow-hidden border-2 border-blue-400/30 shadow-sm transition-all"
                                             style="background: hsl(<?php echo e(hexdec(substr(md5(Auth::user()->name), 0, 4)) % 360); ?>, 65%, 45%) !important;">
                                            <?php if(Auth::user()->image): ?>
                                                <img src="<?php echo e(asset(Auth::user()->image)); ?>" class="h-full w-full object-cover">
                                            <?php else: ?>
                                                <span class="text-xs font-extrabold text-white tracking-tighter">
                                                    <?php echo e(collect(explode(' ', Auth::user()->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('')); ?>

                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="hidden sm:block">
                                            <div class="flex flex-col items-start leading-none text-left">
                                                <span class="text-sm font-bold text-gray-900 dark:text-white"><?php echo e(Auth::user()->name); ?></span>
                                                <span class="text-[10px] text-ink-muted mt-0.5"><?php echo e(Auth::user()->role->name ?? 'Admin'); ?></span>
                                            </div>
                                        </div>
                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </button>
                             <?php $__env->endSlot(); ?>

                             <?php $__env->slot('content', null, []); ?> 
                                <?php if (isset($component)) { $__componentOriginal68cb1971a2b92c9735f83359058f7108 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68cb1971a2b92c9735f83359058f7108 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown-link','data' => ['href' => route('profile.edit'),'class' => 'flex items-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('profile.edit')),'class' => 'flex items-center']); ?>
                                    <svg class="h-4 w-4 mr-2 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <?php echo e(__('Profile')); ?>

                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $attributes = $__attributesOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__attributesOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $component = $__componentOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__componentOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>

                                <form method="POST" action="<?php echo e(route('logout')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php if (isset($component)) { $__componentOriginal68cb1971a2b92c9735f83359058f7108 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68cb1971a2b92c9735f83359058f7108 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown-link','data' => ['href' => route('logout'),'class' => 'flex items-center border-t border-gray-100 dark:border-gray-700','onclick' => 'event.preventDefault();
                                                        this.closest(\'form\').submit();']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('logout')),'class' => 'flex items-center border-t border-gray-100 dark:border-gray-700','onclick' => 'event.preventDefault();
                                                        this.closest(\'form\').submit();']); ?>
                                        <svg class="h-4 w-4 mr-2 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        <?php echo e(__('Logout')); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $attributes = $__attributesOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__attributesOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $component = $__componentOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__componentOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
                                </form>
                             <?php $__env->endSlot(); ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $attributes = $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $component = $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 relative overflow-y-auto focus:outline-none content-scrollbar bg-[#f4f7f6] dark:bg-gray-900 transition-colors duration-200">
                    <div class="py-6 px-4 sm:px-6 md:px-8">
                        <?php echo e($slot); ?>

                    </div>
                </main>
            </div>
        </div>
        
        <?php if (isset($component)) { $__componentOriginalbcd757c7bb20b76cf9bcd607adaf8c39 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbcd757c7bb20b76cf9bcd607adaf8c39 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.toast-container','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toast-container'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbcd757c7bb20b76cf9bcd607adaf8c39)): ?>
<?php $attributes = $__attributesOriginalbcd757c7bb20b76cf9bcd607adaf8c39; ?>
<?php unset($__attributesOriginalbcd757c7bb20b76cf9bcd607adaf8c39); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbcd757c7bb20b76cf9bcd607adaf8c39)): ?>
<?php $component = $__componentOriginalbcd757c7bb20b76cf9bcd607adaf8c39; ?>
<?php unset($__componentOriginalbcd757c7bb20b76cf9bcd607adaf8c39); ?>
<?php endif; ?>
        
        <!-- Session Data for JS -->
        <div id="session-flash-data" 
             data-success="<?php echo e(session('success')); ?>" 
             data-error="<?php echo e(session('error')); ?>"
             data-warning="<?php echo e(session('warning')); ?>"
             data-info="<?php echo e(session('info')); ?>"
             data-welcome-first-time="<?php echo e(session('welcome_first_time') ? 'true' : 'false'); ?>"
             data-site-name="<?php echo e(config('app.name')); ?>"
             style="display:none;"></div>
        
        <script>
            // Show session flash messages
            document.addEventListener('DOMContentLoaded', () => {
                const sessionEl = document.getElementById('session-flash-data');
                if (sessionEl) {
                    const sessionToasts = {
                        success: sessionEl.dataset.success,
                        error: sessionEl.dataset.error,
                        warning: sessionEl.dataset.warning,
                        info: sessionEl.dataset.info
                    };

                    Object.entries(sessionToasts).forEach(([type, message]) => {
                        if (message && message !== 'null' && message !== 'undefined' && message.trim() !== '') {
                            showToast(message, type);
                        }
                    });

                    // Check for first-time welcome toast
                    if (sessionEl.dataset.welcomeFirstTime === 'true') {
                        const siteName = sessionEl.dataset.siteName || 'System';
                        showToast(`Welcome to ${siteName}! We're glad to have you here.`, 'welcome', 10000);
                        
                        // Optionally mark any welcome system notification as read automatically
                        fetch('/notifications/mark-welcome-as-read', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        }).then(() => updateUnreadCount());
                    }
                }
            });

            // Listen for expiration_notification events and update unread count
            window.addEventListener('expiration-notification', function() {
                updateUnreadCount();
            });

            let lastNotificationId = 0;

            // Update unread count from server
            function updateUnreadCount(newData = null) {
                if (newData) {
                    processNotificationData(newData);
                    return;
                }

                fetch('<?php echo e(route("notifications.unread-count")); ?>?last_id=' + lastNotificationId, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => processNotificationData(data))
                .catch(error => console.error('Error updating notification count:', error));
            }

            function processNotificationData(data) {
                const badge = document.querySelector('[data-notification-badge]');
                const count = data.unread_count;
                
                if (badge) {
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                }

                // Update notification count in header title if exists
                const titleCount = document.querySelector('h3.font-bold.text-gray-800');
                if (titleCount) {
                    titleCount.textContent = `Notifications ${count > 0 ? '(' + count + ')' : ''}`;
                }

                // Show toasts for new notifications
                if (data.new_notifications && data.new_notifications.length > 0) {
                    data.new_notifications.forEach(notification => {
                        let type = 'info';
                        if (notification.severity === 'danger') type = 'error';
                        if (notification.severity === 'warning') type = 'warning';
                        if (notification.severity === 'success') type = 'success';
                        
                        let toastEl = showToast(notification.message, type);
                        if (toastEl && notification.meta && notification.meta.url) {
                            toastEl.style.cursor = 'pointer';
                            toastEl.addEventListener('click', function(e) {
                                if (e.target.closest('button')) return; // ignore close button
                                window.location.href = notification.meta.url;
                            });
                        }
                        
                        lastNotificationId = Math.max(lastNotificationId, notification.id);

                        // Dispatch global event for active pages (e.g. All Sales table)
                        window.dispatchEvent(new CustomEvent('new-system-notification', { detail: notification }));
                        if (notification.type === 'sale_approval' || notification.type === 'sale_created') {
                            window.dispatchEvent(new CustomEvent('sale-created', { detail: notification }));
                        }
                    });
                }
            }

            // Only poll if user is authenticated (prevents 401 on guest/login pages)
            <?php if(auth()->guard()->check()): ?>
            // Initial check
            updateUnreadCount();

            // Check for new notifications every 5 seconds
            setInterval(updateUnreadCount, 5000);
            <?php endif; ?>

            // Mark all notifications as read
            function markAllNotificationsAsRead(btn) {
                fetch('<?php echo e(route("notifications.mark-all-as-read")); ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update all items in UI to read state
                        const items = document.querySelectorAll('[data-notification-id]');
                        items.forEach(item => {
                            const id = item.dataset.notificationId;
                            updateNotificationUIState(id, false); // false because we update count once at the end
                        });
                        
                        updateUnreadCount({ unread_count: 0, new_notifications: [] });
                    }
                })
                .catch(error => console.error('Error marking all as read:', error));
            }

            // Delete all notifications
            async function deleteAllNotifications(btn) {
                const confirmed = await showDeleteConfirm('all notifications');
                if (!confirmed) return;
                
                fetch('<?php echo e(route("notifications.delete-all")); ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const list = document.getElementById('notification-list');
                        if (list) list.innerHTML = '';
                        updateUnreadCount({ unread_count: 0, new_notifications: [] });
                        
                        const emptyState = document.getElementById('empty-notification-state');
                        if (emptyState) emptyState.style.display = 'block';
                    }
                })
                .catch(error => console.error('Error deleting all notifications:', error));
            }

            // Mark single notification as read
            function markAsRead(id, btn) {
                fetch(`/notifications/${id}/mark-as-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateNotificationUIState(id);
                    }
                })
                .catch(error => console.error('Error marking as read:', error));
            }

            // Delete single notification
            async function deleteNotification(id, btn) {
                const confirmed = await showDeleteConfirm('this notification');
                if (!confirmed) return;

                fetch(`/notifications/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        removeNotificationFromUI(id);
                    }
                })
                .catch(error => console.error('Error deleting notification:', error));
            }

            function updateNotificationUIState(id, updateCount = true) {
                 const item = document.querySelector(`[data-notification-id="${id}"]`);
                 if (item && item.dataset.isRead === 'false') {
                     item.dataset.isRead = 'true';
                     item.classList.remove('bg-red-50/50', 'bg-amber-50/50', 'bg-blue-50/50');
                     item.classList.add('bg-white');
                     
                     const indicator = item.querySelector('[data-unread-indicator]');
                     if (indicator) indicator.remove();

                     const markReadBtn = item.querySelector('[data-mark-read-btn]');
                     if (markReadBtn) markReadBtn.classList.add('hidden');
                     
                     const texts = item.querySelectorAll('p.font-bold, p.text-gray-600');
                     texts.forEach(t => t.classList.add('opacity-60'));

                     if (updateCount) {
                         decrementUnreadCount();
                     }
                 }
             }

            function decrementUnreadCount() {
                const badge = document.querySelector('[data-notification-badge]');
                if (badge) {
                    let count = parseInt(badge.textContent.replace('+', '')) || 0;
                    count = Math.max(0, count - 1);
                    
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : count;
                    } else {
                        badge.style.display = 'none';
                    }
                    
                    const titleCount = document.querySelector('h3.font-bold.text-gray-800');
                    if (titleCount) {
                        titleCount.textContent = `Notifications ${count > 0 ? '(' + count + ')' : ''}`;
                    }
                }
            }

            function removeNotificationFromUI(id) {
                const items = document.querySelectorAll(`[data-notification-id="${id}"]`);
                if (items.length > 0) {
                    const wasUnread = items[0].dataset.isRead === 'false';
                    items.forEach(item => item.remove());
                    
                    if (wasUnread) {
                        decrementUnreadCount();
                    }

                    // Check for empty state in dropdown
                    const dropdownList = document.getElementById('notification-list');
                    if (dropdownList && dropdownList.children.length === 0) {
                        const emptyState = document.getElementById('empty-notification-state');
                        if (emptyState) emptyState.style.display = 'block';
                    }

                    // Check for empty state in full page
                    const pageList = document.getElementById('full-notification-list');
                    if (pageList && pageList.children.length === 0) {
                        location.reload(); // Reload to show empty state correctly on full page
                    }
                }
            }
        </script>
        
        <script src="<?php echo e(asset('js/navigation-shortcuts.js')); ?>?v=<?php echo e(@filemtime(public_path('js/navigation-shortcuts.js')) ?: time()); ?>"></script>
        <?php echo $__env->yieldPushContent('scripts'); ?>

        
        <div id="global-delete-modal" style="display:none" class="fixed inset-0 z-[250] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" onclick="window._deleteResolve && window._deleteResolve(false); document.getElementById('global-delete-modal').style.display='none'"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-sm w-full z-10 border border-gray-100 dark:border-gray-700">
                    <div class="px-6 pt-6 pb-2 text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                            <svg class="h-7 w-7 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-ink-heading mb-1">Confirm Delete</h3>
                        <p class="text-sm text-ink-muted">
                            Are you sure you want to delete
                            <span id="global-delete-name" class="font-semibold text-ink-body"></span>?
                        </p>
                        <p class="text-xs text-ink-muted mt-1">This action cannot be undone.</p>
                    </div>
                    <div class="px-6 pb-6 pt-4 flex gap-3">
                        <button type="button" onclick="window._deleteResolve && window._deleteResolve(false); document.getElementById('global-delete-modal').style.display='none'"
                            class="flex-1 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-semibold text-ink-body hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancel
                        </button>
                        <button type="button" onclick="window._deleteResolve && window._deleteResolve(true); document.getElementById('global-delete-modal').style.display='none'"
                            class="flex-1 py-2.5 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-colors">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            function showDeleteConfirm(name) {
                return new Promise(resolve => {
                    window._deleteResolve = resolve;
                    document.getElementById('global-delete-name').textContent = name;
                    document.getElementById('global-delete-modal').style.display = '';
                });
            }
        </script>
</body>
</html>
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/layouts/app.blade.php ENDPATH**/ ?>