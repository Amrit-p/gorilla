<x-layouts.dashboard :title="'Salary Calculator'">
    <x-ui.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Salary Calculator'],
    ]" />

    <div class="space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Salary Calculator</h2>
            <p class="text-sm text-slate-600">Select a mower to see their month-by-month sales, bonus, and salary payout.</p>
        </div>

        <div class="flex flex-wrap items-end gap-4 rounded-2xl border border-slate-200 bg-white p-4">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-slate-600">Mower</label>
                <select id="salary-mower-select"
                    class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">Select a mower</option>
                    @foreach ($mowers as $mower)
                        <option value="{{ $mower->id }}" data-mower-name="{{ $mower->name }}">{{ $mower->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-slate-600">Year</label>
                <select id="salary-year-select"
                    class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    @for ($year = $currentYear; $year >= $currentYear - 5; $year--)
                        <option value="{{ $year }}" @selected($year === $currentYear)>{{ $year }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <div id="salary-months-table-container">
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-10 text-center text-sm text-slate-400">
                Select a mower to view their monthly salary breakdown.
            </div>
        </div>
    </div>

    @include('components.reports.mower-bonus-modal')

    <style>
        #salary-months-table-container { position: relative; min-height: 4rem; }

        #salary-months-loading-overlay {
            position: absolute; inset: 0; z-index: 20;
            background: rgba(255,255,255,.72);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            display: flex; align-items: center; justify-content: center;
            border-radius: .75rem;
        }
        @keyframes salary-months-spin { to { transform: rotate(360deg); } }
        #salary-months-loading-overlay .salary-months-spinner {
            width: 28px; height: 28px;
            border: 2.5px solid #e0e7ff; border-top-color: #4f46e5;
            border-radius: 50%; animation: salary-months-spin .7s linear infinite;
        }
    </style>

    <script>
        function showSalaryMonthsLoading() {
            if ($('#salary-months-loading-overlay').length) return;
            $('#salary-months-table-container').append(
                '<div id="salary-months-loading-overlay">' +
                '<div style="display:flex;flex-direction:column;align-items:center;gap:10px;">' +
                '<div class="salary-months-spinner"></div>' +
                '<span style="font-size:.7rem;font-weight:500;color:#64748b;letter-spacing:.05em;">Loading…</span>' +
                '</div></div>'
            );
        }

        function loadSalaryMonths() {
            const mowerId = $('#salary-mower-select').val();
            const year = $('#salary-year-select').val();

            if (!mowerId) {
                $('#salary-months-table-container').html(
                    '<div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-10 text-center text-sm text-slate-400">Select a mower to view their monthly salary breakdown.</div>'
                );
                return;
            }

            showSalaryMonthsLoading();

            $.ajax({
                url: @json(route('admin.salary-calculator.months')),
                method: 'GET',
                data: { mower_id: mowerId, year: year },
                success: function (res) {
                    $('#salary-months-table-container').html(res.html);
                },
                error: function () {
                    $('#salary-months-loading-overlay').remove();
                }
            });
        }

        function recalculateRow(row) {
            const sales = parseFloat(row.data('sales'));
            const bonus = parseFloat(row.data('bonus'));
            const pct = parseFloat(row.find('.salary-percentage-input').val()) || 0;
            const payout = (sales * pct / 100) + bonus;
            const title = pct.toFixed(2) + '% × $' + sales.toFixed(2) + ' + $' + bonus.toFixed(2) + ' = $' + payout.toFixed(2);
            row.find('.salary-payout-cell').text('$' + payout.toFixed(2)).attr('title', title);
        }

        $(document).on('change', '#salary-mower-select, #salary-year-select', loadSalaryMonths);

        $(document).on('input', '.salary-percentage-input', function () {
            recalculateRow($(this).closest('tr'));
        });

        $(document).on('click', '.salary-generate-btn', function () {
            const btn = $(this);
            const row = btn.closest('tr');
            const percentage = row.find('.salary-percentage-input').val();

            if (!confirm('Generate this salary receipt and notify the mower?')) return;

            const originalText = btn.text();
            btn.prop('disabled', true).text('Generating…');

            $.ajax({
                url: @json(route('admin.salary-calculator.store')),
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                data: {
                    _token: "{{ csrf_token() }}",
                    mower_id: row.data('mower-id'),
                    year: row.data('year'),
                    month: row.data('month'),
                    percentage: percentage,
                },
                success: function () {
                    loadSalaryMonths();
                },
                error: function (xhr) {
                    const message = xhr.responseJSON?.errors
                        ? Object.values(xhr.responseJSON.errors).flat().join(' ')
                        : (xhr.responseJSON?.message || 'Unable to generate receipt.');
                    alert(message);
                    btn.prop('disabled', false).text(originalText);
                }
            });
        });

        $(document).on('click', '.salary-delete-btn', function () {
            if (!confirm('Delete this salary receipt? This cannot be undone.')) return;
            const btn = $(this);
            const receiptId = btn.data('receipt-id');
            const originalText = btn.text();
            btn.prop('disabled', true).text('Deleting…');

            $.ajax({
                url: "{{ url('/admin/salary-receipts') }}/" + receiptId,
                method: 'POST',
                data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
                headers: { 'Accept': 'application/json' },
                success: function () {
                    loadSalaryMonths();
                },
                error: function () {
                    btn.prop('disabled', false).text(originalText);
                }
            });
        });
    </script>
</x-layouts.dashboard>
