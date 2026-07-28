<x-layouts.dashboard :title="'Employee Mobile Dashboard'">
    <div class="space-y-4">
        <div class="rounded-lg border border-slate-200 bg-white p-3">
            <p class="text-sm text-slate-600">Today’s route for <span class="font-semibold text-slate-800">{{ auth()->user()->name }}</span></p>
            <p class="text-xs text-slate-500">{{ now()->format('D, d M Y') }}</p>
        </div>

        <div id="employee-alert" class="hidden"></div>

        <div class="space-y-3">
            <h2 class="text-sm font-semibold text-slate-700">Today's Jobs</h2>
            @forelse ($todaysJobs as $job)
                <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">
                                #{{ $job->route_sequence }} {{ $job->customerDisplayName() }}
                            </p>
                            <p class="text-xs text-slate-500">{{ $job->client_address ?: 'No address' }}</p>
                            <p class="mt-1 text-xs text-slate-600">
                                Time: {{ $job->scheduled_time ? \Illuminate\Support\Carbon::parse($job->scheduled_time)->format('h:i A') : 'Flexible' }}
                            </p>
                            <p class="mt-1 text-xs text-slate-600">Phone: {{ $job->phone ?: ($job->client?->phone ?: '-') }}</p>
                            <p class="mt-2 text-xs text-slate-600">Site Instructions: {{ $job->site_instructions ?: 'None' }}</p>
                        </div>
                        <x-ui.badge type="warning">{{ $job->status }}</x-ui.badge>
                    </div>

                    <div class="mt-3 flex items-center gap-2">
                        <select class="job-status rounded-md border border-slate-300 px-2 py-1 text-xs" data-job-id="{{ $job->id }}">
                            @foreach (['Assigned','En Route','On Site','Completed','Cancelled'] as $status)
                                <option value="{{ $status }}" @selected($job->status === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                        <button class="update-job-status rounded-md bg-slate-900 px-3 py-1 text-xs text-white" data-job-id="{{ $job->id }}">
                            Update
                        </button>
                    </div>
                </div>
            @empty
                <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-500">
                    No assigned jobs for today.
                </div>
            @endforelse
        </div>

        <div class="space-y-2 rounded-lg border border-slate-200 bg-white p-3">
            <h2 class="text-sm font-semibold text-slate-700">Completed Jobs Ledger</h2>
            @forelse ($completedLedger as $job)
                <div class="flex items-center justify-between border-b border-slate-100 py-2 last:border-b-0">
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ $job->customerDisplayName() }}</p>
                        <p class="text-xs text-slate-500">{{ optional($job->scheduled_date)->format('d M Y') }} • {{ $job->client_address ?: '-' }}</p>
                    </div>
                    <x-ui.badge type="success">Completed</x-ui.badge>
                </div>
            @empty
                <p class="text-sm text-slate-500">No completed jobs yet.</p>
            @endforelse
        </div>
    </div>

    <script>
        function showEmployeeAlert(message, isError = false) {
            const baseClass = isError
                ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            $('#employee-alert').removeClass('hidden').attr('class', baseClass).text(message);
        }

        $('.update-job-status').on('click', function () {
            const jobId = $(this).data('job-id');
            const status = $('.job-status[data-job-id="' + jobId + '"]').val();

            $.ajax({
                url: "{{ url('/employee/mobile/jobs') }}/" + jobId + "/status",
                method: 'POST',
                data: { _token: "{{ csrf_token() }}", _method: 'PATCH', status: status },
                headers: { 'Accept': 'application/json' },
                success: function (res) {
                    showEmployeeAlert(res.message || 'Status updated.');
                    setTimeout(() => window.location.reload(), 400);
                },
                error: function (xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    showEmployeeAlert(Object.values(errors)[0]?.[0] || 'Unable to update job status.', true);
                }
            });
        });
    </script>
</x-layouts.dashboard>
