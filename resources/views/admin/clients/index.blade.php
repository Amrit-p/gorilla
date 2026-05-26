<x-layouts.dashboard :title="'Customer Management'">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Customers</h2>
                <p class="text-sm text-slate-600">Search, filter, and open customer profiles with job history and statistics.</p>
            </div>
            @can('manage-customers')
                <a href="{{ route('admin.clients.create') }}" class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Add Customer</a>
            @endcan
        </div>

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div id="client-alert" class="hidden"></div>

        @include('admin.clients.partials.filter-bar', ['filters' => $filters])

        <div id="clients-table-container">
            @include('admin.clients.partials.table', ['clients' => $clients])
        </div>
    </div>

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
    </script>
</x-layouts.dashboard>
