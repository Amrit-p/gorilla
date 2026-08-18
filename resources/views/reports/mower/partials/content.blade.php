    <div class="space-y-5">

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('success') }}</div>
        @endif

        <div id="mower-alert" class="hidden"></div>
        @include('admin.jobs.partials.filter-bar', [
            'filters' => $filters,
            'filterCallback' => 'loadMowerReports',
            'filterUrl' => route('reports.mower.report'),
            'excelHref' => route('reports.mower.export'),
            'pdfHref' => route('reports.mower.export-pdf'),
            'tableContainer' => 'mower-table-container',
            'hideListScope' => true,
            'visibleFilters' => $isMower ? ['search', 'date_range'] : null,
        ])

        <div id="mower-table-container" class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
            @include('reports.mower.partials.table', [
                'hideBonusColumn' => $hideBonusColumn,
                'canEditPayout' => ! $isMower,
            ])
        </div>
    </div>

    <x-reports.mower-jobs-modal :employees="$employees" :workflowStatuses="$workflowStatuses" />

    <script>
        $(document).ready(function() {
            loadMowerReports();
        });

        (function () {
            var payoutUrl = @json(route('reports.mower.payout.update'));
            var saveTimer = null;

            $(document).on('change blur', '.mower-payout-input', function () {
                var $input = $(this);
                var userId = $input.data('user-id');
                var amount = parseFloat($input.val());

                if (!userId || Number.isNaN(amount) || amount < 0) {
                    return;
                }

                clearTimeout(saveTimer);
                saveTimer = setTimeout(function () {
                    $input.prop('disabled', true);

                    $.ajax({
                        url: payoutUrl,
                        method: 'PATCH',
                        data: {
                            user_id: userId,
                            amount: amount,
                            start_date: $input.data('start-date') || null,
                            end_date: $input.data('end-date') || null,
                            _token: $('meta[name="csrf-token"]').attr('content'),
                        },
                        success: function (res) {
                            $input.val(parseFloat(res.amount).toFixed(2));
                            $input.closest('td').find('p').text('Manual').removeClass('text-slate-400').addClass('font-medium text-emerald-700');
                            if (typeof window.showMowerAlert === 'function') {
                                window.showMowerAlert(res.message || 'Payout updated.', 'success');
                            }
                        },
                        error: function (xhr) {
                            var msg = xhr.responseJSON?.message || 'Failed to update payout.';
                            if (typeof window.showMowerAlert === 'function') {
                                window.showMowerAlert(msg, 'error');
                            } else {
                                alert(msg);
                            }
                        },
                        complete: function () {
                            $input.prop('disabled', false);
                        }
                    });
                }, 150);
            });
        })();
    </script>

