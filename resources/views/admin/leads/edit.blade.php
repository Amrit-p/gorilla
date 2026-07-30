<x-layouts.dashboard :title="'Edit Lead'" subtitle="Update lead intake details">
    <x-ui.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Leads', 'url' => route('admin.leads.index')],
        ['label' => 'Edit Lead'],
    ]" />

    <div class="mx-auto max-w-6xl space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Edit Lead</h2>
            <p class="text-sm text-slate-600">{{ $lead->client_name ?: $lead->address }}</p>
            @if ($lead->converted_at)
                <p class="mt-1 text-xs text-emerald-700">Converted to job on {{ $lead->converted_at->format('M d, Y') }}</p>
            @endif
        </div>

        <div id="lead-form-alert" class="hidden"></div>

        <form
            id="lead-form"
            method="POST"
            action="{{ route('admin.leads.update', $lead) }}"
            novalidate
            class="js-validate-form grid grid-cols-1 gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:grid-cols-2"
            data-validate="lead-edit"
        >
            @csrf
            @method('PATCH')
            @include('admin.leads.partials.form-fields', ['lead' => $lead])
            @include('admin.leads.partials.form-fields-workflow', ['lead' => $lead])
            <div class="flex gap-3 sm:col-span-2">
                <x-ui.button type="submit" id="lead-form-submit">Update Lead</x-ui.button>
                <a href="{{ route('admin.leads.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function showLeadFormAlert(message, isError) {
                const baseClass = isError
                    ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                    : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
                $('#lead-form-alert').removeClass('hidden').attr('class', baseClass).text(message);
            }

            $(function () {
                $('#lead-form').on('submit', function (event) {
                    event.preventDefault();
                    const $form = $(this);
                    if (!$form.data('validator') && window.CrmFormValidation) {
                        window.CrmFormValidation.initForm($form);
                    }
                    if (!$form.valid()) {
                        return;
                    }

                    const $submit = $('#lead-form-submit').prop('disabled', true);
                    $.ajax({
                        url: $form.attr('action'),
                        method: 'POST',
                        data: $form.serialize(),
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    })
                        .done(function (response) {
                            showLeadFormAlert(response.message || 'Lead updated.');
                            window.setTimeout(function () {
                                window.location.href = response.redirect || "{{ route('admin.leads.index') }}";
                            }, 600);
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
                                showLeadFormAlert('Please fix the highlighted fields.', true);
                                return;
                            }
                            showLeadFormAlert(xhr.responseJSON?.message || 'Unable to update lead.', true);
                        });
                });
            });
        </script>
    @endpush
</x-layouts.dashboard>
