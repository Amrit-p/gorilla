{{--
    Payout modal shared by every view that renders the job bulk toolbar: the jobs
    list, the map, the three-week grid and the salary calculator drill-down.

    One modal handles both directions. Unpaid jobs in the selection become a
    "New payout" card per mower; jobs that are already paid become an "Edit
    payout" card for the payout covering them. A mixed selection shows both and
    saves them together.
--}}
@can('manage-salary-calculator')
    <div id="job-payout-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/50 p-4">
        <div class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Payouts</h3>
                    <p id="job-payout-subtitle" class="text-xs text-slate-500">Reading the selected jobs…</p>
                </div>
                <button type="button" class="job-payout-close rounded-lg p-1 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600" aria-label="Close">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div id="job-payout-blocked" class="hidden px-5 py-6">
                <p id="job-payout-blocked-text" class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700"></p>
                <div class="mt-4 flex justify-end">
                    <button type="button" class="job-payout-close rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Close</button>
                </div>
            </div>

            <form id="job-payout-form" class="hidden min-h-0 flex-1 flex-col">
                <div id="job-payout-groups" class="min-h-0 flex-1 space-y-4 overflow-y-auto px-5 py-4"></div>

                <div class="shrink-0 border-t border-slate-200 bg-slate-50 px-5 py-3">
                    <p id="job-payout-error" class="mb-2 hidden rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700"></p>
                    <div class="flex items-center justify-between gap-3">
                        <p id="job-payout-grand-total" class="text-xs font-medium text-slate-600"></p>
                        <div class="flex gap-2">
                            <button type="button" class="job-payout-close rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancel</button>
                            <button type="submit" id="job-payout-save" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Save</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // The toolbar can be re-rendered by AJAX; only wire the handlers once.
        if (!window.jobPayoutModalReady) {
            window.jobPayoutModalReady = true;

            (function () {
                const selectionUrl = @json(route('admin.salary-calculator.selection'));
                const storeUrl = @json(route('admin.salary-calculator.store'));
                const payoutsUrl = @json(url('/admin/salary-calculator/payouts'));
                const csrfToken = @json(csrf_token());

                let groups = [];

                function selectedIds() {
                    return window.crmJobSelection ? window.crmJobSelection.getIds() : [];
                }

                function money(value) {
                    return '$' + Number(value || 0).toFixed(2);
                }

                function escapeHtml(value) {
                    return $('<div>').text(value == null ? '' : value).html();
                }

                function openModal() {
                    $('#job-payout-modal').removeClass('hidden').addClass('flex');
                }

                function closeModal() {
                    $('#job-payout-modal').addClass('hidden').removeClass('flex');
                }

                function showBlocked(message) {
                    $('#job-payout-form').addClass('hidden').removeClass('flex');
                    $('#job-payout-blocked').removeClass('hidden');
                    $('#job-payout-blocked-text').text(message);
                    $('#job-payout-subtitle').text('Nothing to save');
                }

                function groupCard(group, index) {
                    const isUpdate = group.mode === 'update';
                    const accent = isUpdate ? 'amber' : 'emerald';
                    const initials = (group.mower_name || '?').trim().charAt(0).toUpperCase();

                    const badge = isUpdate
                        ? '<span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-700">Edit payout</span>'
                        : '<span class="shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-700">New payout</span>';

                    // On an edit card the totals cover every job on the payout, which
                    // may be more than the admin actually ticked.
                    const scope = isUpdate && group.selected_job_count < group.job_count
                        ? ' &middot; ' + group.selected_job_count + ' of them selected'
                        : '';

                    const deleteBtn = isUpdate
                        ? '<button type="button" class="job-payout-delete text-[11px] font-medium text-red-600 hover:underline" data-payout-id="' + group.payout_id + '">Delete this payout</button>'
                        : '';

                    return '' +
                        '<section class="rounded-xl border border-' + accent + '-200 bg-white shadow-sm" data-group-index="' + index + '">' +
                            '<header class="flex items-center gap-3 border-b border-slate-100 bg-' + accent + '-50/60 px-4 py-3">' +
                                '<span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-' + accent + '-100 text-sm font-bold text-' + accent + '-700">' + escapeHtml(initials) + '</span>' +
                                '<div class="min-w-0 flex-1">' +
                                    '<p class="truncate text-sm font-semibold text-slate-900">' + escapeHtml(group.mower_name) + '</p>' +
                                    '<p class="text-xs text-slate-500">' + group.job_count + ' job' + (group.job_count === 1 ? '' : 's') + scope + ' &middot; ' + money(group.total_sales) + ' sale &middot; ' + Number(group.total_hours).toFixed(2) + ' h</p>' +
                                '</div>' +
                                badge +
                            '</header>' +
                            '<div class="grid gap-3 px-4 py-3 sm:grid-cols-2">' +
                                '<div>' +
                                    '<label class="text-xs font-medium text-slate-600">Amount <span class="text-red-500">*</span></label>' +
                                    '<input type="number" step="0.01" min="0" required data-field="amount" value="' + Number(group.amount).toFixed(2) + '"' +
                                        ' class="job-payout-input mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">' +
                                '</div>' +
                                '<div>' +
                                    '<label class="text-xs font-medium text-slate-600">Bonus <span class="text-red-500">*</span></label>' +
                                    '<input type="number" step="0.01" min="0" required data-field="bonus" value="' + Number(group.bonus).toFixed(2) + '"' +
                                        ' class="job-payout-input mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">' +
                                '</div>' +
                                '<div class="sm:col-span-2">' +
                                    '<label class="text-xs font-medium text-slate-600">Comment</label>' +
                                    '<input type="text" data-field="comment" placeholder="Optional note for this payout" value="' + escapeHtml(group.comment || '') + '"' +
                                        ' class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">' +
                                '</div>' +
                                (deleteBtn ? '<div class="sm:col-span-2">' + deleteBtn + '</div>' : '') +
                            '</div>' +
                        '</section>';
                }

                function syncGrandTotal() {
                    let total = 0;

                    $('#job-payout-groups section').each(function () {
                        total += parseFloat($(this).find('[data-field="amount"]').val()) || 0;
                        total += parseFloat($(this).find('[data-field="bonus"]').val()) || 0;
                    });

                    const creates = groups.filter(function (g) { return g.mode === 'create'; }).length;
                    const updates = groups.length - creates;
                    const parts = [];

                    if (creates) { parts.push(creates + ' new'); }
                    if (updates) { parts.push(updates + ' edited'); }

                    $('#job-payout-grand-total').html(
                        parts.join(', ') + ' &middot; total <span class="font-semibold text-emerald-700">' + money(total) + '</span>'
                    );
                }

                function renderGroups(payload) {
                    groups = payload.groups || [];

                    $('#job-payout-blocked').addClass('hidden');
                    $('#job-payout-form').removeClass('hidden').addClass('flex');
                    $('#job-payout-groups').html(groups.map(groupCard).join(''));

                    const creates = groups.filter(function (g) { return g.mode === 'create'; }).length;
                    const updates = groups.length - creates;

                    let subtitle;
                    if (creates && updates) {
                        subtitle = creates + ' payout' + (creates === 1 ? '' : 's') + ' to create and ' + updates + ' to update';
                    } else if (updates) {
                        subtitle = 'Editing ' + updates + ' existing payout' + (updates === 1 ? '' : 's');
                    } else {
                        subtitle = creates === 1
                            ? 'New payout for ' + groups[0].mower_name
                            : 'One new payout for each of ' + creates + ' mowers';
                    }

                    $('#job-payout-subtitle').text(subtitle);
                    $('#job-payout-save').text(groups.length === 1 ? 'Save' : 'Save all ' + groups.length);
                    syncGrandTotal();
                }

                function loadSelection(ids) {
                    groups = [];
                    $('#job-payout-error').addClass('hidden').text('');
                    $('#job-payout-blocked').addClass('hidden');
                    $('#job-payout-form').addClass('hidden').removeClass('flex');
                    $('#job-payout-subtitle').text('Reading the selected jobs…');
                    openModal();

                    $.ajax({
                        url: selectionUrl,
                        method: 'POST',
                        headers: { Accept: 'application/json' },
                        data: { _token: csrfToken, job_ids: ids },
                        success: function (res) {
                            if (!res.ok) {
                                showBlocked(res.error || 'This selection cannot be paid out.');
                                return;
                            }
                            renderGroups(res);
                        },
                        error: function () {
                            showBlocked('Unable to read the selected jobs.');
                        }
                    });
                }

                function afterSave(message) {
                    closeModal();
                    if (window.crmJobSelection) { window.crmJobSelection.clear(); }
                    if (typeof window.showJobAlert === 'function') {
                        window.showJobAlert(message);
                    }
                    window.dispatchEvent(new CustomEvent('jobs:payout-recorded', { detail: {} }));
                }

                $(document).on('click', '#job-bulk-payout', function () {
                    const ids = selectedIds();
                    if (!ids.length) return;
                    loadSelection(ids);
                });

                // The "Mower paid" stamp opens the same modal for just that job,
                // which resolves to the edit card for its payout.
                $(document).on('click', '.js-open-payout', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const jobId = Number($(this).data('job-id'));
                    if (!jobId) return;

                    loadSelection([jobId]);
                });

                $(document).on('input', '.job-payout-input', syncGrandTotal);
                $(document).on('click', '.job-payout-close', closeModal);

                $(document).on('click', '.job-payout-delete', function () {
                    const payoutId = $(this).data('payout-id');
                    if (!payoutId) return;
                    if (!confirm('Delete this payout? Its jobs become unpaid and can be paid out again.')) return;

                    const btn = $(this).prop('disabled', true).text('Deleting…');

                    $.ajax({
                        url: payoutsUrl + '/' + payoutId,
                        method: 'POST',
                        headers: { Accept: 'application/json' },
                        data: { _token: csrfToken, _method: 'DELETE' },
                        success: function (res) {
                            afterSave(res.message || 'Payout deleted.');
                        },
                        error: function () {
                            $('#job-payout-error').removeClass('hidden').text('Unable to delete payout.');
                            btn.prop('disabled', false).text('Delete this payout');
                        }
                    });
                });

                $(document).on('submit', '#job-payout-form', function (e) {
                    e.preventDefault();

                    if (!groups.length) return;

                    const payload = groups.map(function (group, index) {
                        const $card = $('#job-payout-groups section[data-group-index="' + index + '"]');

                        return {
                            mode: group.mode,
                            payout_id: group.payout_id,
                            mower_id: group.mower_id,
                            job_ids: group.job_ids,
                            amount: $card.find('[data-field="amount"]').val(),
                            bonus: $card.find('[data-field="bonus"]').val(),
                            comment: $card.find('[data-field="comment"]').val(),
                        };
                    });

                    const btn = $('#job-payout-save');
                    const originalText = btn.text();
                    btn.prop('disabled', true).text('Saving…');

                    $.ajax({
                        url: storeUrl,
                        method: 'POST',
                        headers: { Accept: 'application/json' },
                        data: { _token: csrfToken, payouts: payload },
                        success: function (res) {
                            afterSave(res.message || 'Payouts saved.');
                        },
                        error: function (xhr) {
                            const message = xhr.responseJSON?.errors
                                ? Object.values(xhr.responseJSON.errors).flat().join(' ')
                                : (xhr.responseJSON?.message || 'Unable to save payouts.');
                            $('#job-payout-error').removeClass('hidden').text(message);
                        },
                        complete: function () {
                            btn.prop('disabled', false).text(originalText);
                        }
                    });
                });
            })();
        }
    </script>
@endcan
