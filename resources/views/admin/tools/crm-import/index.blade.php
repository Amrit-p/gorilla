<x-layouts.dashboard :title="'CRM Data Import'" subtitle="Office Manager only — seed a Gorilla CRM JSON backup">
    <x-ui.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'CRM Data Import'],
    ]" />

    <div class="mx-auto max-w-4xl space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Import Gorilla CRM backup</h2>
            <p class="text-sm text-slate-600">
                Upload the Firestore JSON export. Customers become jobs (one per upcoming visit plus one per completed
                visit in the history), leads become leads, and mowers become users. This page is not linked from the
                navigation — reach it by URL.
            </p>
        </div>

        @if ($errors->any())
            <x-ui.alert type="error">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <form
            method="POST"
            action="{{ route('admin.tools.crm-import.store') }}"
            enctype="multipart/form-data"
            class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        >
            @csrf

            <div>
                <label for="backup_file" class="mb-1 block text-sm font-medium text-slate-700">Backup file</label>
                <input
                    id="backup_file"
                    type="file"
                    name="backup_file"
                    accept="application/json,.json"
                    required
                    class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-emerald-700 hover:file:bg-emerald-100"
                >
                <p class="mt-1 text-xs text-slate-500">A <code>.json</code> export with <code>crm_data</code> and <code>mowers</code> keys. Max 50 MB.</p>
            </div>

            <x-ui.input
                label="Mower email domain"
                name="mower_email_domain"
                :value="old('mower_email_domain', 'gorillamowing.co.nz')"
                required
            />
            <p class="-mt-3 text-xs text-slate-500">
                The export has no email addresses, so each imported mower gets
                <code>firstname.lastname@domain</code>. Every user created here is given the password
                <code>password</code>.
            </p>

            <label class="flex items-start gap-2 text-sm text-slate-700">
                <input type="checkbox" name="dry_run" value="1" class="mt-0.5" {{ old('dry_run') ? 'checked' : '' }}>
                <span>
                    <span class="font-medium">Dry run</span>
                    <span class="block text-xs text-slate-500">Parse, hash, and count everything, then roll the transaction back without saving.</span>
                </span>
            </label>

            <div class="flex items-center gap-3 border-t border-slate-200 pt-4">
                <x-ui.button type="submit">Upload &amp; seed</x-ui.button>
                <p class="text-xs text-slate-500">Re-running is safe: existing jobs and leads matching an address and date are skipped.</p>
            </div>
        </form>

        @if ($result)
            @php
                $labels = [
                    'users_created' => 'Users created',
                    'users_matched' => 'Users already present',
                    'leads_created' => 'Leads created',
                    'leads_skipped' => 'Leads skipped',
                    'jobs_created' => 'Upcoming jobs created',
                    'history_jobs_created' => 'History jobs created',
                    'jobs_skipped' => 'Jobs skipped (duplicate)',
                    'assignments_created' => 'Mower assignments created',
                    'lead_history_skipped' => 'Lead visit rows not imported',
                ];
            @endphp

            <div class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-base font-semibold text-slate-900">
                        {{ $result['dry_run'] ? 'Dry run summary' : 'Import summary' }}
                    </h3>
                    <x-ui.badge :type="$result['dry_run'] ? 'warning' : 'success'">
                        {{ $result['dry_run'] ? 'Nothing was saved' : 'Committed' }}
                    </x-ui.badge>
                </div>

                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">SHA-256 of uploaded file</p>
                    <p class="mt-1 break-all font-mono text-sm text-slate-900">{{ $result['sha256'] }}</p>
                    <dl class="mt-3 grid grid-cols-1 gap-2 text-xs text-slate-600 sm:grid-cols-3">
                        <div>
                            <dt class="font-medium text-slate-500">File</dt>
                            <dd class="break-all">{{ $result['filename'] ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">Exported at</dt>
                            <dd>{{ $result['exported_at'] ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">Records read</dt>
                            <dd>{{ $result['records']['crm_data'] }} crm_data · {{ $result['records']['mowers'] }} mowers</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <h4 class="mb-2 text-sm font-semibold text-slate-900">Records</h4>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($labels as $key => $label)
                            <div class="rounded-lg border border-slate-200 px-3 py-2">
                                <p class="text-xs text-slate-500">{{ $label }}</p>
                                <p class="text-lg font-semibold text-slate-900">{{ number_format($result['counts'][$key] ?? 0) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if (! empty($result['masters']))
                    <div>
                        <h4 class="mb-2 text-sm font-semibold text-slate-900">Master catalogs</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                                        <th class="py-2 pr-4 font-medium">Catalog</th>
                                        <th class="py-2 pr-4 font-medium">New entries</th>
                                        <th class="py-2 font-medium">Matched existing</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($result['masters'] as $catalog => $stats)
                                        <tr class="border-b border-slate-100">
                                            <td class="py-2 pr-4 text-slate-700">{{ Str::headline($catalog) }}</td>
                                            <td class="py-2 pr-4 text-slate-900">{{ $stats['created'] }}</td>
                                            <td class="py-2 text-slate-500">{{ $stats['matched'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if (! empty($result['warnings']))
                    <div>
                        <h4 class="mb-2 text-sm font-semibold text-slate-900">Warnings ({{ count($result['warnings']) }})</h4>
                        <ul class="max-h-60 space-y-1 overflow-y-auto rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                            @foreach ($result['warnings'] as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-layouts.dashboard>
