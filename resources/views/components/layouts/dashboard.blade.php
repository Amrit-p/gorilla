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
            <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 hidden h-screen w-64 lg:block">
                @include('components.layouts.partials.sidebar-nav')
            </aside>

            <main class="min-h-screen w-full flex-1 lg:ml-64">
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
        $('#mobile-menu-toggle').on('click', function () {
            $('#sidebar').toggleClass('hidden');
        });

        function syncSidebarAccordion($accordion) {
            const open = $accordion.attr('data-open') === 'true';
            const $panel = $accordion.find('.sidebar-accordion-panel').first();
            const $chevron = $accordion.find('.sidebar-accordion-chevron').first();
            $panel.toggleClass('hidden', !open);
            $chevron.toggleClass('rotate-180', open);
        }

        $('.sidebar-accordion').each(function () {
            syncSidebarAccordion($(this));
        });

        $(document).on('click', '.sidebar-accordion-trigger', function () {
            const $accordion = $(this).closest('.sidebar-accordion');
            const isOpen = $accordion.attr('data-open') === 'true';
            $accordion.attr('data-open', isOpen ? 'false' : 'true');
            syncSidebarAccordion($accordion);
        });
    </script>
</x-layouts.app>
