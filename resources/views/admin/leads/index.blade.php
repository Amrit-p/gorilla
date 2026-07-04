@php
    $convertedOnly = $convertedOnly ?? false;
    $leadsListRoute = $convertedOnly ? route('admin.leads.converted') : route('admin.leads.index');
@endphp
<x-layouts.dashboard
    :title="$convertedOnly ? 'Converted to Customer' : 'Lead Management'"
    :subtitle="$convertedOnly ? 'Leads that have converted into paying customers.' : 'View, filter, and manage your sales leads in one place.'"
>
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
            :resetRoute="$leadsListRoute"
        />

        <div id="leads-table-container">
            @include('admin.leads.partials.table', ['leads' => $leads, 'convertedOnly' => $convertedOnly])
        </div>
    </div>

    <x-ui.modal id="lead-status-modal" title="Update Lead Status">
        <form id="lead-status-form" class="space-y-3">
            @csrf
            @method('PATCH')
            <input type="hidden" name="lead_id">
            <p class="bulk-lead-context hidden rounded-md bg-slate-50 px-3 py-2 text-xs text-slate-600"></p>
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

    <x-leads.import-modal />

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

        function refreshLeads(url = "{{ $leadsListRoute }}") {
            $.get(url, $('#lead-filters-form').serialize(), function (response) {
                $('#leads-table-container').html(response.html);
            });
        }

        $('#lead-filters-form').on('submit', function (event) {
            event.preventDefault();
            refreshLeads();
        });

        function openLeadStatusModal(ids) {
            $('#lead-status-form')[0].reset();
            $('#lead-status-form').data('leadIds', ids);
            $('#lead-status-form').find('[name="lead_id"]').val(ids.join(','));
            const $context = $('#lead-status-form').find('.bulk-lead-context');
            if (ids.length > 1) {
                $context.removeClass('hidden').text('Status update will apply to ' + ids.length + ' selected leads.');
            } else {
                $context.addClass('hidden').text('');
            }
            openModal('lead-status-modal');
        }

        $(document).on('click', '.status-lead', function () {
            openLeadStatusModal([Number($(this).data('id'))]);
        });

        $(document).on('click', '#lead-bulk-status', function () {
            const ids = typeof window.selectedLeadIds === 'function' ? window.selectedLeadIds() : [];
            if (!ids.length) return;
            openLeadStatusModal(ids);
        });

        $('#lead-status-form').on('submit', function (event) {
            event.preventDefault();
            const ids = $(this).data('leadIds') || [];
            if (!ids.length) return;
            $.ajax({
                url: "{{ url('/admin/leads') }}/" + ids.join(',') + "/status",
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

        $(document).on('click', '#lead-bulk-timeline', function () {
            const ids = typeof window.selectedLeadIds === 'function' ? window.selectedLeadIds() : [];
            if (!ids.length) return;
            $('#lead-note-form')[0].reset();
            $('#lead-note-form').find('[name="lead_id"]').val(ids.join(','));
            $('#lead-detail-content').html('<p class="text-slate-500">Adding a note will apply to ' + ids.length + ' selected leads.</p>');
            openModal('lead-detail-modal');
        });

        $('#lead-note-form').on('submit', function (event) {
            event.preventDefault();
            const id = $(this).find('[name="lead_id"]').val();
            const isBulk = id.indexOf(',') !== -1;
            $.ajax({
                url: "{{ url('/admin/leads') }}/" + id + "/notes",
                method: 'POST',
                data: $(this).serialize(),
                headers: { 'Accept': 'application/json' },
                success: function (res) {
                    showLeadAlert(res.message);
                    $('#lead-note-form').find('[name="note"]').val('');
                    if (isBulk) {
                        closeModal('lead-detail-modal');
                    } else {
                        $('.view-lead[data-id="' + id + '"]').first().trigger('click');
                    }
                },
                error: function (xhr) { showLeadAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Failed to add note.', true); }
            });
        });

        function deleteLeads(ids) {
            $.ajax({
                url: "{{ url('/admin/leads') }}/" + ids.join(','),
                method: 'POST',
                data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
                headers: { 'Accept': 'application/json' },
                success: function (res) { showLeadAlert(res.message); refreshLeads(); },
                error: function (xhr) { showLeadAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Failed to delete lead' + (ids.length > 1 ? 's' : '') + '.', true); }
            });
        }

        $(document).on('click', '.delete-lead', function () {
            if (!confirm('Delete this lead?')) return;
            deleteLeads([Number($(this).data('id'))]);
        });

        $(document).on('click', '#lead-bulk-delete', function () {
            const ids = typeof window.selectedLeadIds === 'function' ? window.selectedLeadIds() : [];
            if (!ids.length) return;
            if (!confirm('Delete ' + ids.length + ' selected leads?')) return;
            deleteLeads(ids);
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
