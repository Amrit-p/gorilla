<x-layouts.dashboard :title="'Employee Bonuses'">
    <div class="space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Employee Bonuses</h2>
            <p class="text-sm text-slate-600">Track and manage bonus payments for team members.</p>
        </div>

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div id="bonus-alert" class="hidden"></div>

        @include('admin.employee-bonuses.partials.filter-bar', ['filters' => $filters, 'employees' => $employees])

        <div id="bonuses-table-container">
            @include('admin.employee-bonuses.partials.table', ['bonuses' => $bonuses])
        </div>
    </div>

    <script>
        function showBonusAlert(message, isError = false) {
            const cls = isError
                ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            $('#bonus-alert').removeClass('hidden').attr('class', cls).text(message);
        }

        $(document).on('click', '.delete-bonus', function () {
            if (!confirm('Delete this bonus? This cannot be undone.')) return;
            const id = $(this).data('id');
            $.ajax({
                url: "{{ url('/admin/employee-bonuses') }}/" + id,
                method: 'POST',
                data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
                headers: { 'Accept': 'application/json' },
                success: function (response) {
                    showBonusAlert(response.message);
                    loadBonuses();
                },
                error: function () {
                    showBonusAlert('Unable to delete bonus.', true);
                }
            });
        });
    </script>
</x-layouts.dashboard>
