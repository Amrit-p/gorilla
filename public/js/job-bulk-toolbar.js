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

    function syncToolbar() {
        var ids = getIds();
        var toolbar = document.getElementById('job-bulk-toolbar');
        var count   = document.getElementById('job-bulk-count');

        if (count)   { count.textContent = ids.length; }
        if (toolbar) { toolbar.classList.toggle('hidden', ids.length === 0); }

        window.dispatchEvent(new CustomEvent('jobs:selection-changed', { detail: { ids: ids } }));
    }

    window.crmJobSelection = {
        getIds: getIds,
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
