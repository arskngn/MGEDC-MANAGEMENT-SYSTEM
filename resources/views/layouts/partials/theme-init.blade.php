{{-- Apply theme + sidebar state before paint to prevent flash on reload/navigation --}}
@php
    $defaultThemeDark = ($generalSetting->default_theme ?? 'light') === 'dark';
@endphp
<script>
(function () {
    try {
        var root = document.documentElement;
        root.classList.add('theme-loading', 'sidebar-loading');

        var stored = localStorage.getItem('dark_mode');
        var isDark = stored === 'true' || (stored === null && @json($defaultThemeDark));
        if (isDark) {
            root.classList.add('dark');
            root.style.colorScheme = 'dark';
        } else {
            root.classList.remove('dark');
            root.style.colorScheme = 'light';
        }

        var isDesktop = window.matchMedia('(min-width: 1024px)').matches;
        var sidebarCollapsed = isDesktop && localStorage.getItem('sidebar_collapsed') === 'true';
        if (sidebarCollapsed) {
            root.classList.add('sidebar-collapsed');
            root.classList.remove('sidebar-expanded');
        } else {
            root.classList.add('sidebar-expanded');
            root.classList.remove('sidebar-collapsed');
        }
    } catch (e) {}
})();
</script>
<style>
    :root { color-scheme: light; }
    html {
        /* Token-aware anti-flash backgrounds — match surface-body tokens defined in app.css */
        background-color: #F7F8FA;
        color: #1F2937;
        color-scheme: light;
    }
    html.dark {
        background-color: #0B1220;
        color: #E5E7EB;
        color-scheme: dark;
    }
    html.dark body { background-color: #0B1220; color: #E5E7EB; }
    html.dark main { background-color: #0B1220; color: #E5E7EB; }
    html.theme-loading body,
    html.theme-loading main,
    html.theme-loading .transition-colors,
    html.sidebar-loading .sidebar-panel-preload,
    html.sidebar-loading .sidebar-panel-preload * {
        transition: none !important;
    }

    @media (min-width: 1024px) {
        html.sidebar-collapsed .sidebar-panel-preload {
            width: 5rem !important;
        }
        html.sidebar-expanded .sidebar-panel-preload {
            width: 16rem !important;
        }
        html.sidebar-collapsed .sidebar-panel-preload .sidebar-header {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        html.sidebar-collapsed .sidebar-panel-preload .sidebar-expand-only,
        html.sidebar-collapsed .sidebar-panel-preload .sidebar-submenu {
            display: none !important;
        }
        html.sidebar-expanded .sidebar-panel-preload .sidebar-expand-only:not(.sidebar-submenu) {
            display: revert !important;
        }
        html.sidebar-expanded .sidebar-panel-preload .sidebar-header {
            justify-content: space-between !important;
        }

        html.sidebar-collapsed .sidebar-panel-preload .custom-scrollbar {
            padding-left: 0.375rem !important;
            padding-right: 0.375rem !important;
        }
        html.sidebar-collapsed .sidebar-panel-preload .sidebar-header a {
            justify-content: center !important;
            width: 100%;
        }

        /* Compact icon-only nav items when collapsed */
        html.sidebar-collapsed .sidebar-panel-preload .sidebar-nav-item {
            justify-content: center !important;
            align-items: center !important;
            width: 2.75rem !important;
            min-width: 2.75rem !important;
            max-width: 2.75rem !important;
            height: 2.75rem !important;
            margin-left: auto !important;
            margin-right: auto !important;
            padding: 0 !important;
            flex-shrink: 0;
        }
        html.sidebar-collapsed .sidebar-panel-preload button.sidebar-nav-item {
            width: 2.75rem !important;
        }
        html.sidebar-collapsed .sidebar-panel-preload .sidebar-nav-item > span.flex {
            justify-content: center !important;
            width: 100%;
            margin: 0 !important;
        }
        html.sidebar-collapsed .sidebar-panel-preload .sidebar-nav-item svg.h-5 {
            margin-right: 0 !important;
            flex-shrink: 0;
        }
        html.sidebar-collapsed .sidebar-panel-preload .sidebar-nav-item.shadow-md {
            box-shadow: 0 1px 3px rgba(70, 52, 255, 0.35) !important;
        }
        html.sidebar-collapsed .sidebar-panel-preload .sidebar-nav-item .sidebar-expand-only {
            display: none !important;
            width: 0 !important;
            overflow: hidden;
        }
        html.sidebar-expanded .sidebar-panel-preload a.sidebar-nav-item {
            padding: 0.625rem 1rem !important;
        }
        html.sidebar-expanded .sidebar-panel-preload button.sidebar-nav-item {
            padding: 0.625rem 1rem !important;
            justify-content: space-between !important;
        }
        html.sidebar-expanded .sidebar-panel-preload .sidebar-nav-item:not(.sidebar-nav-item--icon-only) svg.h-5 {
            margin-right: 0.75rem !important;
        }
    }
</style>
