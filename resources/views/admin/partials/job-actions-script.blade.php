@props([
    'filterCallback' => 'loadJobs',
])
@can('manage-job-records')
    <style>
        .sortable-ghost {
            opacity: 0;
        }

        .sortable-drag {
            background: white;
            box-shadow: 0 8px 32px -4px rgba(15, 23, 42, .14), 0 2px 8px -2px rgba(15, 23, 42, .08);
            border-radius: 10px;
            outline: 2px solid #10b981;
            outline-offset: -2px;
        }

        .sortable-chosen {
            background: #f8fafc;
        }

        #jobs-reorder-toast {
            transition: opacity .25s ease, transform .25s ease;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js" defer></script>
    <script>
        (function() {
            const REORDER_URL = "{{ route('admin.jobs.reorder') }}";
            const CSRF = "{{ csrf_token() }}";

            let toastTimer;
            let sortableInstance = null;

            function showReorderToast(msg, isError) {
                let el = document.getElementById('jobs-reorder-toast');
                if (!el) {
                    el = document.createElement('div');
                    el.id = 'jobs-reorder-toast';
                    el.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;pointer-events:none;';
                    document.body.appendChild(el);
                }
                el.className = isError ?
                    'rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm text-red-600 shadow-lg ring-1 ring-red-100' :
                    'rounded-xl border border-emerald-200 bg-white px-4 py-2.5 text-sm text-emerald-700 shadow-lg ring-1 ring-emerald-100';
                el.textContent = msg;
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
                clearTimeout(toastTimer);
                toastTimer = setTimeout(() => {
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(.5rem)';
                }, 2200);
            }

            function initSortable() {
                const tbody = document.querySelector('#jobs-table-container table tbody');
                if (!tbody || !window.Sortable) return;

                if (sortableInstance) {
                    sortableInstance.destroy();
                    sortableInstance = null;
                }

                sortableInstance = Sortable.create(tbody, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    onEnd: function() {
                        const ids = Array.from(tbody.querySelectorAll('tr[data-job-id]'))
                            .map(tr => parseInt(tr.dataset.jobId, 10));

                        fetch(REORDER_URL, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': CSRF,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    ordered_ids: ids
                                }),
                            })
                            .then(r => r.ok ? r.json() : Promise.reject())
                            .then(() => showReorderToast('Order saved', false))
                            .catch(() => showReorderToast('Could not save order — try again', true));
                    },
                });
            }

            function onSortableReady() {
                initSortable();
                // Re-init only when loadJobs() replaces the container's direct children (not during drag)
                const container = document.getElementById('jobs-table-container');
                if (!container) return;
                new MutationObserver(initSortable).observe(container, {
                    childList: true
                });
            }

            if (window.Sortable) {
                onSortableReady();
            } else {
                const s = document.querySelector('script[src*="sortablejs"]');
                if (s) s.addEventListener('load', onSortableReady);
            }
        })();
    </script>
@endcan

