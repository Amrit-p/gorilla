<x-layouts.dashboard :title="'Edit Job #'.$job->id" subtitle="Update scheduling and assignments">
    <x-ui.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Jobs', 'url' => route('admin.jobs.index')],
        ['label' => 'Job #'.$job->id, 'url' => route('admin.jobs.show', $job)],
        ['label' => 'Edit'],
    ]" />

    <div class="mx-auto max-w-6xl space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Edit Job</h2>
                <p class="text-sm text-slate-600">{{ $job->client?->name }} — <x-jobs.status-badge :status="$job->status" /></p>
            </div>
            <a href="{{ route('admin.jobs.show', $job) }}" class="text-sm text-emerald-700 hover:underline">View job</a>
        </div>

        <div id="job-form-alert" class="hidden"></div>

        <form
            id="job-form"
            method="POST"
            action="{{ route('admin.jobs.update', $job) }}"
            enctype="multipart/form-data"
            novalidate
            class="js-validate-form grid grid-cols-1 gap-4 rounded-2xl border border-slate-200 bg-white p-6 sm:grid-cols-2"
            data-validate="job-edit"
        >
            @csrf
            @method('PATCH')
            <input type="hidden" name="is_recurring" value="{{ $job->is_recurring ? '1' : '0' }}">
            <input type="hidden" name="recurrence_pattern" value="{{ $job->recurrence_pattern }}">
            <input type="hidden" name="route_sequence" value="{{ $job->route_sequence }}">
            <input type="hidden" name="priority" value="{{ $job->priority }}">
            @include('admin.jobs.partials.form-tabs', ['job' => $job, 'submitLabel' => 'Update Job'])
        </form>
    </div>

    @push('scripts')
        <script>
            window.jobFormRoutes = {
                workloads: @json(route('admin.jobs.mower-workloads')),
                suggestions: @json(route('admin.jobs.mower-suggestions')),
            };
        </script>
        <script src="{{ asset('js/job-form.js') }}?v={{ @filemtime(public_path('js/job-form.js')) ?: 1 }}"></script>
        <script>
            $('#job-form').on('submit', function (event) {
                event.preventDefault();
                const $form = $(this);
                if (!$form.data('validator') && window.CrmFormValidation) {
                    window.CrmFormValidation.initForm($form);
                }
                const isValid =
                    typeof window.validateAllJobSteps === 'function'
                        ? window.validateAllJobSteps()
                        : $form.valid();
                if (!isValid) {
                    return;
                }

                const formData = new FormData(this);
                formData.append('_method', 'PATCH');

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .done(function (response) {
                        window.location.href = response.redirect || "{{ route('admin.jobs.show', $job) }}";
                    })
                    .fail(function (xhr) {
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            $form.validate().showErrors(
                                Object.fromEntries(
                                    Object.entries(xhr.responseJSON.errors).map(([k, v]) => [k, v[0]])
                                )
                            );
                        }
                    });
            });
        </script>
    @endpush
</x-layouts.dashboard>
