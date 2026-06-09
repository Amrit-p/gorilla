<x-layouts.dashboard title="Contractors" subtitle="Manage contractors and their contracts.">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Contractors</h2>
                <p class="text-sm text-slate-600">Add and manage contractors.</p>
            </div>
            <button type="button" id="open-create-contractor"
                class="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Add Contractor
            </button>
        </div>

        <div id="contractor-alert" class="hidden"></div>

        <form id="contractor-filter-form" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ $filters['search'] }}"
                placeholder="Search name, phone, email…"
                class="rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" />
            <button type="submit"
                class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Search</button>
            @if ($filters['search'])
                <a href="{{ route('admin.contractors.index') }}"
                    class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Clear</a>
            @endif
        </form>

        <div id="contractors-table-container">
            @include('admin.contractors.partials.table', ['contractors' => $contractors])
        </div>
    </div>

    {{-- Create / Edit Contractor Modal --}}
    <x-ui.modal id="contractor-form-modal" title="Add Contractor">
        <form id="contractor-form" class="space-y-3">
            @csrf
            <input type="hidden" name="record_id" value="">
            @include('admin.contractors.partials.contractor-form')
            <x-ui.button type="submit" id="contractor-form-submit">Save</x-ui.button>
        </form>
    </x-ui.modal>

    <script>
        (function () {
            const routes = {
                index: '{{ route('admin.contractors.index') }}',
                store: '{{ route('admin.contractors.store') }}',
                show: '{{ url('admin/contractors') }}/__ID__',
                update: '{{ url('admin/contractors') }}/__ID__',
                detail: '{{ url('admin/contractors') }}/__ID__/detail',
            };

            function showAlert(message, isError = false) {
                const cls = isError
                    ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                    : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
                $('#contractor-alert').removeClass('hidden').attr('class', cls).text(message);
            }

            function recordUrl(template, id) {
                return template.replace('__ID__', id);
            }

            function openModal(id) { $('#' + id).removeClass('hidden').addClass('flex'); }
            function closeModal(id) { $('#' + id).addClass('hidden').removeClass('flex'); }

            $('[data-close-modal]').on('click', function () { closeModal($(this).data('close-modal')); });

            function fetchContractors(url) {
                $.get(url || routes.index, $('#contractor-filter-form').serialize(), function (res) {
                    $('#contractors-table-container').html(res.html);
                });
            }

            $('#contractor-filter-form').on('submit', function (e) {
                e.preventDefault();
                fetchContractors();
            });

            function resetForm(isEdit) {
                $('#contractor-form')[0].reset();
                $('#contractor-form [name="record_id"]').val('');
                $('#contractor-form-submit').text(isEdit ? 'Update' : 'Create');
                $('#contractor-form-modal h3').text(isEdit ? 'Edit Contractor' : 'Add Contractor');
            }

            $('#open-create-contractor').on('click', function () {
                resetForm(false);
                openModal('contractor-form-modal');
            });

            $(document).on('click', '.edit-contractor', function () {
                const id = $(this).data('id');
                $.get(recordUrl(routes.show, id), function (data) {
                    resetForm(true);
                    $('#contractor-form [name="record_id"]').val(data.id);
                    $('#contractor-form [name="name"]').val(data.name);
                    $('#contractor-form [name="phone"]').val(data.phone);
                    $('#contractor-form [name="email"]').val(data.email || '');
                    openModal('contractor-form-modal');
                });
            });

            $('#contractor-form').on('submit', function (e) {
                e.preventDefault();
                const id = $(this).find('[name="record_id"]').val();
                const isEdit = Boolean(id);
                const url = isEdit ? recordUrl(routes.update, id) : routes.store;

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: $(this).serialize() + (isEdit ? '&_method=PATCH' : ''),
                    headers: { 'Accept': 'application/json' },
                    success: function (res) {
                        closeModal('contractor-form-modal');
                        showAlert(res.message);
                        fetchContractors();
                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        showAlert(Object.values(errors)[0]?.[0] || 'Unable to save contractor.', true);
                    },
                });
            });

            $(document).on('click', '#contractors-table-container .pagination a', function (e) {
                e.preventDefault();
                fetchContractors($(this).attr('href'));
            });
        })();
    </script>
</x-layouts.dashboard>