<script>
    if (typeof crmDropdown === 'function') crmDropdown('.job-actions-btn', '.job-actions-menu');

    function showJobAlert(message, isError = false) {
        const baseClass = isError ?
            'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700' :
            'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
        $('#job-alert').removeClass('hidden').attr('class', baseClass).text(message);
    }

    function openModal(id) {
        $('#' + id).removeClass('hidden').addClass('flex');
    }

    function closeModal(id) {
        $('#' + id).addClass('hidden').removeClass('flex');
    }
    $('[data-close-modal]').on('click', function() {
        closeModal($(this).data('close-modal'));
    });

    // ── Tab switcher ────────────────────────────────────────────────────────
    const TAB_INACTIVE = 'border-transparent text-slate-500 hover:text-slate-700';
    const TAB_ACTIVE   = 'border-emerald-500 text-emerald-600 job-tab-active';

    $(document).on('click', '.job-tab-btn', function() {
        const $btn   = $(this);
        const modal  = $btn.data('modal');
        const target = $btn.data('tab');
        const $modal = $('#' + modal);

        // Switch button styles
        $modal.find('.job-tab-btn').each(function() {
            $(this).removeClass(TAB_ACTIVE).addClass('border-transparent text-slate-500 hover:text-slate-700')
                   .attr('aria-selected', 'false');
        });
        $btn.removeClass('border-transparent text-slate-500 hover:text-slate-700')
            .addClass(TAB_ACTIVE).attr('aria-selected', 'true');

        // Switch panels
        $modal.find('.job-tab-panel').addClass('hidden');
        $modal.find(`.job-tab-panel[data-panel="${target}"]`).removeClass('hidden');

        // Lazy-load history on first click
        if (target === 'history') {
            const panelId  = modal === 'assign-job-modal' ? 'assign-client-history' : 'schedule-client-history';
            const clientId = $modal.data('client-id') || '';
            const jobId    = $modal.data('job-id') || 0;
            const loaded   = $modal.data('history-loaded');
            if (!loaded && clientId) {
                loadClientHistory(panelId, clientId, jobId);
                $modal.data('history-loaded', true);
            }
        }
    });

    function resetHistoryPanel(panelId) {
        const $p = $('#' + panelId);
        $p.find('.client-history-loading').removeClass('hidden');
        $p.find('.client-history-content').addClass('hidden');
        $p.find('.client-history-accounting').addClass('hidden').text('');
        $p.find('.client-history-level-banner').css('display', 'none');
        $p.find('.client-history-service').empty();
        $p.find('.client-history-service-section').addClass('hidden');
        $p.find('.client-history-remarks').empty();
        $p.find('.client-history-remarks-section').addClass('hidden');
        $p.find('.client-history-empty').css('display', 'none');
        $p.closest('.job-tab-panel').siblings('[role="tablist"]').find('.job-history-dot').addClass('hidden');
    }

    function resetModalTabs(modalId) {
        const $modal = $('#' + modalId);
        $modal.find('.job-tab-btn').each(function() {
            const isForm = $(this).data('tab') === 'form';
            $(this).toggleClass(TAB_ACTIVE, isForm)
                   .toggleClass('border-transparent text-slate-500 hover:text-slate-700', !isForm)
                   .attr('aria-selected', isForm ? 'true' : 'false');
        });
        $modal.find('.job-tab-panel').addClass('hidden');
        $modal.find('.job-tab-panel[data-panel="form"]').removeClass('hidden');
    }

    function selectedJobIds() {
        return typeof window.selectedTableIds === 'function' ? window.selectedTableIds() : [];
    }

    function markModalBulkContext($form, ids, action) {
        const $context = $form.find('.bulk-job-context');
        $form.data('jobIds', ids);

        if (ids.length > 1) {
            $context.removeClass('hidden').text(action + ' will apply to ' + ids.length + ' selected jobs.');
        } else {
            $context.addClass('hidden').text('');
        }
    }

    function bulkActionPayload($form) {
        const payload = $form.serializeArray().filter(function(field) {
            return field.name !== 'job_id' && field.name !== 'job_ids' && field.name !== 'job_ids[]';
        });
        const ids = ($form.data('jobIds') || []).map(Number).filter(function(id) {
            return Number.isInteger(id) && id > 0;
        });

        ids.forEach(function(id) {
            payload.push({ name: 'job_ids[]', value: id });
        });

        return $.param(payload);
    }

    function refreshJobsTable() {
        {{$filterCallback}}(typeof getFilters === 'function' ? getFilters() : {});
        if (typeof window.clearJobBulkSelection === 'function') {
            window.clearJobBulkSelection();
        }
    }

    // ── Clampable text helper ───────────────────────────────────────────────
    let _chSeq = 0;
    function clampable(text, extraClass) {
        const id = 'ch-' + (++_chSeq);
        return '<p id="' + id + '" class="ch-clamped ' + (extraClass || '') + '">' + esc(text) + '</p>' +
               '<button type="button" class="ch-toggle mt-0.5 text-[10px] font-medium text-emerald-600 hover:underline" data-target="' + id + '" data-expanded="false">Show more</button>';
    }

    $(document).on('click', '.ch-section-toggle', function (e) {
        e.preventDefault();
        const $btn     = $(this);
        const $section = $('#' + $btn.data('target'));
        const opening  = $section.hasClass('hidden');
        $section.toggleClass('hidden', !opening);
        $btn.find('.ch-chevron').toggleClass('rotate-180', opening);
    });

    $(document).on('click', '.ch-toggle', function () {
        const $btn      = $(this);
        const $p        = $('#' + $btn.data('target'));
        const expanded  = $btn.data('expanded') === true;
        if (expanded) {
            $p.addClass('ch-clamped');
            $btn.text('Show more').data('expanded', false);
        } else {
            $p.removeClass('ch-clamped');
            $btn.text('Show less').data('expanded', true);
        }
    });

    function hideToggleIfNotClamped($container) {
        $container.find('.ch-toggle').each(function () {
            const $btn = $(this);
            const el   = document.getElementById($btn.data('target'));
            if (el && el.scrollHeight <= el.clientHeight + 2) {
                $btn.hide();
            }
        });
    }

    const STATUS_COLORS = {
        'Pending':    'bg-amber-50 text-amber-700 ring-amber-200/60',
        'Started':    'bg-blue-50 text-blue-700 ring-blue-200/60',
        'Completed':  'bg-emerald-50 text-emerald-700 ring-emerald-200/60',
        'Cancelled':  'bg-red-50 text-red-600 ring-red-200/60',
    };

    function statusBadge(status) {
        const cls = STATUS_COLORS[status] || 'bg-slate-100 text-slate-600 ring-slate-200/60';
        return '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 ' + cls + '">' + $('<span>').text(status).html() + '</span>';
    }

    function esc(str) {
        return $('<span>').text(str || '').html();
    }

    function loadClientHistory(panelId, clientId, jobId) {
        const $panel = $('#' + panelId);
        if (!$panel.length || !clientId) {
            $panel.addClass('hidden');
            return;
        }
        $panel.removeClass('hidden');
        $panel.find('.client-history-loading').removeClass('hidden');
        $panel.find('.client-history-content').addClass('hidden');
        $panel.find('.client-history-accounting').addClass('hidden').text('');

        // wire toggle button (once)
        if (!$panel.data('history-wired')) {
            $panel.data('history-wired', true);
            $panel.on('click', '.client-history-toggle', function() {
                const $body = $panel.find('.client-history-body');
                const $chevron = $panel.find('.client-history-chevron');
                $body.toggleClass('hidden');
                $chevron.toggleClass('rotate-180');
            });
        }

        $.ajax({
            url: "{{ route('admin.jobs.client-history') }}",
            method: 'GET',
            data: { client_id: clientId, exclude_job_id: jobId || 0 },
            headers: { Accept: 'application/json' },
            success: function(data) {
                $panel.find('.client-history-loading').addClass('hidden');

                if (data.accounting_level) {
                    $panel.find('.client-history-accounting')
                        .removeClass('hidden')
                        .text(data.accounting_level);
                    // tabbed banner
                    $panel.find('.client-history-level-name').text(data.accounting_level);
                    $panel.find('.client-history-level-banner').css('display', 'flex');
                }

                const hasAny = (data.service_history && data.service_history.length) ||
                               (data.mower_remarks && data.mower_remarks.length);

                if (!hasAny) {
                    $panel.find('.client-history-empty').css('display', 'flex');
                    $panel.find('.client-history-service-section, .client-history-remarks-section, .client-history-level-banner').addClass('hidden').css('display', '');
                    $panel.find('.client-history-content').removeClass('hidden');
                    return;
                }
                $panel.find('.client-history-empty').css('display', 'none');

                // ── Service history (date + status + notes grouped per job) ──
                const $service = $panel.find('.client-history-service');
                const $serviceSection = $panel.find('.client-history-service-section');
                $service.empty();
                if (data.service_history && data.service_history.length) {
                    const jobBaseUrl = "{{ url('admin/jobs') }}";
                    data.service_history.forEach(function(j) {
                        const hasNotes = !!(j.special_remarks || j.internal_notes);
                        const notesId  = 'chn-' + (++_chSeq);

                        let notesBodyHtml = '';
                        if (hasNotes) {
                            notesBodyHtml += '<div id="' + notesId + '" class="hidden space-y-2 border-t border-slate-100 px-3 pb-2.5 pt-2">';
                            if (j.special_remarks) {
                                notesBodyHtml += '<div>' +
                                    '<span class="mb-0.5 inline-block rounded bg-violet-100 px-1 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-violet-600">Remarks</span>' +
                                    clampable(j.special_remarks, 'text-slate-700') +
                                '</div>';
                            }
                            if (j.internal_notes) {
                                notesBodyHtml += '<div>' +
                                    '<span class="mb-0.5 inline-block rounded bg-sky-100 px-1 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-sky-600">Notes</span>' +
                                    clampable(j.internal_notes, 'text-slate-600') +
                                '</div>';
                            }
                            notesBodyHtml += '</div>';
                        }

                        const toggleBtn = hasNotes
                            ? '<button type="button" class="ch-section-toggle shrink-0 rounded p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600" data-target="' + notesId + '" aria-label="Toggle notes">' +
                                '<svg class="ch-chevron h-3.5 w-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>' +
                              '</button>'
                            : '';

                        $service.append(
                            '<div class="overflow-hidden rounded-lg bg-white text-xs ring-1 ring-slate-200">' +
                                '<div class="flex items-center gap-1 pr-1">' +
                                    '<a href="' + jobBaseUrl + '/' + j.id + '" target="_blank" rel="noopener"' +
                                        ' class="group flex min-w-0 flex-1 items-center gap-2 px-3 py-2 transition-colors hover:bg-emerald-50">' +
                                        '<svg class="h-3 w-3 shrink-0 text-slate-400 group-hover:text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>' +
                                        '<span class="font-medium text-slate-700 group-hover:text-emerald-700">' + esc(j.date) + '</span>' +
                                        statusBadge(j.status) +
                                        '<span class="ml-auto truncate text-slate-400" style="max-width:100px;">' + esc(j.done_by) + '</span>' +
                                        '<svg class="h-3 w-3 shrink-0 text-slate-300 group-hover:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>' +
                                    '</a>' +
                                    toggleBtn +
                                '</div>' +
                                notesBodyHtml +
                            '</div>'
                        );
                    });
                    $serviceSection.removeClass('hidden');
                } else {
                    $serviceSection.addClass('hidden');
                }

                // ── Mower remarks ──────────────────────────────────────────────────
                const $remarks = $panel.find('.client-history-remarks');
                const $remarksSection = $panel.find('.client-history-remarks-section');
                $remarks.empty();
                if (data.mower_remarks && data.mower_remarks.length) {
                    data.mower_remarks.forEach(function(r) {
                        $remarks.append(
                            '<div class="overflow-hidden rounded-lg bg-white text-xs ring-1 ring-slate-200">' +
                                '<div class="px-3 py-2.5">' +
                                    '<div class="mb-1 flex items-center justify-between gap-2">' +
                                        '<span class="font-medium text-slate-600">' + esc(r.created_by) + '</span>' +
                                        '<span class="text-[10px] text-slate-400">' + esc(r.created_at) + '</span>' +
                                    '</div>' +
                                    clampable(r.description, 'text-slate-700') +
                                '</div>' +
                            '</div>'
                        );
                    });
                    $remarksSection.removeClass('hidden');
                } else {
                    $remarksSection.addClass('hidden');
                }

                $panel.find('.client-history-content').removeClass('hidden');

                // Hide "Show more" on items whose text fits within 2 lines
                hideToggleIfNotClamped($panel);

                // Show dot indicator on the history tab button
                const dotModalId = panelId === 'assign-client-history' ? 'assign-job-modal' : 'schedule-job-modal';
                $('#' + dotModalId + ' .job-tab-btn[data-tab="history"] .job-history-dot').removeClass('hidden');
            },
            error: function() {
                $panel.find('.client-history-loading').addClass('hidden');
            }
        });
    }

    $(document).on('click', '.assign-job', function() {
        const $btn        = $(this);
        const doneBy      = $btn.data('done-by');
        const employeeIds = $btn.data('employee-ids') || [];
        const jobId       = Number($btn.data('id'));
        const clientId    = $btn.data('client-id') || '';

        $('#assign-job-form')[0].reset();
        markModalBulkContext($('#assign-job-form'), [jobId], 'Assignment');

        if (typeof window.mcaReset_assign === 'function') {
            window.mcaReset_assign(
                doneBy ? String(doneBy) : null,
                (employeeIds || []).map(String)
            );
        }

        // Store context for lazy history load; reset history state
        $('#assign-job-modal').data({ 'client-id': clientId, 'job-id': jobId, 'history-loaded': false });
        resetHistoryPanel('assign-client-history');
        resetModalTabs('assign-job-modal');
        // Show history tab only for single jobs
        $('#assign-job-modal .job-tab-btn[data-tab="history"]').toggleClass('hidden', !clientId);
        openModal('assign-job-modal');
    });

    $(document).on('click', '#job-bulk-assign', function() {
        const ids = selectedJobIds();
        if (!ids.length) return;

        $('#assign-job-form')[0].reset();
        markModalBulkContext($('#assign-job-form'), ids, 'Assignment');

        if (typeof window.mcaReset_assign === 'function') {
            window.mcaReset_assign(null, []);
        }

        $('#assign-job-modal').data({ 'client-id': '', 'job-id': 0, 'history-loaded': false });
        resetModalTabs('assign-job-modal');
        $('#assign-job-modal .job-tab-btn[data-tab="history"]').addClass('hidden');
        openModal('assign-job-modal');
    });

    function submitBulkForm($form, url, modalId, successMsg, errorMsg) {
        $.ajax({
            url: url,
            method: 'POST',
            data: bulkActionPayload($form),
            headers: { Accept: 'application/json' },
            success: function(res) {
                closeModal(modalId);
                showJobAlert(res.message || successMsg);
                refreshJobsTable();
            },
            error: function(xhr) {
                showJobAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || errorMsg, true);
            }
        });
    }

    function deleteJobs(ids) {
        $.ajax({
            url: "{{ route('admin.jobs.bulk.destroy') }}",
            method: 'POST',
            data: { _token: "{{ csrf_token() }}", _method: 'DELETE', job_ids: ids },
            headers: { Accept: 'application/json' },
            success: function(res) {
                showJobAlert(res.message || (ids.length > 1 ? 'Deleted selected jobs.' : 'Job deleted.'));
                refreshJobsTable();
            },
            error: function() {
                showJobAlert('Failed to delete job' + (ids.length > 1 ? 's' : '') + '.', true);
            }
        });
    }

    $('#assign-job-form').on('submit', function(e) {
        e.preventDefault();
        const ids = $(this).data('jobIds') || [];
        submitBulkForm($(this), "{{ route('admin.jobs.bulk.assign') }}", 'assign-job-modal',
            ids.length > 1 ? 'Assigned selected jobs.' : 'Job assigned.', 'Failed to assign.');
    });

    $(document).on('click', '.status-job', function() {
        markModalBulkContext($('#status-job-form'), [Number($(this).data('id'))], 'Status update');
        $('#status-job-form').find('[name="status"]').val($(this).data('status') || 'Started');
        openModal('status-job-modal');
    });

    $(document).on('click', '#job-bulk-status', function() {
        const ids = selectedJobIds();
        if (!ids.length) return;
        markModalBulkContext($('#status-job-form'), ids, 'Status update');
        openModal('status-job-modal');
    });

    $('#status-job-form').on('submit', function(e) {
        e.preventDefault();
        const ids = $(this).data('jobIds') || [];
        submitBulkForm($(this), "{{ route('admin.jobs.bulk.status.update') }}", 'status-job-modal',
            ids.length > 1 ? 'Updated selected job statuses.' : 'Job status updated.', 'Failed to update status.');
    });

    $(document).on('click', '.schedule-job', function() {
        const $btn     = $(this);
        const jobId    = Number($btn.data('id'));
        const clientId = $btn.data('client-id') || '';
        markModalBulkContext($('#schedule-job-form'), [jobId], 'Reschedule');
        $('#schedule-job-form').find('[name="scheduled_date"]').val($btn.data('scheduled-date') || '');
        $('#schedule-job-form').find('[name="scheduled_time"]').val($btn.data('scheduled-time') || '');
        $('#schedule-job-modal').data({ 'client-id': clientId, 'job-id': jobId, 'history-loaded': false });
        resetHistoryPanel('schedule-client-history');
        resetModalTabs('schedule-job-modal');
        $('#schedule-job-modal .job-tab-btn[data-tab="history"]').toggleClass('hidden', !clientId);
        openModal('schedule-job-modal');
    });

    $(document).on('click', '#job-bulk-schedule', function() {
        const ids = selectedJobIds();
        if (!ids.length) return;
        markModalBulkContext($('#schedule-job-form'), ids, 'Reschedule');
        $('#schedule-job-form').find('[name="scheduled_date"]').val('');
        $('#schedule-job-form').find('[name="scheduled_time"]').val('');
        $('#schedule-job-modal').data({ 'client-id': '', 'job-id': 0, 'history-loaded': false });
        resetModalTabs('schedule-job-modal');
        $('#schedule-job-modal .job-tab-btn[data-tab="history"]').addClass('hidden');
        openModal('schedule-job-modal');
    });

    $('#schedule-job-form').on('submit', function(e) {
        e.preventDefault();
        const ids = $(this).data('jobIds') || [];
        submitBulkForm($(this), "{{ route('admin.jobs.bulk.schedule') }}", 'schedule-job-modal',
            ids.length > 1 ? 'Rescheduled selected jobs.' : 'Job rescheduled.', 'Failed to reschedule.');
    });

    $(document).on('click', '.view-remarks-btn', function() {
        const $btn = $(this);
        $('#remarks-job-form').data('jobId', $btn.data('id'));
        $('#remarks-job-form').find('[name="special_remarks"]').val($btn.data('special-remarks') || '');
        $('#remarks-job-form').find('[name="internal_notes"]').val($btn.data('internal-notes') || '');
        openModal('remarks-job-modal');
    });

    $('#remarks-job-form').on('submit', function(e) {
        e.preventDefault();
        const id = $(this).data('jobId');
        $.ajax({
            url: "{{ url('admin/jobs') }}/" + id + '/remarks',
            method: 'POST',
            data: $(this).serialize() + '&_method=PATCH',
            headers: { Accept: 'application/json' },
            success: function(res) {
                closeModal('remarks-job-modal');
                showJobAlert(res.message || 'Remarks updated.');
                refreshJobsTable();
            },
            error: function(xhr) {
                showJobAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Failed to update remarks.', true);
            }
        });
    });

    $(document).on('click', '.delete-job', function() {
        if (!confirm('Delete this job?')) return;
        deleteJobs([Number($(this).data('id'))]);
    });

    $(document).on('click', '#job-bulk-delete', function() {
        const ids = selectedJobIds();
        if (!ids.length) return;
        if (!confirm('Delete ' + ids.length + ' selected jobs?')) return;
        deleteJobs(ids);
    });
</script>
