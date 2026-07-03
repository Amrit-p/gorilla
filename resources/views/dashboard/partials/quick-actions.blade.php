<div class="rounded-2xl border border-slate-200 bg-white p-5">
    <h2 class="text-base font-semibold text-slate-900">Quick actions</h2>
    <p class="mt-1 text-sm text-slate-600">Create records or jump to list views.</p>

    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @can('manage-leads')
            <a href="{{ route('admin.leads.create') }}" class="group rounded-xl border border-slate-200 p-4 transition hover:border-emerald-200 hover:shadow-sm">
                <p class="text-sm font-semibold text-slate-900 group-hover:text-slate-950">Add Lead</p>
                <p class="mt-1 text-xs text-slate-500">New lead intake form</p>
            </a>
        @endcan

        @can('manage-customers')
            <a href="{{ route('admin.clients.create') }}" class="group rounded-xl border border-slate-200 p-4 transition hover:border-slate-400 hover:shadow-sm">
                <p class="text-sm font-semibold text-slate-900 group-hover:text-slate-950">Add Customer</p>
                <p class="mt-1 text-xs text-slate-500">Manual client entry</p>
            </a>
        @endcan

        @can('create', \App\Models\Job::class)
            <a href="{{ route('admin.jobs.create') }}" class="group rounded-xl border border-slate-200 p-4 transition hover:border-slate-400 hover:shadow-sm">
                <p class="text-sm font-semibold text-slate-900 group-hover:text-slate-950">Create Job</p>
                <p class="mt-1 text-xs text-slate-500">Schedule a service job</p>
            </a>
        @endcan

        @can('manage-users')
            <a href="{{ route('admin.users.create') }}" class="group rounded-xl border border-slate-200 p-4 transition hover:border-slate-400 hover:shadow-sm">
                <p class="text-sm font-semibold text-slate-900 group-hover:text-slate-950">Add User</p>
                <p class="mt-1 text-xs text-slate-500">Mowers, sales, managers</p>
            </a>
        @endcan
    </div>

    <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-4 text-xs">
        @can('manage-leads')
            <a href="{{ route('admin.leads.index') }}" class="rounded-md border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50">View leads</a>
        @endcan
        @can('manage-customers')
            <a href="{{ route('admin.clients.index') }}" class="rounded-md border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50">View clients</a>
        @endcan
        @can('view-jobs')
            <a href="{{ route('admin.jobs.index') }}" class="rounded-md border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50">View jobs</a>
        @endcan
        @can('manage-users')
            <a href="{{ route('admin.users.index') }}" class="rounded-md border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50">View users</a>
        @endcan
    </div>
</div>
