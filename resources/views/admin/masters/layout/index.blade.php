<x-layouts.dashboard :title="$title">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">{{ $title }}</h2>
                <p class="text-sm text-slate-600">Manage {{ $title }} used across leads, clients, and jobs.</p>
            </div>
            <button type="button" id="open-create-master" class="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Add {{ ucfirst($singularLabel) }}
            </button>
        </div>

        <div id="master-alert" class="hidden"></div>

        <x-masters.filter-bar :filters="$filters" />

        <div id="master-table-container">
            @include($tablePartial, ['records' => $records])
        </div>
    </div>

    <x-ui.modal id="master-form-modal" :title="'Add ' . ucfirst($singularLabel)">
        <form id="master-form" class="space-y-3">
            @csrf
            <input type="hidden" name="record_id" value="">
            @include($formFieldsPartial, ['showColorCode' => $showColorCode])
            <x-ui.button type="submit" id="master-form-submit">Save</x-ui.button>
        </form>
    </x-ui.modal>

    <script>
        (function () {
            const routes = @json($routes);
            const singularLabel = @json($singularLabel);

            function recordUrl(template, id) {
                return template.replace('__ID__', id);
            }

            function showMasterAlert(message, isError = false) {
                const baseClass = isError
                    ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                    : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
                $('#master-alert').removeClass('hidden').attr('class', baseClass).text(message);
            }

            function closeModal(id) {
                $('#' + id).addClass('hidden').removeClass('flex');
            }

            function openModal(id) {
                $('#' + id).removeClass('hidden').addClass('flex');
            }

            $('[data-close-modal]').on('click', function () {
                closeModal($(this).data('close-modal'));
            });

            function fetchRecords(url = routes.index) {
                $.get(url, $('#master-filter-form').serialize(), function (response) {
                    $('#master-table-container').html(response.html);
                });
            }

            $('#master-filter-form').on('submit', function (event) {
                event.preventDefault();
                fetchRecords();
            });

            $('#master-filter-form select').on('change', function () {
                fetchRecords();
            });

            function resetForm(isEdit = false) {
                const $form = $('#master-form');
                $form[0].reset();
                $form.find('[name="record_id"]').val('');
                $form.find('[name="is_active"]').val('1');
                $('#master-form-submit').text(isEdit ? 'Update' : 'Create');
                $('#master-form-modal h3').text((isEdit ? 'Edit ' : 'Add ') + singularLabel);
            }

            $('#open-create-master').on('click', function () {
                resetForm(false);
                openModal('master-form-modal');
            });

            $(document).on('click', '.edit-master', function () {
                const id = $(this).data('id');
                $.get(recordUrl(routes.show, id), function (record) {
                    resetForm(true);
                    const $form = $('#master-form');
                    $form.find('[name="record_id"]').val(record.id);
                    $form.find('[name="name"]').val(record.name);
                    if ($form.find('[name="color_code"]').length) {
                        $form.find('[name="color_code"]').val(record.color_code || '#64748b');
                    }
                    $form.find('[name="sort_order"]').val(record.sort_order ?? 0);
                    $form.find('[name="is_active"]').val(record.is_active ? '1' : '0');
                    openModal('master-form-modal');
                });
            });

            $('#master-form').on('submit', function (event) {
                event.preventDefault();
                const $form = $(this);
                const id = $form.find('[name="record_id"]').val();
                const isEdit = Boolean(id);
                const url = isEdit ? recordUrl(routes.update, id) : routes.store;

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: $form.serialize() + (isEdit ? '&_method=PATCH' : ''),
                    headers: { 'Accept': 'application/json' },
                    success: function (response) {
                        closeModal('master-form-modal');
                        showMasterAlert(response.message);
                        fetchRecords();
                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        showMasterAlert(Object.values(errors)[0]?.[0] || 'Unable to save record.', true);
                    }
                });
            });

            $(document).on('click', '.toggle-master-status', function () {
                const id = $(this).data('id');
                const isActive = Number($(this).data('active')) === 1 ? 0 : 1;
                $.ajax({
                    url: recordUrl(routes.status, id),
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'PATCH', is_active: isActive },
                    headers: { 'Accept': 'application/json' },
                    success: function (response) {
                        showMasterAlert(response.message);
                        fetchRecords();
                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        showMasterAlert(Object.values(errors)[0]?.[0] || 'Unable to update status.', true);
                    }
                });
            });

            $(document).on('click', '.delete-master', function () {
                const id = $(this).data('id');
                if (!confirm('Delete this ' + singularLabel + '?')) {
                    return;
                }
                $.ajax({
                    url: recordUrl(routes.destroy, id),
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    headers: { 'Accept': 'application/json' },
                    success: function (response) {
                        showMasterAlert(response.message);
                        fetchRecords();
                    },
                    error: function () {
                        showMasterAlert('Unable to delete record.', true);
                    }
                });
            });

            $(document).on('click', '#master-table-container .pagination a', function (event) {
                event.preventDefault();
                fetchRecords($(this).attr('href'));
            });
        })();
    </script>
</x-layouts.dashboard>
