(function () {
    'use strict';

    /** Map of canvas id → Chart instance, so re-inits destroy the old one first. */
    var _instances = {};

    function initCharts() {
        document.querySelectorAll('[data-crm-chart]').forEach(function (canvas) {
            var id = canvas.id;
            if (!id) return;

            if (_instances[id]) {
                _instances[id].destroy();
                delete _instances[id];
            }

            var type = canvas.dataset.chartType || 'bar';
            var labels, datasets;

            try {
                labels   = JSON.parse(canvas.dataset.chartLabels   || '[]');
                datasets = JSON.parse(canvas.dataset.chartDatasets  || '[]');
            } catch (e) {
                return;
            }

            if (!datasets.length) return;

            var isDoughnut = (type === 'doughnut' || type === 'pie');

            _instances[id] = new Chart(canvas, {
                type: type,
                data: { labels: labels, datasets: datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: isDoughnut,
                            position: 'bottom',
                            labels: { boxWidth: 12, padding: 12, font: { size: 11 } },
                        },
                        tooltip: { mode: 'index', intersect: false },
                    },
                    scales: isDoughnut ? {} : {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { font: { size: 11 } },
                        },
                    },
                },
            });
        });
    }

    window.crmInitDashboardCharts = initCharts;

    /* Auto-init as soon as Chart.js is available and the DOM is ready. */
    if (typeof Chart !== 'undefined') {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCharts);
        } else {
            initCharts();
        }
    }
}());
