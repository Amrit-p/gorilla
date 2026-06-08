<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.meta.website-seo')

    {{-- Apply sidebar collapsed state before first paint to prevent layout flash --}}
    <script>
        (function () {
            var c = localStorage.getItem('sidebarCollapsed');
            if (c === null || c === 'true') {
                document.documentElement.classList.add('sidebar-init-collapsed');
            }
        }());
    </script>

    {{-- Tailwind CSS by CDN (project rule: no npm build pipeline) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')

    {{-- jQuery CDN for upcoming AJAX modules --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    <style>
        /* ===== PREVENT FLASH ON INITIAL LOAD ===== */
        .no-transitions * { transition: none !important; }

        /* ===== SIDEBAR COLLAPSE ===== */
        #sidebar {
            overflow: hidden;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #main-content {
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        @media (min-width: 1024px) {
            #sidebar            { width: 16rem; }
            #sidebar.sidebar-collapsed { width: 4rem; }
            #main-content       { margin-left: 16rem; }
            #main-content.sidebar-collapsed { margin-left: 4rem; }

            /* Pre-collapse layout applied before first paint — eliminates width flash on refresh */
            html.sidebar-init-collapsed #sidebar            { width: 4rem; }
            html.sidebar-init-collapsed #main-content       { margin-left: 4rem; }
            html.sidebar-init-collapsed #sidebar-collapse-btn { left: calc(4rem - 12px) !important; }
        }
        @media (max-width: 1023px) {
            #sidebar      { width: 16rem !important; }
            #main-content { margin-left: 0 !important; }
        }

        /* Floating toggle button */
        #sidebar-collapse-btn {
            transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #sidebar-collapse-btn svg {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Brand / logo text */
        .sidebar-brand-text {
            overflow: hidden;
            white-space: nowrap;
            max-width: 200px;
            opacity: 1;
            transition: max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.15s ease;
        }
        #sidebar.sidebar-collapsed .sidebar-brand-text {
            max-width: 0;
            opacity: 0;
        }

        /* Inline nav-item text */
        .sidebar-text {
            overflow: hidden;
            white-space: nowrap;
            max-width: 200px;
            opacity: 1;
            transition: max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.15s ease;
        }
        #sidebar.sidebar-collapsed .sidebar-text {
            max-width: 0;
            opacity: 0;
        }

        /* Section labels (Main / Sales / etc.) */
        .sidebar-label {
            overflow: hidden;
            max-height: 2.5rem;
            opacity: 1;
            transition: max-height 0.25s ease, opacity 0.15s ease, margin 0.25s ease;
        }
        #sidebar.sidebar-collapsed .sidebar-label {
            max-height: 0;
            opacity: 0;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        /* Center icons when collapsed */
        #sidebar.sidebar-collapsed .sidebar-nav-item {
            justify-content: center;
            padding-left: 0 !important;
            padding-right: 0 !important;
            gap: 0 !important;
        }

        /* Accordion chevron */
        .sidebar-accordion-chevron {
            flex-shrink: 0;
            overflow: hidden;
            max-width: 1.5rem;
            transition: transform 0.3s ease, max-width 0.25s ease, opacity 0.15s ease !important;
        }
        #sidebar.sidebar-collapsed .sidebar-accordion-chevron {
            max-width: 0;
            opacity: 0;
        }

        /* User info text */
        .sidebar-user-info {
            overflow: hidden;
            white-space: nowrap;
            max-width: 200px;
            opacity: 1;
            transition: max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.15s ease;
        }
        #sidebar.sidebar-collapsed .sidebar-user-info {
            max-width: 0;
            opacity: 0;
        }

        /* User section when collapsed */
        #sidebar.sidebar-collapsed #sidebar-user-section {
            padding: 0.75rem 0.25rem;
            transition: padding 0.3s ease;
        }
        #sidebar.sidebar-collapsed #sidebar-user-avatar-wrap {
            justify-content: center;
            gap: 0;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            background: transparent;
        }

        /* Click-based accordion panels */
        .sidebar-accordion-panel {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.25s ease, opacity 0.15s ease;
        }
        .sidebar-accordion.is-open .sidebar-accordion-panel {
            max-height: 300px;
            opacity: 1;
        }
        .sidebar-accordion.is-open .sidebar-accordion-chevron {
            transform: rotate(180deg);
        }
        /* Hide panels in icon-only mode — flyout JS handles hover */
        #sidebar.sidebar-collapsed .sidebar-accordion-panel {
            display: none;
        }

        /* Flyout panel (collapsed sidebar hover) */
        .sidebar-flyout {
            display: block !important;
            position: fixed !important;
            left: 4.25rem !important;
            max-height: none !important;
            opacity: 1 !important;
            overflow: visible !important;
            background: #2c3344;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 0.5rem;
            padding: 0.375rem;
            min-width: 180px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            z-index: 999999;
            transition: none !important;
        }
        .sidebar-flyout-title {
            padding: 0.375rem 0.75rem 0.5rem;
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            margin-bottom: 0.25rem;
        }
        .sidebar-flyout a {
            padding-left: 0.75rem !important;
        }

        /* ===== HIDE SCROLLBAR, KEEP SCROLL ===== */
        #app-sidebar nav {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        #app-sidebar nav::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 no-transitions">
    {{-- Main content slot shared by all pages --}}
    {{ $slot }}

    @include('components.scripts.jquery-validate')
    @stack('scripts')
</body>
</html>
