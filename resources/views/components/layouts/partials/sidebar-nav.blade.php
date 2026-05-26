@php
    $linkClass = function (array $patterns, bool $child = false): string {
        $active = request()->routeIs($patterns);
        if ($child) {
            return $active
                ? 'flex items-center gap-2.5 rounded-md bg-white/10 py-2 pl-9 pr-3 text-sm font-medium text-white'
                : 'flex items-center gap-2.5 rounded-md py-2 pl-9 pr-3 text-sm text-slate-400 transition hover:bg-white/5 hover:text-slate-200';
        }

        return $active
            ? 'flex w-full items-center gap-3 rounded-md bg-white/10 px-3 py-2.5 text-sm font-medium text-white'
            : 'flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-sm text-slate-400 transition hover:bg-white/5 hover:text-slate-200';
    };

    $accordionOpen = function (array $patterns): bool {
        return request()->routeIs($patterns);
    };
@endphp

<div class="flex h-full flex-col bg-[#2c3344] text-slate-300" id="app-sidebar">
    <div class="border-b border-white/10 px-4 py-5">
        <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3">
            <x-ui.site-logo size="md" />
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-white">{{ $websiteBranding['site_name'] ?? config('app.name') }}</p>
                <p class="truncate text-xs text-slate-500">{{ $websiteBranding['site_tagline'] ?? 'Operations hub' }}</p>
            </div>
        </a>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Main</p>
        @can('view-dashboard')
            <a href="{{ route('dashboard.index') }}" class="{{ $linkClass(['dashboard.*']) }}">
                <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l9-9 9 9M5 10v10h14V10"/></svg>
                Dashboard
            </a>
        @endcan

        @can('manage-leads')
            <p class="mb-2 mt-5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Sales</p>
            <div class="sidebar-accordion" data-open="{{ $accordionOpen(['admin.leads.*']) ? 'true' : 'false' }}">
                <button type="button" class="sidebar-accordion-trigger {{ $linkClass(['admin.leads.*']) }} w-full text-left">
                    <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="flex-1">Leads</span>
                    <svg class="sidebar-accordion-chevron h-4 w-4 shrink-0 opacity-60 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="sidebar-accordion-panel mt-0.5 space-y-0.5 {{ $accordionOpen(['admin.leads.*']) ? '' : 'hidden' }}">
                    <a href="{{ route('admin.leads.index') }}" class="{{ $linkClass(['admin.leads.index', 'admin.leads.show', 'admin.leads.edit'], true) }}">All leads</a>
                    <a href="{{ route('admin.leads.create') }}" class="{{ $linkClass(['admin.leads.create'], true) }}">Add lead</a>
                </div>
            </div>
        @endcan

        @can('manage-customers')
            <p class="mb-2 mt-5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Customers</p>
            <div class="sidebar-accordion" data-open="{{ $accordionOpen(['admin.clients.*']) ? 'true' : 'false' }}">
                <button type="button" class="sidebar-accordion-trigger {{ $linkClass(['admin.clients.*']) }} w-full text-left">
                    <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span class="flex-1">Customers</span>
                    <svg class="sidebar-accordion-chevron h-4 w-4 shrink-0 opacity-60 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="sidebar-accordion-panel mt-0.5 space-y-0.5 {{ $accordionOpen(['admin.clients.*']) ? '' : 'hidden' }}">
                    <a href="{{ route('admin.clients.index') }}" class="{{ $linkClass(['admin.clients.index', 'admin.clients.show', 'admin.clients.edit'], true) }}">All customers</a>
                    <a href="{{ route('admin.clients.create') }}" class="{{ $linkClass(['admin.clients.create'], true) }}">Add customer</a>
                </div>
            </div>
        @endcan

        @can('view-jobs')
            <p class="mb-2 mt-5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Operations</p>
            <div class="sidebar-accordion" data-open="{{ $accordionOpen(['admin.jobs.*', 'admin.maps.*', 'mower.*', 'employee.mobile.*']) ? 'true' : 'false' }}">
                <button type="button" class="sidebar-accordion-trigger {{ $linkClass(['admin.jobs.*', 'admin.maps.*', 'mower.*', 'employee.mobile.*']) }} w-full text-left">
                    <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="flex-1">Jobs</span>
                    <svg class="sidebar-accordion-chevron h-4 w-4 shrink-0 opacity-60 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="sidebar-accordion-panel mt-0.5 space-y-0.5 {{ $accordionOpen(['admin.jobs.*', 'admin.maps.*', 'mower.*', 'employee.mobile.*']) ? '' : 'hidden' }}">
                    <a href="{{ route('admin.jobs.index') }}" class="{{ $linkClass(['admin.jobs.index', 'admin.jobs.show', 'admin.jobs.edit'], true) }}">All jobs</a>
                    @can('manage-job-records')
                        <a href="{{ route('admin.jobs.create') }}" class="{{ $linkClass(['admin.jobs.create'], true) }}">Create job</a>
                    @endcan
                    <a href="{{ route('admin.maps.index') }}" class="{{ $linkClass(['admin.maps.*'], true) }}">Map &amp; routing</a>
                    <a href="{{ route('mower.index') }}" class="{{ $linkClass(['mower.*', 'employee.mobile.*'], true) }}">Mower dashboard</a>
                </div>
            </div>
        @endcan

        <p class="mb-2 mt-5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Account</p>
        <a href="{{ route('notifications.index') }}" class="{{ $linkClass(['notifications.*']) }}">
            <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            Notifications
        </a>
        <a href="{{ route('profile.edit') }}" class="{{ $linkClass(['profile.*']) }}">
            <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Profile
        </a>

        @can('manage-users')
            <p class="mb-2 mt-5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Admin</p>
            <div class="sidebar-accordion" data-open="{{ $accordionOpen(['admin.users.*', 'admin.activity-logs.*', 'admin.settings.*', 'admin.rbac.*', 'admin.masters.*']) ? 'true' : 'false' }}">
                <button type="button" class="sidebar-accordion-trigger {{ $linkClass(['admin.users.*', 'admin.activity-logs.*', 'admin.settings.*', 'admin.rbac.*', 'admin.masters.*']) }} w-full text-left">
                    <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="flex-1">Administration</span>
                    <svg class="sidebar-accordion-chevron h-4 w-4 shrink-0 opacity-60 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="sidebar-accordion-panel mt-0.5 space-y-0.5 {{ $accordionOpen(['admin.users.*', 'admin.activity-logs.*', 'admin.settings.*', 'admin.rbac.*', 'admin.masters.*']) ? '' : 'hidden' }}">
                    <a href="{{ route('admin.users.index') }}" class="{{ $linkClass(['admin.users.index', 'admin.users.show', 'admin.users.create'], true) }}">Users</a>
                    @can('manage-masters')
                        <a href="{{ route('admin.masters.service-types.index') }}" class="{{ $linkClass(['admin.masters.service-types.*'], true) }}">Service types</a>
                        <a href="{{ route('admin.masters.equipment-types.index') }}" class="{{ $linkClass(['admin.masters.equipment-types.*'], true) }}">Equipment types</a>
                        <a href="{{ route('admin.masters.safety-types.index') }}" class="{{ $linkClass(['admin.masters.safety-types.*'], true) }}">Safety types</a>
                    @endcan
                    <a href="{{ route('admin.activity-logs.index') }}" class="{{ $linkClass(['admin.activity-logs.*'], true) }}">Activity logs</a>
                    <a href="{{ route('admin.settings.index') }}" class="{{ $linkClass(['admin.settings.*'], true) }}">Website settings</a>
                    <a href="{{ route('admin.rbac.index') }}" class="{{ $linkClass(['admin.rbac.*'], true) }}">Roles &amp; permissions</a>
                </div>
            </div>
        @endcan
    </nav>

    <div class="border-t border-white/10 p-4">
        <div class="flex items-center gap-3 rounded-lg bg-white/5 px-3 py-2.5">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/10 text-xs font-semibold text-white">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-slate-500">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</p>
            </div>
        </div>
    </div>
</div>
