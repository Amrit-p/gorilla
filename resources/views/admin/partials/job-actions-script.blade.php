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

    $(document).on('click', '.assign-job', function() {
        const $btn        = $(this);
        const doneBy      = $btn.data('done-by');
        const employeeIds = $btn.data('employee-ids') || [];

        $('#assign-job-form')[0].reset();
        markModalBulkContext($('#assign-job-form'), [Number($btn.data('id'))], 'Assignment');

        // Restore mower widget state
        if (typeof window.mcaReset_assign === 'function') {
            window.mcaReset_assign(
                doneBy ? String(doneBy) : null,
                (employeeIds || []).map(String)
            );
        }

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
        const $btn = $(this);
        markModalBulkContext($('#schedule-job-form'), [Number($btn.data('id'))], 'Reschedule');
        $('#schedule-job-form').find('[name="scheduled_date"]').val($btn.data('scheduled-date') || '');
        $('#schedule-job-form').find('[name="scheduled_time"]').val($btn.data('scheduled-time') || '');
        openModal('schedule-job-modal');
    });

    $(document).on('click', '#job-bulk-schedule', function() {
        const ids = selectedJobIds();
        if (!ids.length) return;
        markModalBulkContext($('#schedule-job-form'), ids, 'Reschedule');
        $('#schedule-job-form').find('[name="scheduled_date"]').val('');
        $('#schedule-job-form').find('[name="scheduled_time"]').val('');
        openModal('schedule-job-modal');
    });

    $('#schedule-job-form').on('submit', function(e) {
        e.preventDefault();
        const ids = $(this).data('jobIds') || [];
        submitBulkForm($(this), "{{ route('admin.jobs.bulk.schedule') }}", 'schedule-job-modal',
            ids.length > 1 ? 'Rescheduled selected jobs.' : 'Job rescheduled.', 'Failed to reschedule.');
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
