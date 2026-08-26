@props(['listUrl'])

{{--
    The payouts list modal, shared by the salary calculator and the mower report.

    `listUrl` is the endpoint that renders admin.salary.calculator.partials.payouts-table
    and returns { html, count, month_label|label }. What scopes that list differs per
    page, so the opener supplies the query params:

      window.salaryPayoutsModal.open({ params: { … }, subtitle: 'Mower name' })

    The salary calculator's months table opens it through .salary-payouts-cell, which
    carries mower/year/month data attributes and is handled below. Editing or deleting
    a payout fires a `payouts:changed` window event so the host page can refresh
    whatever totals it shows behind the modal.
--}}
<div id="salary-payouts-modal" class="fixed inset-0 z-[55] hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Payouts</h3>
                <p id="salary-payouts-subtitle" class="text-xs text-slate-500">Loading…</p>
            </div>
            <button type="button" class="salary-payouts-close rounded-lg p-1 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600" aria-label="Close">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4">
            <p id="salary-payouts-error" class="mb-3 hidden rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700"></p>

            <div id="salary-payouts-list"></div>

            {{-- Correcting a single payout. Shown in place of the list. --}}
            <form id="salary-payouts-edit-form" class="hidden">
                <p class="mb-3 text-xs text-slate-500">Editing payout created <span id="salary-payouts-edit-created" class="font-medium text-slate-700"></span></p>
                <input type="hidden" id="salary-payouts-edit-id">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-medium text-slate-600">Amount <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" required id="salary-payouts-edit-amount"
                               class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600">Bonus <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" required id="salary-payouts-edit-bonus"
                               class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs font-medium text-slate-600">Comment</label>
                        <input type="text" id="salary-payouts-edit-comment" placeholder="Optional note for this payout"
                               class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" class="salary-payouts-edit-cancel rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Nested drill-down: the jobs a single payout settles, opened from the
     "Jobs" count inside the payouts modal. Sits above that modal. --}}
<div id="salary-payout-jobs-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Jobs in this payout</h3>
                <p id="salary-payout-jobs-subtitle" class="text-xs text-slate-500">Loading…</p>
            </div>
            <button type="button" class="salary-payout-jobs-close rounded-lg p-1 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600" aria-label="Close">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="salary-payout-jobs-body" class="min-h-0 flex-1 overflow-y-auto px-5 py-4"></div>
    </div>
</div>

