<x-layouts.dashboard :title="'Lead Management'">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Leads</h2>
                <p class="text-sm text-slate-600">Manage leads, assignments, notes, status transitions, and CSV import.</p>
            </div>
            <div class="flex gap-2">
                @can('manage-leads')
                    <button id="open-import-modal" type="button" class="rounded-md border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">Import CSV</button>
                    <a href="{{ route('admin.leads.create') }}" class="rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">Add Lead</a>
                @endcan
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div id="lead-alert" class="hidden"></div>

        <form id="lead-filters-form" class="grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-3 lg:grid-cols-5">
            <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search leads..." class="rounded-md border border-slate-300 px-3 py-2 text-sm">
            <select name="status" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <select name="assigned_sales_user_id" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">All assignees</option>
                @foreach ($salesUsers as $salesUser)
                    <option value="{{ $salesUser->id }}" @selected($filters['assigned_sales_user_id'] == $salesUser->id)>{{ $salesUser->name }}</option>
                @endforeach
            </select>
            <select name="zone_id" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">All zones</option>
                @foreach ($zones as $zone)
                    <option value="{{ $zone->id }}" @selected($filters['zone_id'] == $zone->id)>{{ $zone->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50">Apply Filters</button>
        </form>

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

    <x-ui.modal id="lead-import-modal" title="Import Leads from CSV">
        <form id="lead-import-form" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <a
                href="{{ route('admin.leads.import.sample') }}"
                class="inline-flex items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800 hover:bg-emerald-100"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                </svg>
                Download sample CSV
            </a>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">CSV file</label>
                <input type="file" name="csv_file" accept=".csv,.txt" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" required>
            </div>
            <x-ui.button type="submit">Import</x-ui.button>
        </form>
    </x-ui.modal>

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

        $('#open-import-modal').on('click', function () { openModal('lead-import-modal'); });

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
            const formData = new FormData(this);
            $.ajax({
                url: "{{ route('admin.leads.import') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'Accept': 'application/json' },
                success: function (res) { closeModal('lead-import-modal'); showLeadAlert(res.message); refreshLeads(); },
                error: function (xhr) { showLeadAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'CSV import failed.', true); }
            });
        });

        $(document).on('click', '#leads-table-container .pagination a', function (event) {
            event.preventDefault();
            refreshLeads($(this).attr('href'));
        });

        // Action dropdown
        let $openLeadMenu = null;

        function closeLeadMenu() {
            if ($openLeadMenu) {
                $openLeadMenu.addClass('hidden').css({ position: '', top: '', left: '', zIndex: '' });
                $openLeadMenu = null;
            }
        }

        $(document).on('click', '.lead-actions-btn', function (e) {
            e.stopPropagation();
            const $btn = $(this);
            const $menu = $btn.siblings('.lead-actions-menu');

            if ($openLeadMenu && $openLeadMenu.is($menu)) {
                closeLeadMenu();
                return;
            }

            closeLeadMenu();

            // Render off-screen first to measure actual dimensions
            $menu.css({ position: 'fixed', top: '-9999px', left: '-9999px', zIndex: 9999 }).removeClass('hidden');

            const menuH = $menu.outerHeight();
            const menuW = $menu.outerWidth();
            const rect  = $btn[0].getBoundingClientRect();
            const vw    = window.innerWidth;
            const vh    = window.innerHeight;

            let top  = rect.bottom + 4;
            let left = rect.right - menuW;

            if (top + menuH > vh - 8) top = rect.top - menuH - 4;
            if (top < 8) top = 8;
            if (left < 8) left = 8;
            if (left + menuW > vw - 8) left = vw - menuW - 8;

            $menu.css({ top: top + 'px', left: left + 'px' });
            $openLeadMenu = $menu;
        });

        $(document).on('click', function () { closeLeadMenu(); });
        $(document).on('click', '.lead-actions-menu a, .lead-actions-menu button', function () { closeLeadMenu(); });
        $(window).on('scroll', closeLeadMenu);
        $('#leads-table-container').on('scroll', closeLeadMenu);
    </script>
</x-layouts.dashboard>
