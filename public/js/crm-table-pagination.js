/**
 * Shared pagination behaviour for CRM list tables.
 *
 * Page size and page links both re-render the whole table through the reload
 * function named on the markup. The "All" page size is the different one: the
 * server still answers one bounded chunk at a time, and this appends the next
 * chunk into the table already on screen as the sentinel scrolls into view.
 *
 * All wiring comes from data attributes, so a page only has to expose a reload
 * function that takes { per_page, page }.
 */
(function () {
    if (window.crmTablePagination) {
        return;
    }

    var observer = null;
    var loading = false;

    function reloadWith(el, params) {
        var reload = window[el.dataset.reload];

        if (typeof reload === 'function') {
            reload(params);
        }
    }

    function cleanup() {
        if (observer) {
            observer.disconnect();
        }

        observer = null;
        loading = false;
    }

    /** Static params from the markup, merged under whatever the page's filter function reports. */
    function chunkParams(sentinel) {
        var params = {};

        try {
            params = JSON.parse(sentinel.dataset.params || '{}');
        } catch (e) {
            params = {};
        }

        var filters = sentinel.dataset.filters ? window[sentinel.dataset.filters] : null;

        return typeof filters === 'function' ? Object.assign({}, filters(), params) : params;
    }

    /** Counts in the header and footer describe every loaded row, not just the last chunk. */
    function refreshTotals(tbody) {
        var rows = tbody.querySelectorAll('.job-row');

        var loaded = document.getElementById('crm-pagination-loaded');
        if (loaded) {
            loaded.textContent = rows.length;
        }

        var estTotal = document.getElementById('jobs-est-total');
        if (!estTotal) {
            return;
        }

        var minutes = 0;
        rows.forEach(function (row) {
            minutes += parseInt(row.dataset.estMinutes, 10) || 0;
        });

        var hours = Math.floor(minutes / 60);
        var rest = minutes % 60;

        estTotal.textContent = hours > 0 ? (rest > 0 ? hours + 'h ' + rest + 'm' : hours + 'h') : rest + 'm';
    }

    function init() {
        cleanup();

        var sentinel = document.querySelector('.crm-scroll-sentinel');
        if (!sentinel) {
            return;
        }

        var container = document.querySelector(sentinel.dataset.target);
        var tbody = container ? container.querySelector('table tbody') : null;
        if (!tbody) {
            return;
        }

        var lastPage = parseInt(sentinel.dataset.lastPage, 10) || 1;
        var total = parseInt(sentinel.dataset.total, 10) || 0;
        var label = sentinel.dataset.label || 'row';
        var nextPage = parseInt(sentinel.dataset.nextPage, 10) || 2;

        function finish() {
            cleanup();
            sentinel.textContent = 'All ' + total + ' ' + label + (total === 1 ? '' : 's') + ' loaded.';
        }

        function loadNextChunk() {
            if (loading || nextPage > lastPage) {
                return;
            }

            loading = true;
            sentinel.textContent = 'Loading more…';

            $.ajax({
                url: sentinel.dataset.url,
                method: 'GET',
                dataType: 'json',
                data: $.extend({}, chunkParams(sentinel), {
                    per_page: sentinel.dataset.perPage,
                    page: nextPage,
                    rows_only: 1,
                }),
                success: function (res) {
                    tbody.insertAdjacentHTML('beforeend', res.html);
                    nextPage += 1;
                    loading = false;
                    refreshTotals(tbody);

                    if (typeof window.syncJobBulkState === 'function') {
                        window.syncJobBulkState();
                    }

                    if (nextPage > lastPage) {
                        finish();
                        return;
                    }

                    sentinel.textContent = 'Scroll for more…';

                    // A short chunk can leave the sentinel still on screen, and an
                    // unchanged intersection never fires again — so re-arm it.
                    if (observer) {
                        observer.unobserve(sentinel);
                        observer.observe(sentinel);
                    }
                },
                error: function () {
                    loading = false;
                    sentinel.innerHTML = '<button type="button" class="crm-scroll-retry rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100">Could not load more &mdash; retry</button>';
                },
            });
        }

        sentinel.addEventListener('click', function (event) {
            if (event.target.closest('.crm-scroll-retry')) {
                loadNextChunk();
            }
        });

        if (nextPage > lastPage) {
            return;
        }

        observer = new IntersectionObserver(function (entries) {
            if (entries.some(function (entry) { return entry.isIntersecting; })) {
                loadNextChunk();
            }
        }, { rootMargin: '300px' });

        observer.observe(sentinel);
    }

    // Delegated once: the table markup is replaced on every reload.
    document.addEventListener('change', function (event) {
        var select = event.target.closest('.crm-per-page');
        if (!select) {
            return;
        }

        reloadWith(select, { per_page: select.value, page: 1 });
    });

    document.addEventListener('click', function (event) {
        var link = event.target.closest('.crm-pagination-links a[href*="page="]');
        if (!link) {
            return;
        }

        event.preventDefault();

        var links = link.closest('.crm-pagination-links');

        reloadWith(links, {
            per_page: links.dataset.perPage,
            page: new URL(link.href, window.location.href).searchParams.get('page') || 1,
        });
    });

    window.crmTablePagination = { init: init, cleanup: cleanup };
})();
