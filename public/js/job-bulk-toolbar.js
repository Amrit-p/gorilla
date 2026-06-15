/**
 * Shared job-selection registry used by both the table and map views.
 * Manages a Set of selected job IDs, syncs the #job-bulk-toolbar count/visibility,
 * and dispatches the 'jobs:selection-changed' custom event.
 */
(function () {
    var selectedIds = new Set();

    function getIds() {
        return Array.from(selectedIds).filter(function (id) {
            return Number.isInteger(id) && id > 0;
        });
    }

    function getTotalEstimatedMinutes() {
        var total = 0;
        var hasAny = false;
        getIds().forEach(function (id) {
            var row = document.querySelector('.job-row[data-job-id="' + id + '"]');
            if (row) {
                var val = parseInt(row.dataset.estMinutes, 10);
                if (!isNaN(val) && val > 0) { total += val; hasAny = true; }
            }
        });
        return hasAny ? total : null;
    }

    function formatMinutes(mins) {
        var h = Math.floor(mins / 60);
        var m = mins % 60;
        if (h > 0 && m > 0) { return h + 'h ' + m + 'm'; }
        if (h > 0) { return h + 'h'; }
        return m + 'm';
    }

    function syncToolbar() {
        var ids = getIds();
        var toolbar = document.getElementById('job-bulk-toolbar');
        var count   = document.getElementById('job-bulk-count');
        var totalEl = document.getElementById('job-bulk-total');

        if (count)   { count.textContent = ids.length; }
        if (toolbar) { toolbar.classList.toggle('hidden', ids.length === 0); }

        var totalMins = getTotalEstimatedMinutes();
        if (totalEl) {
            if (totalMins !== null && ids.length > 0) {
                totalEl.textContent = formatMinutes(totalMins) + ' est.';
                totalEl.classList.remove('hidden');
            } else {
                totalEl.classList.add('hidden');
            }
        }

        window.dispatchEvent(new CustomEvent('jobs:selection-changed', { detail: { ids: ids, totalEstimatedMinutes: totalMins } }));
    }

    window.crmJobSelection = {
        getIds: getIds,
        getTotalEstimatedMinutes: getTotalEstimatedMinutes,
        formatMinutes: formatMinutes,
        has: function (id) { return selectedIds.has(Number(id)); },
        add: function (id) { selectedIds.add(Number(id)); syncToolbar(); },
        remove: function (id) { selectedIds.delete(Number(id)); syncToolbar(); },
        toggle: function (id) {
            id = Number(id);
            if (selectedIds.has(id)) { selectedIds.delete(id); } else { selectedIds.add(id); }
            syncToolbar();
        },
        setIds: function (ids) {
            selectedIds.clear();
            (ids || []).forEach(function (id) {
                var n = Number(id);
                if (n > 0) { selectedIds.add(n); }
            });
            syncToolbar();
        },
        clear: function () { selectedIds.clear(); syncToolbar(); },
    };

    /* ── Backward-compat alias ──────────────────────────────────────────── */
    window.clearJobBulkSelection = function () { window.crmJobSelection.clear(); };

    /* ── Toolbar clear button ────────────────────────────────────────────── */
    document.addEventListener('click', function (e) {
        if (e.target.closest('#job-bulk-clear')) {
            window.crmJobSelection.clear();
        }
    });

    /* ── Map popup checkbox ──────────────────────────────────────────────── */
    document.addEventListener('change', function (e) {
        var target = e.target;
        if (!target.matches('.map-job-checkbox')) { return; }
        var id = Number(target.dataset.jobId);
        if (target.checked) {
            window.crmJobSelection.add(id);
        } else {
            window.crmJobSelection.remove(id);
        }
    });
})();
