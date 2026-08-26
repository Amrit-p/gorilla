@php
    $linkClass = function (array $patterns, bool $child = false): string {
        $active = request()->routeIs($patterns);
        if ($child) {
            return $active
                ? 'flex items-center gap-2.5 rounded-md bg-white/10 py-2 pl-9 pr-3 text-sm font-medium text-white'
                : 'flex items-center gap-2.5 rounded-md py-2 pl-9 pr-3 text-sm text-slate-400 transition hover:bg-white/5 hover:text-slate-200';
        }

        return $active
            ? 'sidebar-nav-item flex w-full items-center gap-3 rounded-md bg-white/10 px-3 py-2.5 text-sm font-medium text-white'
            : 'sidebar-nav-item flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-sm text-slate-400 transition hover:bg-white/5 hover:text-slate-200';
    };

    $accordionOpen = function (array $patterns): bool {
        return request()->routeIs($patterns);
    };
@endphp

<div class="flex h-full flex-col bg-[#2c3344] text-slate-300" id="app-sidebar">
    <div class="border-b border-white/10 px-4 py-5">
        <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3">
            <x-ui.site-logo size="md" class="shrink-0" />
            <div class="sidebar-brand-text min-w-0">
                <p class="truncate text-sm font-semibold text-white">{{ $websiteBranding['site_name'] ?? config('app.name') }}</p>
                <p class="truncate text-xs text-slate-500">{{ $websiteBranding['site_tagline'] ?? 'Operations hub' }}</p>
            </div>
        </a>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        <p class="sidebar-label mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Main</p>
        @can('view-dashboard')
            <a href="{{ route('dashboard.index') }}" class="{{ $linkClass(['dashboard.*']) }}" title="Dashboard">
                <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l9-9 9 9M5 10v10h14V10"/></svg>
                <span class="sidebar-text flex-1">Dashboard</span>
            </a>
        @endcan

        @can('manage-leads')
            <p class="sidebar-label mb-2 mt-5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Sales</p>
            <div class="sidebar-accordion" data-open="{{ $accordionOpen(['admin.leads.*']) ? 'true' : 'false' }}">
                <button type="button" class="sidebar-accordion-trigger {{ $linkClass(['admin.leads.*']) }} w-full text-left" title="Leads">
                    <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="sidebar-text flex-1">Leads</span>
                    <svg class="sidebar-accordion-chevron h-4 w-4 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="sidebar-accordion-panel mt-0.5 space-y-0.5">
                    <a href="{{ route('admin.leads.index') }}" class="{{ $linkClass(['admin.leads.index', 'admin.leads.show', 'admin.leads.edit'], true) }}">All leads</a>
                    <a href="{{ route('admin.leads.converted') }}" class="{{ $linkClass(['admin.leads.converted'], true) }}">Converted leads</a>
                    <a href="{{ route('admin.leads.create') }}" class="{{ $linkClass(['admin.leads.create'], true) }}">Add lead</a>
                </div>
            </div>
        @endcan

        @can('view-jobs')
            <p class="sidebar-label mb-2 mt-5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Operations</p>
            <div class="sidebar-accordion" data-open="{{ $accordionOpen(['admin.jobs.*', 'admin.maps.*', 'mower.*', 'employee.mobile.*']) ? 'true' : 'false' }}">
                <button type="button" class="sidebar-accordion-trigger {{ $linkClass(['admin.jobs.*', 'admin.maps.*', 'mower.*', 'employee.mobile.*']) }} w-full text-left" title="Jobs">
                    <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="sidebar-text flex-1">Jobs</span>
                    <svg class="sidebar-accordion-chevron h-4 w-4 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="sidebar-accordion-panel mt-0.5 space-y-0.5">
                    <a href="{{ route('admin.jobs.index') }}" class="{{ $linkClass(['admin.jobs.index', 'admin.jobs.show', 'admin.jobs.edit'], true) }}">All jobs</a>
                    @can('create', \App\Models\Job::class)
                        <a href="{{ route('admin.jobs.create') }}" class="{{ $linkClass(['admin.jobs.create'], true) }}">Create job</a>
                    @endcan
                    <a href="{{ route('admin.maps.index') }}" class="{{ $linkClass(['admin.maps.*'], true) }}">Map &amp; routing</a>
                    <a href="{{ route('mower.index') }}" class="{{ $linkClass(['mower.*', 'employee.mobile.*'], true) }}">Mower dashboard</a>
                </div>
            </div>
        @endcan

        @canany(['view-mower-report', 'view-checklist-report'])
            <p class="sidebar-label mb-2 mt-5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Reports</p>
        @endcanany
        @can('view-mower-report')
            <a href="{{ route('reports.mower.index') }}" class="{{ $linkClass(['reports.mower.*']) }}" title="Mower report">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-[18px] w-[18px] shrink-0 opacity-80">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25M9 16.5v.75m3-3v3M15 12v5.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <span class="sidebar-text flex-1">Mower report</span>
            </a>
        @endcan
        @can('view-checklist-report')
            <a href="{{ route('reports.checklist.index') }}" class="{{ $linkClass(['reports.checklist.*']) }}" title="Checklist report">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-[18px] w-[18px] shrink-0 opacity-80">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="sidebar-text flex-1">Checklist report</span>
            </a>
        @endcan

        <p class="sidebar-label mb-2 mt-5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Account</p>
        <a href="{{ route('notifications.index') }}" class="{{ $linkClass(['notifications.*']) }}" title="Notifications">
            <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <span class="sidebar-text flex-1">Notifications</span>
        </a>
        <a href="{{ route('profile.edit') }}" class="{{ $linkClass(['profile.*']) }}" title="Profile">
            <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span class="sidebar-text flex-1">Profile</span>
        </a>

        @can('manage-employee-bonuses')
            <p class="sidebar-label mb-2 mt-5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Payroll</p>
            <div class="sidebar-accordion" data-open="{{ $accordionOpen(['admin.employee-bonuses.*']) ? 'true' : 'false' }}">
                <button type="button" class="sidebar-accordion-trigger {{ $linkClass(['admin.employee-bonuses.*']) }} w-full text-left" title="Employee Bonuses">
                    <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="sidebar-text flex-1">Employee Bonuses</span>
                    <svg class="sidebar-accordion-chevron h-4 w-4 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="sidebar-accordion-panel mt-0.5 space-y-0.5">
                    <a href="{{ route('admin.employee-bonuses.index') }}" class="{{ $linkClass(['admin.employee-bonuses.index', 'admin.employee-bonuses.edit'], true) }}">All bonuses</a>
                    <a href="{{ route('admin.employee-bonuses.create') }}" class="{{ $linkClass(['admin.employee-bonuses.create'], true) }}">Add bonus</a>
                </div>
            </div>
        @endcan

        @can('manage-salary-calculator')
            <a href="{{ route('admin.salary-calculator.index') }}" class="{{ $linkClass(['admin.salary-calculator.*']) }}" title="Salary Calculator">
                <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 14l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="sidebar-text flex-1">Salary Calculator</span>
            </a>
        @endcan

        @can('manage-contractors')
            <p class="sidebar-label mb-2 mt-5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Contractors</p>
            <a href="{{ route('admin.contractors.index') }}" class="{{ $linkClass(['admin.contractors.*']) }}" title="Contractors">
                <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="sidebar-text flex-1">Contractors</span>
            </a>
        @endcan

        @canany(['manage-discussions', 'manage-followups'])
            <p class="sidebar-label mb-2 mt-5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Internal</p>
            @can('manage-discussions')
                <div class="sidebar-accordion" data-open="{{ $accordionOpen(['admin.discussions.*']) ? 'true' : 'false' }}">
                    <button type="button" class="sidebar-accordion-trigger {{ $linkClass(['admin.discussions.*']) }} w-full text-left" title="Discussions">
                        <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span class="sidebar-text flex-1">Discussions</span>
                        <svg class="sidebar-accordion-chevron h-4 w-4 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="sidebar-accordion-panel mt-0.5 space-y-0.5">
                        <a href="{{ route('admin.discussions.index') }}" class="{{ $linkClass(['admin.discussions.index', 'admin.discussions.edit'], true) }}">All discussions</a>
                        <a href="{{ route('admin.discussions.create') }}" class="{{ $linkClass(['admin.discussions.create'], true) }}">New discussion</a>
                    </div>
                </div>
            @endcan
            @can('manage-followups')
                <a href="{{ route('admin.followups.index') }}" class="{{ $linkClass(['admin.followups.*']) }}" title="Follow-ups">
                    <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span class="sidebar-text flex-1">Follow-ups</span>
                </a>
            @endcan
        @endcanany

        @can('manage-users')
            <p class="sidebar-label mb-2 mt-5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Admin</p>
            <div class="sidebar-accordion" data-open="{{ $accordionOpen(['admin.users.*', 'admin.activity-logs.*', 'admin.settings.*', 'admin.rbac.*', 'admin.masters.*', 'admin.employee-bonuses.*']) ? 'true' : 'false' }}">
                <button type="button" class="sidebar-accordion-trigger {{ $linkClass(['admin.users.*', 'admin.activity-logs.*', 'admin.settings.*', 'admin.rbac.*', 'admin.masters.*']) }} w-full text-left" title="Administration">
                    <svg class="h-[18px] w-[18px] shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="sidebar-text flex-1">Administration</span>
                    <svg class="sidebar-accordion-chevron h-4 w-4 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="sidebar-accordion-panel mt-0.5 space-y-0.5">
                    <a href="{{ route('admin.users.index') }}" class="{{ $linkClass(['admin.users.index', 'admin.users.show', 'admin.users.create'], true) }}">Users</a>
                    @can('manage-masters')
                        <a href="{{ route('admin.masters.checklists.index') }}" class="{{ $linkClass(['admin.masters.checklists.*'], true) }}">Checklists</a>
                        <a href="{{ route('admin.masters.accounting-levels.index') }}" class="{{ $linkClass(['admin.masters.accounting-levels.*'], true) }}">Accounting levels</a>
                        <a href="{{ route('admin.masters.client-ratings.index') }}" class="{{ $linkClass(['admin.masters.client-ratings.*'], true) }}">Client ratings</a>
                        <a href="{{ route('admin.masters.job-levels.index') }}" class="{{ $linkClass(['admin.masters.job-levels.*'], true) }}">Job levels</a>
                        <a href="{{ route('admin.masters.zones.index') }}" class="{{ $linkClass(['admin.masters.zones.*'], true) }}">Zones</a>
                        <a href="{{ route('admin.masters.recurrences.index') }}" class="{{ $linkClass(['admin.masters.recurrences.*'], true) }}">Recurrences</a>
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

    <div id="sidebar-user-section" class="border-t border-white/10 p-4">
        <div id="sidebar-user-avatar-wrap" class="flex items-center gap-3 rounded-lg bg-white/5 px-3 py-2.5">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/10 text-xs font-semibold text-white">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
            <div class="sidebar-user-info min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-slate-500">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</p>
            </div>
        </div>
    </div>
</div>
