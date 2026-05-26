<x-layouts.dashboard :title="'Customer #' . $client->customer_unique_id" subtitle="{{ $client->name }}">
    <x-ui.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Customers', 'url' => route('admin.clients.index')],
        ['label' => '#' . $client->customer_unique_id],
    ]" />

    <div class="space-y-5">
        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">{{ $client->name }}</h2>
                <p class="text-sm text-slate-600">
                    Customer ID <span class="font-mono font-medium text-slate-800">#{{ $client->customer_unique_id }}</span>
                    @if ($client->lead_id)
                        • <span class="text-emerald-700">Lead conversion</span>
                    @endif
                </p>
            </div>
            <div class="flex gap-2">
                @can('manage-customers')
                    <a href="{{ route('admin.clients.edit', $client) }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">Edit</a>
                @endcan
                @can('view-jobs')
                    <a href="{{ route('admin.jobs.create') }}?client_id={{ $client->id }}" class="rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">Schedule job</a>
                @endcan
            </div>
        </div>

        @include('admin.clients.partials.stats-cards', ['statistics' => $statistics])

        <div class="rounded-2xl border border-slate-200 bg-white">
            <div class="flex border-b border-slate-200">
                <button
                    type="button"
                    class="customer-tab px-4 py-3 text-sm font-medium {{ $activeTab === 'details' ? 'border-b-2 border-emerald-600 text-emerald-700' : 'text-slate-600 hover:text-slate-900' }}"
                    data-tab="details"
                >
                    Details
                </button>
                <button
                    type="button"
                    class="customer-tab px-4 py-3 text-sm font-medium {{ $activeTab === 'jobs' ? 'border-b-2 border-emerald-600 text-emerald-700' : 'text-slate-600 hover:text-slate-900' }}"
                    data-tab="jobs"
                >
                    Jobs
                    <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $client->jobs_count ?? 0 }}</span>
                </button>
            </div>

            <div class="p-5">
                <div id="customer-tab-details" class="{{ $activeTab === 'details' ? '' : 'hidden' }}">
                    @include('admin.clients.partials.details-tab', ['client' => $client])
                </div>

                <div id="customer-tab-jobs" class="{{ $activeTab === 'jobs' ? '' : 'hidden' }}">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div id="customer-jobs-stats" class="flex gap-4 text-sm text-slate-600"></div>
                        <form id="customer-jobs-filter" class="flex flex-wrap gap-2">
                            <input type="text" name="job_search" placeholder="Search jobs..." class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                            <select name="job_status" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                                <option value="">All statuses</option>
                                @foreach ($jobStatuses as $jobStatus)
                                    <option value="{{ $jobStatus }}">{{ $jobStatus }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm hover:bg-slate-50">Filter</button>
                        </form>
                    </div>
                    <div id="customer-jobs-table">
                        <p class="text-sm text-slate-500">Loading jobs...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const customerJobsUrl = "{{ route('admin.clients.jobs', $client) }}";
        let jobsTabLoaded = {{ $activeTab === 'jobs' ? 'true' : 'false' }};

        function loadCustomerJobs(url) {
            const params = $('#customer-jobs-filter').length ? $('#customer-jobs-filter').serialize() : '';
            $.get(url || customerJobsUrl, params, function (res) {
                $('#customer-jobs-table').html(res.html);
                if (res.stats) {
                    $('#customer-jobs-stats').html(
                        '<span><strong>' + res.stats.filtered_total + '</strong> shown</span>' +
                        '<span><strong>' + res.stats.filtered_completed + '</strong> completed</span>'
                    );
                }
                jobsTabLoaded = true;
            });
        }

        function switchCustomerTab(tab) {
            $('.customer-tab').removeClass('border-b-2 border-emerald-600 text-emerald-700').addClass('text-slate-600');
            $('.customer-tab[data-tab="' + tab + '"]').addClass('border-b-2 border-emerald-600 text-emerald-700').removeClass('text-slate-600');
            $('#customer-tab-details, #customer-tab-jobs').addClass('hidden');
            $('#customer-tab-' + tab).removeClass('hidden');
            if (tab === 'jobs' && !jobsTabLoaded) {
                loadCustomerJobs();
            }
            const u = new URL(window.location.href);
            u.searchParams.set('tab', tab);
            window.history.replaceState({}, '', u);
        }

        $('.customer-tab').on('click', function () {
            switchCustomerTab($(this).data('tab'));
        });

        $('#customer-jobs-filter').on('submit', function (e) {
            e.preventDefault();
            loadCustomerJobs();
        });

        $(document).on('click', '#customer-jobs-table .pagination a', function (e) {
            e.preventDefault();
            loadCustomerJobs($(this).attr('href'));
        });

        @if ($activeTab === 'jobs')
            loadCustomerJobs();
        @endif
    </script>
</x-layouts.dashboard>
