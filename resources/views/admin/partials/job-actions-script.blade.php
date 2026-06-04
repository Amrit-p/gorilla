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

    $(document).on('click', '.assign-job', function() {
        const $btn        = $(this);
        const doneBy      = $btn.data('done-by');
        const employeeIds = $btn.data('employee-ids') || [];

        $('#assign-job-form')[0].reset();
        $('#assign-job-form').find('[name="job_id"]').val($btn.data('id'));

        // Restore mower widget state
        if (typeof window.mcaReset_assign === 'function') {
            window.mcaReset_assign(
                doneBy ? String(doneBy) : null,
                (employeeIds || []).map(String)
            );
        }

        openModal('assign-job-modal');
    });

    $('#assign-job-form').on('submit', function(e) {
        e.preventDefault();
        const id = $(this).find('[name="job_id"]').val();
        $.ajax({
            url: "{{ url('/admin/jobs') }}/" + id + "/assign",
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                Accept: 'application/json'
            },
            success: function(res) {
                closeModal('assign-job-modal');
                showJobAlert(res.message);
                {{$filterCallback}}(typeof getFilters === 'function' ? getFilters() : {});
            },
            error: function(xhr) {
                showJobAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] ||
                    'Failed to assign.', true);
            }
        });
    });

    $(document).on('click', '.status-job', function() {
        $('#status-job-form').find('[name="job_id"]').val($(this).data('id'));
        $('#status-job-form').find('[name="status"]').val($(this).data('status') || 'Started');
        openModal('status-job-modal');
    });
    $('#status-job-form').on('submit', function(e) {
        e.preventDefault();
        const id = $(this).find('[name="job_id"]').val();
        $.ajax({
            url: "{{ url('/admin/jobs') }}/" + id + "/status",
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                Accept: 'application/json'
            },
            success: function(res) {
                closeModal('status-job-modal');
                showJobAlert(res.message);
                {{$filterCallback}}(typeof getFilters === 'function' ? getFilters() : {});
            },
            error: function(xhr) {
                showJobAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] ||
                    'Failed to update status.', true);
            }
        });
    });

    $(document).on('click', '.delete-job', function() {
        const id = $(this).data('id');
        if (!confirm('Delete this job?')) return;
        $.ajax({
            url: "{{ url('/admin/jobs') }}/" + id,
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                _method: 'DELETE'
            },
            headers: {
                Accept: 'application/json'
            },
            success: function(res) {
                showJobAlert(res.message);
                {{$filterCallback}}(typeof getFilters === 'function' ? getFilters() : {});
            },
            error: function() {
                showJobAlert('Failed to delete job.', true);
            }
        });
    });
</script>