<script>
    window.salaryPayoutsModal = (function () {
        const listUrl = @json($listUrl);
        const payoutStoreUrl = @json(route('admin.salary-calculator.store'));
        const payoutBaseUrl = @json(url('/admin/salary-calculator/payouts'));
        const csrfToken = @json(csrf_token());

        // The list currently open: the query params that produced it and the
        // heading it was opened under, so an edit or delete can re-fetch it.
        let payoutsCtx = null;

        function openModal() {
            $('#salary-payouts-modal').removeClass('hidden').addClass('flex');
        }

        function closeModal() {
            $('#salary-payouts-modal').addClass('hidden').removeClass('flex');
            showList();
            payoutsCtx = null;
        }

        function showList() {
            $('#salary-payouts-edit-form').addClass('hidden');
            $('#salary-payouts-list').removeClass('hidden');
            $('#salary-payouts-error').addClass('hidden').text('');
        }

        function loadPayouts() {
            if (!payoutsCtx) { return; }

            $('#salary-payouts-error').addClass('hidden').text('');
            $('#salary-payouts-list').html('<p class="py-6 text-center text-sm text-slate-400">Loading payouts…</p>');

            $.ajax({
                url: listUrl,
                method: 'GET',
                headers: { Accept: 'application/json' },
                data: payoutsCtx.params,
                success: function (res) {
                    $('#salary-payouts-list').html(res.html);
                    const noun = res.count === 1 ? 'payout' : 'payouts';
                    const parts = [payoutsCtx.subtitle, res.month_label || res.label, res.count + ' ' + noun];
                    $('#salary-payouts-subtitle').text(parts.filter(Boolean).join(' · '));
                },
                error: function () {
                    $('#salary-payouts-list').html('<p class="py-6 text-center text-sm text-red-600">Unable to load payouts.</p>');
                }
            });
        }

        function open(context) {
            payoutsCtx = {
                params: (context && context.params) || {},
                subtitle: (context && context.subtitle) || '',
            };
            showList();
            $('#salary-payouts-subtitle').text('Loading…');
            openModal();
            loadPayouts();
        }

        function announceChange() {
            window.dispatchEvent(new CustomEvent('payouts:changed'));
        }

        // The salary calculator's monthly breakdown opens the modal for one month.
        $(document).on('click', '.salary-payouts-cell', function () {
            const btn = $(this);
            open({
                params: {
                    mower_id: btn.data('mower-id'),
                    year: btn.data('year'),
                    month: Number(btn.data('month')),
                },
                subtitle: btn.data('mower-name'),
            });
        });

        $(document).on('click', '.salary-payouts-close', closeModal);

        $(document).on('click', '.salary-payouts-edit', function () {
            const btn = $(this);
            $('#salary-payouts-edit-id').val(btn.data('payout-id'));
            $('#salary-payouts-edit-amount').val(btn.data('amount'));
            $('#salary-payouts-edit-bonus').val(btn.data('bonus'));
            $('#salary-payouts-edit-comment').val(btn.data('comment') || '');
            $('#salary-payouts-edit-created').text(btn.data('created') || '');
            $('#salary-payouts-error').addClass('hidden').text('');
            $('#salary-payouts-list').addClass('hidden');
            $('#salary-payouts-edit-form').removeClass('hidden');
        });

        $(document).on('click', '.salary-payouts-edit-cancel', showList);

        $(document).on('submit', '#salary-payouts-edit-form', function (e) {
            e.preventDefault();

            const payoutId = $('#salary-payouts-edit-id').val();
            const btn = $(this).find('button[type="submit"]');
            const originalText = btn.text();
            btn.prop('disabled', true).text('Saving…');

            $.ajax({
                url: payoutStoreUrl,
                method: 'POST',
                headers: { Accept: 'application/json' },
                data: {
                    _token: csrfToken,
                    payouts: [{
                        mode: 'update',
                        payout_id: payoutId,
                        amount: $('#salary-payouts-edit-amount').val(),
                        bonus: $('#salary-payouts-edit-bonus').val(),
                        comment: $('#salary-payouts-edit-comment').val(),
                    }],
                },
                success: function (res) {
                    showList();
                    loadPayouts();
                    announceChange();
                    if (typeof window.showJobAlert === 'function') {
                        window.showJobAlert(res.message || 'Payout updated.');
                    }
                },
                error: function (xhr) {
                    const message = xhr.responseJSON?.errors
                        ? Object.values(xhr.responseJSON.errors).flat().join(' ')
                        : (xhr.responseJSON?.message || 'Unable to update payout.');
                    $('#salary-payouts-error').removeClass('hidden').text(message);
                },
                complete: function () {
                    btn.prop('disabled', false).text(originalText);
                }
            });
        });

        $(document).on('click', '.salary-payouts-delete', function () {
            const payoutId = $(this).data('payout-id');
            if (!payoutId) { return; }
            if (!confirm('Delete this payout? Its jobs become unpaid and can be paid out again.')) { return; }

            const row = $(this).closest('tr');
            row.css('opacity', '0.5');

            $.ajax({
                url: payoutBaseUrl + '/' + payoutId,
                method: 'POST',
                headers: { Accept: 'application/json' },
                data: { _token: csrfToken, _method: 'DELETE' },
                success: function (res) {
                    loadPayouts();
                    announceChange();
                    if (typeof window.showJobAlert === 'function') {
                        window.showJobAlert(res.message || 'Payout deleted.');
                    }
                },
                error: function () {
                    row.css('opacity', '1');
                    $('#salary-payouts-error').removeClass('hidden').text('Unable to delete payout.');
                }
            });
        });

        // ---- Jobs-in-a-payout drill-down (nested modal) -------------------

        $(document).on('click', '.salary-payout-jobs-cell', function () {
            const payoutId = $(this).data('payout-id');
            const count = Number($(this).data('job-count')) || 0;
            if (!payoutId) { return; }

            const noun = count === 1 ? 'job' : 'jobs';
            $('#salary-payout-jobs-subtitle').text(count + ' ' + noun + ' settled by this payout');
            $('#salary-payout-jobs-body').html('<p class="py-6 text-center text-sm text-slate-400">Loading jobs…</p>');
            $('#salary-payout-jobs-modal').removeClass('hidden').addClass('flex');

            $.ajax({
                url: payoutBaseUrl + '/' + payoutId + '/jobs',
                method: 'GET',
                headers: { Accept: 'application/json' },
                success: function (res) {
                    $('#salary-payout-jobs-body').html(res.html);
                },
                error: function () {
                    $('#salary-payout-jobs-body').html('<p class="py-6 text-center text-sm text-red-600">Unable to load the jobs for this payout.</p>');
                }
            });
        });

        $(document).on('click', '.salary-payout-jobs-close', function () {
            $('#salary-payout-jobs-modal').addClass('hidden').removeClass('flex');
        });

        return { open: open, reload: loadPayouts, close: closeModal };
    })();
</script>
