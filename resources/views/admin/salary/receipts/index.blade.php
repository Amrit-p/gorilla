<x-layouts.dashboard :title="'Salary Receipts'">
    <div class="space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Salary Receipts</h2>
            <p class="text-sm text-slate-600">
                @can('manage-salary-calculator')
                    Generated salary receipts for all mowers.
                @else
                    Your generated salary receipts.
                @endcan
            </p>
        </div>

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @include('admin.salary.receipts.partials.filter-bar', ['filters' => $filters, 'mowers' => $mowers])

        <div id="salary-receipts-table-container">
            @include('admin.salary.receipts.partials.table', ['receipts' => $receipts])
        </div>
    </div>

    <script>
        var salaryReceiptSearchTimer;

        function getSalaryReceiptFilters() {
            return Object.fromEntries(new URLSearchParams(new FormData(document.getElementById('salary-receipt-filter-form'))).entries());
        }

        function loadSalaryReceipts(url) {
            url = url || @json(route('admin.salary-receipts.index'));
            $.ajax({
                url: url,
                method: 'GET',
                data: getSalaryReceiptFilters(),
                dataType: 'json',
                success: function (res) { $('#salary-receipts-table-container').html(res.html); },
            });
        }

        $('#salary-receipt-filter-form').on('change', 'select.filter, input[readonly].filter', function () {
            loadSalaryReceipts();
        });

        $('#salary-receipt-filter-form').on('input', 'input[type="text"]:not([readonly]).filter', function () {
            clearTimeout(salaryReceiptSearchTimer);
            salaryReceiptSearchTimer = setTimeout(loadSalaryReceipts, 400);
        });

        $('#salary-receipt-filter-form').on('submit', function (e) {
            e.preventDefault();
            loadSalaryReceipts();
        });

        $(document).on('click', '#salary-receipts-table-container .pagination a', function (e) {
            e.preventDefault();
            loadSalaryReceipts($(this).attr('href'));
        });

        $(document).on('click', '.delete-salary-receipt', function () {
            if (!confirm('Delete this salary receipt? This cannot be undone.')) return;
            const id = $(this).data('id');
            $.ajax({
                url: "{{ url('/admin/salary-receipts') }}/" + id,
                method: 'POST',
                data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
                headers: { 'Accept': 'application/json' },
                success: function () { loadSalaryReceipts(); },
            });
        });
    </script>
</x-layouts.dashboard>
