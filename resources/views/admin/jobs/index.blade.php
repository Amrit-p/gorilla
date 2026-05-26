<x-layouts.dashboard :title="'Job Scheduling'">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Jobs</h2>
                <p class="text-sm text-slate-600">Track workflow, assign mowers, and filter by Today / Upcoming / Done / Hold.</p>
            </div>
            @can('manage-job-records')
                <a href="{{ route('admin.jobs.create') }}" class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Create Job</a>
            @endcan
        </div>

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div id="job-alert" class="hidden"></div>

        @include('admin.jobs.partials.filter-bar', ['filters' => $filters])

        <div id="jobs-table-container">
            @include('admin.jobs.partials.table', ['jobs' => $jobs])
        </div>
    </div>

    <x-ui.modal id="assign-job-modal" title="Assign Mowers">
        <form id="assign-job-form" class="space-y-3">
            @csrf
            <input type="hidden" name="job_id">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Mowers</label>
                <select name="employee_ids[]" multiple class="min-h-32 w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->efficiency ?? 'Average' }})</option>
                    @endforeach
                </select>
            </div>
            <x-ui.button type="submit">Assign</x-ui.button>
        </form>
    </x-ui.modal>

    <x-ui.modal id="status-job-modal" title="Update Job Status">
        <form id="status-job-form" class="space-y-3">
            @csrf
            <input type="hidden" name="job_id">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                <select name="status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($workflowStatuses as $workflowStatus)
                        <option value="{{ $workflowStatus }}">{{ $workflowStatus }}</option>
                    @endforeach
                </select>
            </div>
            <x-ui.button type="submit">Update Status</x-ui.button>
        </form>
    </x-ui.modal>

    <script>
        function showJobAlert(message, isError = false) {
            const baseClass = isError
                ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            $('#job-alert').removeClass('hidden').attr('class', baseClass).text(message);
        }
        function openModal(id) { $('#' + id).removeClass('hidden').addClass('flex'); }
        function closeModal(id) { $('#' + id).addClass('hidden').removeClass('flex'); }
        $('[data-close-modal]').on('click', function () { closeModal($(this).data('close-modal')); });

        function refreshJobs(url = "{{ route('admin.jobs.index') }}") {
            $.get(url, $('#job-filter-form').serialize(), function (res) {
                $('#jobs-table-container').html(res.html);
            }, 'json');
        }

        $('.job-list-scope').on('click', function () {
            const scope = $(this).data('list-scope');
            $('#job-list-scope').val(scope);
            $('.job-list-scope').removeClass('border-emerald-600 bg-emerald-50 text-emerald-800 border-slate-800 bg-slate-800 text-white');
            $(this).addClass(scope === '' ? 'border-slate-800 bg-slate-800 text-white' : 'border-emerald-600 bg-emerald-50 text-emerald-800');
            refreshJobs();
        });

        $('#job-filter-form').on('submit', function (e) { e.preventDefault(); refreshJobs(); });
        $(document).on('click', '#jobs-table-container .pagination a', function (e) {
            e.preventDefault();
            refreshJobs($(this).attr('href'));
        });

        $(document).on('click', '.assign-job', function () {
            $('#assign-job-form')[0].reset();
            $('#assign-job-form').find('[name="job_id"]').val($(this).data('id'));
            openModal('assign-job-modal');
        });
        $('#assign-job-form').on('submit', function (e) {
            e.preventDefault();
            const id = $(this).find('[name="job_id"]').val();
            $.ajax({
                url: "{{ url('/admin/jobs') }}/" + id + "/assign",
                method: 'POST',
                data: $(this).serialize(),
                headers: { Accept: 'application/json' },
                success: function (res) { closeModal('assign-job-modal'); showJobAlert(res.message); refreshJobs(); },
                error: function (xhr) { showJobAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Failed to assign.', true); }
            });
        });

        $(document).on('click', '.status-job', function () {
            $('#status-job-form').find('[name="job_id"]').val($(this).data('id'));
            $('#status-job-form').find('[name="status"]').val($(this).data('status') || 'Started');
            openModal('status-job-modal');
        });
        $('#status-job-form').on('submit', function (e) {
            e.preventDefault();
            const id = $(this).find('[name="job_id"]').val();
            $.ajax({
                url: "{{ url('/admin/jobs') }}/" + id + "/status",
                method: 'POST',
                data: $(this).serialize(),
                headers: { Accept: 'application/json' },
                success: function (res) { closeModal('status-job-modal'); showJobAlert(res.message); refreshJobs(); },
                error: function (xhr) { showJobAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Failed to update status.', true); }
            });
        });

        $(document).on('click', '.delete-job', function () {
            const id = $(this).data('id');
            if (!confirm('Delete this job?')) return;
            $.ajax({
                url: "{{ url('/admin/jobs') }}/" + id,
                method: 'POST',
                data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
                headers: { Accept: 'application/json' },
                success: function (res) { showJobAlert(res.message); refreshJobs(); },
                error: function () { showJobAlert('Failed to delete job.', true); }
            });
        });
    </script>
</x-layouts.dashboard>
