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

    @include('admin.partials.dropdown-script')

    <script>
        function showClientAlert(message, isError = false) {
            const baseClass = isError
                ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            $('#client-alert').removeClass('hidden').attr('class', baseClass).text(message);
        }

        function refreshClients(url = "{{ route('admin.clients.index') }}") {
            $.get(url, $('#client-filter-form').serialize(), function (res) {
                $('#clients-table-container').html(res.html);
            }, 'json');
        }

        $('#client-filter-form').on('submit', function (e) { e.preventDefault(); refreshClients(); });
        $(document).on('click', '#clients-table-container .pagination a', function (e) { e.preventDefault(); refreshClients($(this).attr('href')); });

        crmDropdown('.client-actions-btn', '.client-actions-menu');

        $(document).on('click', '.delete-client', function () {
            const id = $(this).data('id');
            if (!confirm('Delete this customer?')) return;
            $.ajax({
                url: "{{ url('/admin/clients') }}/" + id,
                method: 'POST',
                data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
                headers: { 'Accept': 'application/json' },
                success: function (res) { showClientAlert(res.message); refreshClients(); },
                error: function () { showClientAlert('Failed to delete customer.', true); }
            });
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
