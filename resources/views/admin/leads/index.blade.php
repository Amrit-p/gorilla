<x-layouts.dashboard :title="'Lead Management'" :subtitle="'View, filter, and manage your sales leads in one place.'">
    <div class="space-y-5">

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div id="lead-alert" class="hidden"></div>

        <x-leads.filter-bar
            :filters="$filters"
            :statuses="$statuses"
            :salesUsers="$salesUsers"
            :zones="$zones"
            :recurrences="$recurrences"
            :resetRoute="route('admin.leads.index')"
        />

        <div id="leads-table-container">
            @include('admin.leads.partials.table', ['leads' => $leads])
        </div>
    </div>

    <x-ui.modal id="lead-status-modal" title="Update Lead Status">
        <form id="lead-status-form" class="space-y-3">
            @csrf
            @method('PATCH')
            <input type="hidden" name="lead_id">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                <select name="status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Timeline Note (optional)</label>
                <textarea name="note" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"></textarea>
            </div>
            <x-ui.button type="submit">Update Status</x-ui.button>
        </form>
    </x-ui.modal>

    <x-ui.modal id="lead-detail-modal" title="Lead Timeline">
        <div id="lead-detail-content" class="space-y-3 text-sm text-slate-700"></div>
        <form id="lead-note-form" class="mt-4 space-y-2">
            @csrf
            <input type="hidden" name="lead_id">
            <textarea name="note" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" placeholder="Add timeline note"></textarea>
            <x-ui.button type="submit">Add Note</x-ui.button>
        </form>
    </x-ui.modal>

    <x-ui.modal id="lead-import-modal" title="Import Leads">
        <form id="lead-import-form" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <a
                href="{{ route('admin.leads.import.sample') }}"
                class="inline-flex items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800 hover:bg-emerald-100"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                </svg>
                Download sample Excel
            </a>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">File (Excel)</label>
                <input type="file" name="import_file" accept=".xlsx" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" required>
            </div>
            <div id="import-progress" class="hidden space-y-1">
                <div class="flex justify-between text-xs text-slate-500">
                    <span id="import-progress-label">Uploading…</span>
                    <span id="import-progress-pct">0%</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                    <div id="import-progress-bar" class="h-2 rounded-full bg-emerald-500 transition-all duration-200" style="width:0%"></div>
                </div>
            </div>
            <div id="import-result" class="hidden space-y-2">
                <div id="import-result-success" class="hidden rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700"></div>
                <div id="import-result-partial" class="hidden space-y-2">
                    <p id="import-failures-summary" class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-700"></p>
                    <ul id="import-failures-list" class="max-h-48 overflow-y-auto rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800 space-y-1 list-disc list-inside"></ul>
                </div>
            </div>
            <x-ui.button type="submit">Import</x-ui.button>
        </form>
    </x-ui.modal>

    @include('admin.partials.dropdown-script')

    <script>
        function showLeadAlert(message, isError = false) {
            const baseClass = isError
                ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            $('#lead-alert').removeClass('hidden').attr('class', baseClass).text(message);
        }

        function openModal(id) { $('#' + id).removeClass('hidden').addClass('flex'); }
        function closeModal(id) { $('#' + id).addClass('hidden').removeClass('flex'); }
        $('[data-close-modal]').on('click', function () { closeModal($(this).data('close-modal')); });

        function refreshLeads(url = "{{ route('admin.leads.index') }}") {
            $.get(url, $('#lead-filters-form').serialize(), function (response) {
                $('#leads-table-container').html(response.html);
            });
        }

        $('#lead-filters-form').on('submit', function (event) {
            event.preventDefault();
            refreshLeads();
        });

        $('#open-import-modal').on('click', function () {
            $('#lead-import-form')[0].reset();
            $('#import-result').addClass('hidden');
            $('#import-progress').addClass('hidden');
            openModal('lead-import-modal');
        });

        $(document).on('click', '.status-lead', function () {
            $('#lead-status-form')[0].reset();
            $('#lead-status-form').find('[name="lead_id"]').val($(this).data('id'));
            openModal('lead-status-modal');
        });
        $('#lead-status-form').on('submit', function (event) {
            event.preventDefault();
            const id = $(this).find('[name="lead_id"]').val();
            $.ajax({
                url: "{{ url('/admin/leads') }}/" + id + "/status",
                method: 'POST',
                data: $(this).serialize(),
                headers: { 'Accept': 'application/json' },
                success: function (res) { closeModal('lead-status-modal'); showLeadAlert(res.message); refreshLeads(); },
                error: function (xhr) { showLeadAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Failed to update status.', true); }
            });
        });

        $(document).on('click', '.view-lead', function () {
            const id = $(this).data('id');
            $.get("{{ url('/admin/leads') }}/" + id, function (res) {
                const notes = (res.notes || []).map(n => `<div class="rounded-md border border-slate-200 p-2"><p>${n.note}</p><p class="mt-1 text-xs text-slate-500">${n.user_name} • ${n.created_at}</p></div>`);
                $('#lead-detail-content').html(notes.length ? notes.join('') : '<p class="text-slate-500">No timeline notes yet.</p>');
                $('#lead-note-form').find('[name="lead_id"]').val(res.lead.id);
                openModal('lead-detail-modal');
            });
        });

        $('#lead-note-form').on('submit', function (event) {
            event.preventDefault();
            const id = $(this).find('[name="lead_id"]').val();
            $.ajax({
                url: "{{ url('/admin/leads') }}/" + id + "/notes",
                method: 'POST',
                data: $(this).serialize(),
                headers: { 'Accept': 'application/json' },
                success: function (res) { showLeadAlert(res.message); $('.view-lead[data-id="' + id + '"]').first().trigger('click'); $('#lead-note-form').find('[name="note"]').val(''); },
                error: function (xhr) { showLeadAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Failed to add note.', true); }
            });
        });

        $(document).on('click', '.delete-lead', function () {
            const id = $(this).data('id');
            if (!confirm('Delete this lead?')) return;
            $.ajax({
                url: "{{ url('/admin/leads') }}/" + id,
                method: 'POST',
                data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
                headers: { 'Accept': 'application/json' },
                success: function (res) { showLeadAlert(res.message); refreshLeads(); },
                error: function (xhr) { showLeadAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Failed to delete lead.', true); }
            });
        });

        $('#lead-import-form').on('submit', function (event) {
            event.preventDefault();
            const $form = $(this);
            const $btn  = $form.find('[type="submit"]');

            $('#import-progress-bar').css('width', '0%');
            $('#import-progress-pct').text('0%');
            $('#import-progress-label').text('Uploading…');
            $('#import-progress').removeClass('hidden');
            $('#import-result').addClass('hidden');
            $btn.prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.leads.import') }}",
                method: 'POST',
                data: new FormData($form[0]),
                processData: false,
                contentType: false,
                headers: { 'Accept': 'application/json' },
                xhr: function () {
                    const xhr = new XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function (e) {
                        if (!e.lengthComputable) return;
                        const pct = Math.round((e.loaded / e.total) * 100);
                        $('#import-progress-bar').css('width', pct + '%');
                        $('#import-progress-pct').text(pct + '%');
                        if (pct === 100) $('#import-progress-label').text('Processing…');
                    });
                    return xhr;
                },
                success: function (res) {
                    $('#import-progress').addClass('hidden');
                    $btn.prop('disabled', false);
                    $form[0].reset();
                    refreshLeads();

                    $('#import-result-success').addClass('hidden');
                    $('#import-result-partial').addClass('hidden');

                    if (res.failures && res.failures.length > 0) {
                        const items = res.failures.map(f =>
                            `<li><strong>Row ${f.row} (${$('<span>').text(f.identifier).html()}):</strong> ${$('<span>').text(f.reason).html()}</li>`
                        ).join('');
                        $('#import-failures-summary').text(res.imported + ' imported successfully, ' + res.failed + ' failed:');
                        $('#import-failures-list').html(items);
                        $('#import-result-partial').removeClass('hidden');
                    } else {
                        $('#import-result-success').text(res.message).removeClass('hidden');
                    }

                    $('#import-result').removeClass('hidden');
                },
                error: function (xhr) {
                    $('#import-progress').addClass('hidden');
                    $btn.prop('disabled', false);
                    showLeadAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Import failed.', true);
                }
            });
        });

        $(document).on('click', '#leads-table-container .pagination a', function (event) {
            event.preventDefault();
            refreshLeads($(this).attr('href'));
        });

        crmDropdown('.lead-actions-btn', '.lead-actions-menu');

        // Keep export links in sync with active filters
        function syncExportLinks() {
            const params = $('#lead-filters-form').serialize();
            $('#export-excel-link').attr('href', "{{ route('admin.leads.export.excel') }}" + (params ? '?' + params : ''));
            $('#export-pdf-link').attr('href',   "{{ route('admin.leads.export.pdf') }}"   + (params ? '?' + params : ''));
        }
        syncExportLinks();
        $('#lead-filters-form').on('change input', syncExportLinks);
    </script>
</x-layouts.dashboard>
