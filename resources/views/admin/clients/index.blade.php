<x-layouts.dashboard :title="'Customer Management'" :subtitle="'Search, filter, and manage your customers with detailed profiles and job history.'">
    <div class="space-y-5">


        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div id="client-alert" class="hidden"></div>

        @include('admin.clients.partials.filter-bar', ['filters' => $filters])

        <div id="clients-table-container">
            @include('admin.clients.partials.table', ['clients' => $clients])
        </div>
    </div>

    <x-clients.import-modal />

    @include('admin.partials.dropdown-script')

    <style>
        @keyframes clients-spin {
            to { transform: rotate(360deg); }
        }
        #clients-table-container {
            position: relative;
        }
        #clients-loading-overlay {
            position: absolute;
            inset: 0;
            z-index: 20;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
        }
        #clients-loading-overlay .clients-spinner {
            width: 28px;
            height: 28px;
            border: 2.5px solid #e0e7ff;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: clients-spin 0.7s linear infinite;
        }
    </style>

    <script>
        function openModal(id) { $('#' + id).removeClass('hidden').addClass('flex'); }
        function closeModal(id) { $('#' + id).addClass('hidden').removeClass('flex'); }
        $('[data-close-modal]').on('click', function () { closeModal($(this).data('close-modal')); });

        function showClientsLoading() {
            if ($('#clients-loading-overlay').length) return;
            $('#clients-table-container').append(
                '<div id="clients-loading-overlay">' +
                '<div style="display:flex;flex-direction:column;align-items:center;gap:10px;">' +
                '<div class="clients-spinner"></div>' +
                '<span style="font-size:0.7rem;font-weight:500;color:#64748b;letter-spacing:0.05em;">Loading…</span>' +
                '</div>' +
                '</div>'
            );
        }

        function showClientAlert(message, isError = false) {
            const baseClass = isError
                ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            $('#client-alert').removeClass('hidden').attr('class', baseClass).text(message);
        }

        function refreshClients(url = "{{ route('admin.clients.index') }}") {
            showClientsLoading();
            $.ajax({
                url: url,
                method: 'GET',
                data: $('#client-filter-form').serialize(),
                dataType: 'json',
                success: function (res) {
                    $('#clients-table-container').html(res.html);
                },
                error: function () {
                    $('#clients-loading-overlay').remove();
                    alert('Failed to load customers. Please try again.');
                }
            });
        }

        $('#client-filter-form').on('submit', function (e) { e.preventDefault(); refreshClients(); });
        $(document).on('click', '#clients-table-container .pagination a', function (e) { e.preventDefault(); refreshClients($(this).attr('href')); });

        crmDropdown('.client-actions-btn', '.client-actions-menu');

        $(document).on('click', '#clients-table-container .sort-column', function () {
            const col = $(this).data('sort');
            const currentSort = $('#sort-input').val();
            const currentDir = $('#direction-input').val();
            const newDir = (currentSort === col && currentDir === 'asc') ? 'desc' : 'asc';
            $('#sort-input').val(col);
            $('#direction-input').val(newDir);
            refreshClients();
        });

        function deleteClients(ids) {
            $.ajax({
                url: "{{ url('/admin/clients') }}/" + ids.join(','),
                method: 'POST',
                data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
                headers: { 'Accept': 'application/json' },
                success: function (res) { showClientAlert(res.message); refreshClients(); },
                error: function () { showClientAlert('Failed to delete customer' + (ids.length > 1 ? 's' : '') + '.', true); }
            });
        }

        $(document).on('click', '.delete-client', function () {
            if (!confirm('Delete this customer?')) return;
            deleteClients([Number($(this).data('id'))]);
        });

        $(document).on('click', '#client-bulk-delete', function () {
            const ids = typeof window.selectedClientIds === 'function' ? window.selectedClientIds() : [];
            if (!ids.length) return;
            if (!confirm('Delete ' + ids.length + ' selected customers?')) return;
            deleteClients(ids);
        });

        function restoreClients(ids) {
            $.ajax({
                url: "{{ url('/admin/clients') }}/" + ids.join(',') + "/restore",
                method: 'POST',
                data: { _token: "{{ csrf_token() }}" },
                headers: { 'Accept': 'application/json' },
                success: function (res) { showClientAlert(res.message); refreshClients(); },
                error: function () { showClientAlert('Failed to restore customer' + (ids.length > 1 ? 's' : '') + '.', true); }
            });
        }

        $(document).on('click', '.restore-client', function () {
            if (!confirm('Restore this customer?')) return;
            restoreClients([Number($(this).data('id'))]);
        });

        $(document).on('click', '#client-bulk-restore', function () {
            const ids = typeof window.selectedClientIds === 'function' ? window.selectedClientIds() : [];
            if (!ids.length) return;
            if (!confirm('Restore ' + ids.length + ' selected customers?')) return;
            restoreClients(ids);
        });

        // Keep export links in sync with active filters
        function syncExportLinks() {
            const params = $('#client-filter-form').serialize();
            $('#export-excel-link').attr('href', "{{ route('admin.clients.export.excel') }}" + (params ? '?' + params : ''));
            $('#export-pdf-link').attr('href',   "{{ route('admin.clients.export.pdf') }}"   + (params ? '?' + params : ''));
        }
        syncExportLinks();
        $('#client-filter-form').on('change input', syncExportLinks);
    </script>
</x-layouts.dashboard>
