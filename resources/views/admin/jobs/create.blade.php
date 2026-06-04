<x-layouts.dashboard :title="'Create Job'" subtitle="Tab-based scheduling with smart mower assignment">
    <x-ui.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Jobs', 'url' => route('admin.jobs.index')],
        ['label' => 'Create Job'],
    ]" />

    <div class="mx-auto max-w-6xl space-y-5">
        <x-jobs.remarks-card :empty="true" />
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Create Job</h2>
            <p class="text-sm text-slate-600">Complete each step in order. Review mower assignment on the final step, then submit.</p>
        </div>

        <div id="job-form-alert" class="hidden"></div>

        <form
            id="job-form"
            method="POST"
            action="{{ route('admin.jobs.store') }}"
            enctype="multipart/form-data"
            novalidate
            class="js-validate-form grid grid-cols-1 gap-4 rounded-2xl border border-slate-200 bg-white p-6 sm:grid-cols-2"
            data-validate="job"
        >
            @csrf
            @include('admin.jobs.partials.form-tabs', ['submitLabel' => 'Create Job'])
        </form>

    </div>

    @push('scripts')
        <script>
            window.jobFormRoutes = {
                workloads: @json(route('admin.jobs.mower-workloads')),
                suggestions: @json(route('admin.jobs.mower-suggestions')),
                clientRemarks: @json(route('admin.jobs.client-remarks')),
            };
        </script>
        <script src="{{ asset('js/job-form.js') }}?v={{ @filemtime(public_path('js/job-form.js')) ?: 1 }}"></script>
        <script src="{{ asset('js/job-remarks.js') }}?v={{ @filemtime(public_path('js/job-remarks.js')) ?: 1 }}"></script>
        <script>
            function showJobFormAlert(message, isError) {
                const baseClass = isError
                    ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                    : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
                $('#job-form-alert').removeClass('hidden').attr('class', baseClass).text(message);
            }

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

                const $submit = $('#job-form-submit').prop('disabled', true);
                const formData = new FormData(this);

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .done(function (response) {
                        showJobFormAlert(response.message || 'Job saved.');
                        window.setTimeout(function () {
                            window.location.href = response.redirect || "{{ route('admin.jobs.index') }}";
                        }, 500);
                    })
                    .fail(function (xhr) {
                        $submit.prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            const validator = $form.validate();
                            const mapped = {};
                            Object.keys(xhr.responseJSON.errors).forEach(function (key) {
                                mapped[key] = xhr.responseJSON.errors[key][0];
                            });
                            validator.showErrors(mapped);
                            showJobFormAlert('Please fix the highlighted fields.', true);
                            return;
                        }
                        showJobFormAlert(xhr.responseJSON?.message || 'Unable to save job.', true);
                    });
            });
        </script>
    @endpush
</x-layouts.dashboard>
