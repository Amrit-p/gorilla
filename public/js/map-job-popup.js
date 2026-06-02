/**
 * Builds the HTML card shown inside map popups (Leaflet + Google Maps).
 * Action buttons rely on document-level jQuery delegation in job-actions-script.blade.php.
 */
window.crmBuildMapJobPopup = function (job) {
    var canManage = window.crmJobsMapConfig && window.crmJobsMapConfig.canManageJobs;

    var employees = (job.assigned_employees || []).length
        ? (job.assigned_employees || []).map(_esc).join(', ')
        : null;

    var dateStr = _formatDate(job.scheduled_date);
    var timeStr = _formatTime(job.scheduled_time);

    var statusColor = _statusColor(job.status);

    return (
        '<div style="width:270px;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif;font-size:13px;line-height:1.5;color:#1e293b;">' +

            /* ── Header ── */
            '<div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px;">' +
                '<a href="' + job.show_url + '" target="_blank" style="font-weight:700;font-size:14px;color:#4338ca;text-decoration:none;letter-spacing:-0.01em;">Job #' + job.id + '</a>' +
                (job.status
                    ? '<span style="padding:2px 9px;border-radius:999px;font-size:10px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;background:' + statusColor.bg + ';color:' + statusColor.text + ';">' + _esc(job.status) + '</span>'
                    : '') +
            '</div>' +

            /* ── Divider ── */
            '<div style="height:1px;background:#e2e8f0;margin-bottom:10px;"></div>' +

            /* ── Client info ── */
            (job.client_name
                ? '<p style="margin:0 0 2px;font-weight:600;font-size:13px;color:#0f172a;">' + _esc(job.client_name) + '</p>'
                : '') +
            (job.client_address
                ? '<div style="display:flex;align-items:flex-start;gap:5px;margin-bottom:10px;">' +
                      _icon('M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z', '#94a3b8') +
                      '<span style="color:#64748b;font-size:12px;line-height:1.4;">' + _esc(job.client_address) + '</span>' +
                  '</div>'
                : '') +

            /* ── Date / time ── */
            (dateStr || timeStr
                ? '<div style="display:flex;align-items:center;gap:5px;margin-bottom:6px;">' +
                      _icon('M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z', '#94a3b8') +
                      '<span style="color:#475569;font-size:12px;">' +
                          (dateStr ? '<strong style="color:#334155;">' + dateStr + '</strong>' : '') +
                          (timeStr ? '<span style="color:#94a3b8;"> &nbsp;·&nbsp; </span>' + timeStr : '') +
                      '</span>' +
                  '</div>'
                : '') +

            /* ── Equipment type ── */
            (job.equipment_name
                ? '<div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">' +
                      '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:' + (job.equipment_color || '#64748b') + ';flex-shrink:0;box-shadow:0 0 0 2px #fff,0 0 0 3px ' + (job.equipment_color || '#64748b') + '44;"></span>' +
                      '<span style="color:#475569;font-size:12px;">' + _esc(job.equipment_name) + '</span>' +
                  '</div>'
                : '') +

            /* ── Assigned mowers ── */
            '<div style="display:flex;align-items:center;gap:5px;margin-bottom:12px;">' +
                _icon('M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z', '#94a3b8') +
                '<span style="color:#475569;font-size:12px;">' +
                    (employees
                        ? employees
                        : '<span style="color:#94a3b8;font-style:italic;">Unassigned</span>') +
                '</span>' +
            '</div>' +

            /* ── Divider ── */
            '<div style="height:1px;background:#e2e8f0;margin-bottom:10px;"></div>' +

            /* ── Action buttons ── */
            '<div style="display:flex;flex-wrap:wrap;gap:6px;">' +
                '<a href="' + job.show_url + '" target="_blank" style="' + _btn('#475569', false) + '">View</a>' +
                (canManage
                    ? '<button class="assign-job" data-id="' + job.id + '" style="' + _btn('#4f46e5', true) + '">Assign</button>' +
                      '<button class="status-job" data-id="' + job.id + '" style="' + _btn('#0891b2', true) + '">Status</button>' +
                      '<button class="delete-job" data-id="' + job.id + '" style="' + _btn('#dc2626', true) + '">Delete</button>'
                    : '') +
            '</div>' +

        '</div>'
    );
};

/* ── helpers ─────────────────────────────────────────────────────── */

function _esc(str) {
    return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function _btn(bg, isFill) {
    return (
        'display:inline-flex;align-items:center;padding:5px 12px;border-radius:6px;font-size:11px;font-weight:600;' +
        'cursor:pointer;border:none;text-decoration:none;transition:opacity .15s;' +
        (isFill
            ? 'background:' + bg + ';color:#fff;'
            : 'background:#f1f5f9;color:' + bg + ';border:1px solid #e2e8f0;')
    );
}

function _icon(path, color) {
    return (
        '<svg style="width:14px;height:14px;flex-shrink:0;margin-top:1px;" viewBox="0 0 24 24" fill="' + color + '">' +
            '<path d="' + path + '"/>' +
        '</svg>'
    );
}

function _formatDate(str) {
    if (!str) return '';
    var d = new Date(str + 'T00:00:00');
    if (isNaN(d.getTime())) return str;
    return d.toLocaleDateString('en-AU', { day: 'numeric', month: 'short', year: 'numeric' });
}

function _formatTime(str) {
    if (!str) return '';
    if (typeof str !== 'string') { str = String(str); }
    var m = str.indexOf('T') !== -1
        ? moment(str)
        : moment(str, ['HH:mm:ss', 'HH:mm', 'H:mm']);
    return m.isValid() ? m.format('h:mm A') : str;
}

function _statusColor(status) {
    if (!status) return { bg: '#f1f5f9', text: '#475569' };
    var s = (status || '').toLowerCase();
    if (s === 'started')   return { bg: '#dcfce7', text: '#15803d' };
    if (s === 'hold')      return { bg: '#fef3c7', text: '#b45309' };
    if (s === 'completed') return { bg: '#dbeafe', text: '#1d4ed8' };
    if (s === 'cancelled') return { bg: '#fee2e2', text: '#b91c1c' };
    if (s === 'assigned')  return { bg: '#ede9fe', text: '#6d28d9' };
    return { bg: '#f1f5f9', text: '#475569' };
}
