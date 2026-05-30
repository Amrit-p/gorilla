@props([
    'title' => 'Dashboard',
    'subtitle' => null,
])

<x-layouts.app :title="$title">


    <div class="min-h-screen bg-[#f3f5f9]">
        <header class="border-b border-[#2c3344] bg-[#2c3344] lg:hidden">
            <div class="flex items-center justify-between px-4 py-3">
                <a href="{{ route('dashboard.index') }}" class="flex items-center gap-2">
                    <x-ui.site-logo size="sm" />
                    <p class="text-sm font-semibold text-white">{{ $websiteBranding['site_name'] ?? config('app.name') }}</p>
                </a>
                <button id="mobile-menu-toggle" type="button" class="rounded-md border border-white/20 px-3 py-1.5 text-sm text-slate-200 hover:bg-white/10">
                    Menu
                </button>
            </div>
        </header>

        <div class="flex">
            <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 hidden h-screen lg:block">
                @include('components.layouts.partials.sidebar-nav')
            </aside>

            {{-- Floating collapse toggle (desktop only) --}}
            <button id="sidebar-collapse-btn"
                type="button"
                class="fixed top-[22px] z-50 hidden h-6 w-6 items-center justify-center rounded-full border border-slate-600 bg-[#3a4358] text-slate-400 shadow-md hover:border-slate-400 hover:text-white lg:flex"
                style="left: calc(16rem - 12px);"
                title="Toggle sidebar">
                <svg id="collapse-icon" class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            <main id="main-content" class="min-h-screen w-full flex-1">
                <div class="border-b border-slate-200 bg-white">
                    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6">
                        <div class="min-w-[200px]">
                            <h1 class="text-lg font-semibold text-slate-900">{{ $title }}</h1>
                            @if ($subtitle)
                                <p class="text-xs text-slate-500">{{ $subtitle }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('notifications.index') }}" class="relative rounded-md border border-slate-300 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50">
                                Notifications
                                @if (($unreadNotificationCount ?? 0) > 0)
                                    <span class="absolute -right-1 -top-1 rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] text-white">{{ $unreadNotificationCount }}</span>
                                @endif
                            </a>
                            <span class="hidden rounded-md bg-slate-100 px-3 py-2 text-xs font-medium text-slate-700 sm:inline">
                                {{ auth()->user()->name }}
                            </span>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <x-ui.button type="submit" class="px-3 py-2 text-xs">Logout</x-ui.button>
                            </form>
                        </div>
                    </div>
                </div>

                <section class="p-4 sm:p-6">
                    {{ $slot }}
                </section>
            </main>
        </div>
    </div>

    <script>
        // ===== MOBILE MENU =====
        $('#mobile-menu-toggle').on('click', function () {
            $('#sidebar').toggleClass('hidden');
        });

        // ===== ACCORDION (expanded sidebar — click to open/close) =====
        // Restore open state from data-open attribute (set server-side when route matches)
        $('.sidebar-accordion').each(function () {
            if ($(this).data('open') === 'true' || $(this).data('open') === true) {
                $(this).addClass('is-open');
            }
        });

        $(document).on('click', '.sidebar-accordion-trigger', function () {
            if ($('#sidebar').hasClass('sidebar-collapsed')) return;
            $(this).closest('.sidebar-accordion').toggleClass('is-open');
        });

        // ===== SIDEBAR COLLAPSE (DESKTOP) =====
        // Default to collapsed (null = first visit = collapsed)
        var stored = localStorage.getItem('sidebarCollapsed');
        var sidebarCollapsed = stored === null ? true : stored === 'true';

        function applySidebarState() {
            if (sidebarCollapsed) {
                $('#sidebar').addClass('sidebar-collapsed');
                $('#main-content').addClass('sidebar-collapsed');
                $('#collapse-icon').css('transform', 'rotate(180deg)');
                $('#sidebar-collapse-btn').css('left', 'calc(4rem - 12px)');
            } else {
                $('#sidebar').removeClass('sidebar-collapsed');
                $('#main-content').removeClass('sidebar-collapsed');
                $('#collapse-icon').css('transform', '');
                $('#sidebar-collapse-btn').css('left', 'calc(16rem - 12px)');
            }
        }

        // Apply initial state without animation, then remove the pre-paint init class
        applySidebarState();
        $('html').removeClass('sidebar-init-collapsed');
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                $('body').removeClass('no-transitions');
            });
        });

        $('#sidebar-collapse-btn').on('click', function () {
            clearFlyout();
            sidebarCollapsed = !sidebarCollapsed;
            localStorage.setItem('sidebarCollapsed', sidebarCollapsed);
            applySidebarState();
        });

        // ===== COLLAPSED SIDEBAR FLYOUT =====
        var $activeFlyout = null;
        var flyoutHideTimer = null;

        function clearFlyout() {
            clearTimeout(flyoutHideTimer);
            if ($activeFlyout) {
                $activeFlyout.find('.sidebar-flyout-title').remove();
                $activeFlyout.removeClass('sidebar-flyout').css({ top: '', display: '' });
                $activeFlyout = null;
            }
        }

        function showFlyout($accordion) {
            clearTimeout(flyoutHideTimer);
            var $panel = $accordion.find('.sidebar-accordion-panel');
            var triggerRect = $accordion.find('.sidebar-accordion-trigger')[0].getBoundingClientRect();
            var label = $accordion.find('.sidebar-text').first().text().trim();

            if ($activeFlyout && $activeFlyout[0] !== $panel[0]) {
                clearFlyout();
            }

            if (!$panel.find('.sidebar-flyout-title').length && label) {
                $panel.prepend('<div class="sidebar-flyout-title">' + label + '</div>');
            }

            $panel.css({ top: triggerRect.top + 'px', display: 'block' }).addClass('sidebar-flyout');
            $activeFlyout = $panel;
        }

        function scheduleFlyoutHide() {
            flyoutHideTimer = setTimeout(clearFlyout, 120);
        }

        $(document).on('mouseenter', '#sidebar.sidebar-collapsed .sidebar-accordion', function () {
            showFlyout($(this));
        }).on('mouseleave', '#sidebar.sidebar-collapsed .sidebar-accordion', function () {
            scheduleFlyoutHide();
        });

        $(document).on('mouseenter', '.sidebar-flyout', function () {
            clearTimeout(flyoutHideTimer);
        }).on('mouseleave', '.sidebar-flyout', function () {
            scheduleFlyoutHide();
        });
    </script>
</x-layouts.app>
