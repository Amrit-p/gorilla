/**
 * Builds the HTML card shown inside map popups (Leaflet + Google Maps).
 * Action buttons rely on document-level jQuery delegation in job-actions-script.blade.php.
 */
window.crmBuildMapJobPopup = function (job) {
    const cfg       = window.crmJobsMapConfig || {};
    const canView   = cfg.canViewJobs;
    const canManage = cfg.canManageJobs;
    const showUrl   = cfg.isMower ? (cfg.mowerJobBaseUrl + '/' + job.id) : job.show_url;

    const employees  = (job.assigned_employees || []).length
        ? job.assigned_employees.map(_esc).join(', ')
        : null;
    const isSelected = window.crmJobSelection ? window.crmJobSelection.has(job.id) : false;

    const dateStr    = _formatDate(job.scheduled_date);
    const timeStr    = _formatTime(job.scheduled_time);
    const sc         = _statusClasses(job.status);
    const equipColor = job.equipment_color || '#64748b';

    const ICON_PIN      = 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z';
    const ICON_CALENDAR = 'M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z';
    const ICON_PEOPLE   = 'M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z';

    return `
<div class="w-[min(320px,calc(100vw-32px))] relative p-3 sm:p-4 font-sans text-[13px] leading-relaxed text-slate-800">

    <button onclick="window.crmCloseMapPopup(this)"
            class="absolute top-3 right-3 w-6 h-6 flex items-center justify-center rounded-md p-0 border-0 bg-transparent text-slate-400 hover:text-slate-600 hover:bg-slate-100 cursor-pointer transition-colors text-base leading-none"
            title="Close">&#x2715;</button>

    <div class="flex items-center gap-2 mb-3 pr-8">
        <a href="${showUrl}" target="_blank"
           class="font-bold text-sm text-indigo-600 hover:text-indigo-800 no-underline tracking-tight">
            Job #${job.id}
        </a>
        ${job.status ? `
        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${sc.bg} ${sc.text}">
            ${_esc(job.status)}
        </span>` : ''}
    </div>

    <div class="h-px bg-slate-100 mb-3"></div>

    ${job.client_name ? `
    <p class="m-0 mb-1 font-semibold text-slate-900">${_esc(job.client_name)}</p>` : ''}

    ${job.client_address ? `
    <div class="flex items-start gap-1.5 mb-2.5">
        ${_icon(ICON_PIN, 'mt-0.5 text-slate-400')}
        <span class="text-[12px] text-slate-500 leading-snug">${_esc(job.client_address)}</span>
    </div>` : ''}

    ${dateStr || timeStr ? `
    <div class="flex items-center gap-1.5 mb-1.5">
        ${_icon(ICON_CALENDAR, 'text-slate-400')}
        <span class="text-[12px] text-slate-500">
            ${dateStr ? `<strong class="text-slate-700 font-semibold">${dateStr}</strong>` : ''}
            ${timeStr ? `<span class="text-slate-400 mx-1">·</span>${timeStr}` : ''}
        </span>
    </div>` : ''}

    ${job.equipment_name ? `
    <div class="flex items-center gap-1.5 mb-1.5">
        <span class="w-3.5 h-3.5 shrink-0 flex items-center justify-center">
            <span class="w-2.5 h-2.5 rounded-full" style="background:${equipColor};box-shadow:0 0 0 2px #fff,0 0 0 3px ${equipColor}44;"></span>
        </span>
        <span class="text-[12px] text-slate-500">${_esc(job.equipment_name)}</span>
    </div>` : ''}

    <div class="flex items-center gap-1.5 mb-3">
        ${_icon(ICON_PEOPLE, 'text-slate-400')}
        <span class="text-[12px] text-slate-500">
            ${employees ?? '<span class="text-slate-400 italic">Unassigned</span>'}
        </span>
    </div>

    <div class="h-px bg-slate-100 mb-3"></div>

    <div class="flex flex-wrap items-center gap-1.5">
        <label class="inline-flex items-center gap-1.5 h-8 sm:h-7 px-3 rounded-md text-[11px] font-semibold cursor-pointer select-none border transition-colors ${isSelected ? 'bg-emerald-50 border-emerald-300 text-emerald-700' : 'bg-slate-50 border-slate-200 text-slate-500 hover:bg-slate-100'}"
               title="Select job for bulk actions">
            <input type="checkbox" class="map-job-checkbox h-3.5 w-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                   data-job-id="${job.id}" ${isSelected ? 'checked' : ''}>
            Select
        </label>
        ${canView ? `
        <a href="${showUrl}" target="_blank"
           class="inline-flex items-center justify-center h-8 sm:h-7 px-3 rounded-md text-[11px] font-semibold no-underline bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200 transition-colors">View</a>` : ''}
        ${canManage && job.edit_url ? `
        <a href="${job.edit_url}" target="_blank"
           class="inline-flex items-center justify-center h-8 sm:h-7 px-3 rounded-md text-[11px] font-semibold no-underline bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">Edit</a>` : ''}
        ${canManage ? `
        <button class="assign-job inline-flex items-center justify-center h-8 sm:h-7 px-3 rounded-md text-[11px] font-semibold bg-indigo-600 text-white border-0 cursor-pointer hover:bg-indigo-700 transition-colors"
                data-id="${job.id}"
                data-done-by="${job.done_by_user_id || ''}"
                data-employee-ids='${JSON.stringify(job.helper_employee_ids || [])}'>Assign</button>
        <button class="status-job inline-flex items-center justify-center h-8 sm:h-7 px-3 rounded-md text-[11px] font-semibold bg-cyan-600 text-white border-0 cursor-pointer hover:bg-cyan-700 transition-colors"
                data-id="${job.id}">Status</button>
        <button class="delete-job inline-flex items-center justify-center h-8 sm:h-7 px-3 rounded-md text-[11px] font-semibold bg-red-600 text-white border-0 cursor-pointer hover:bg-red-700 transition-colors"
                data-id="${job.id}">Delete</button>` : ''}
    </div>

</div>`;
};

/* ── hide native framework close buttons ────────────────────────── */

(function () {
    var style = document.createElement('style');
    style.textContent = '.gm-ui-hover-effect { display: none !important; } .gm-style-iw-chr { height: 0 !important; } .gm-style-iw-c { padding: 0 !important; overflow: hidden !important; max-width: 100% !important; } .gm-style-iw-d { padding: 0 !important; overflow: visible !important; } .leaflet-popup-content-wrapper { overflow: hidden !important; } .leaflet-popup-content { margin: 0 !important; overflow: visible !important; }';
    document.head.appendChild(style);
})();

/* ── popup close ────────────────────────────────────────────────── */

window.crmCloseMapPopup = function (el) {
    var lp = el.closest('.leaflet-popup');
    if (lp) {
        var cb = lp.querySelector('.leaflet-popup-close-button');
        if (cb) { cb.click(); return; }
    }
    var gm = document.querySelector('.gm-ui-hover-effect');
    if (gm) gm.click();
};

/* ── helpers ─────────────────────────────────────────────────────── */

function _esc(str) {
    return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function _icon(path, cls) {
    return `<svg class="w-3.5 h-3.5 shrink-0 fill-current ${cls || ''}" viewBox="0 0 24 24"><path d="${path}"/></svg>`;
}

function _statusClasses(status) {
    if (!status) return { bg: 'bg-slate-100', text: 'text-slate-500' };
    switch ((status || '').toLowerCase()) {
        case 'started':   return { bg: 'bg-green-100',  text: 'text-green-700'  };
        case 'hold':      return { bg: 'bg-amber-100',  text: 'text-amber-700'  };
        case 'completed': return { bg: 'bg-blue-100',   text: 'text-blue-700'   };
        case 'cancelled': return { bg: 'bg-red-100',    text: 'text-red-700'    };
        case 'assigned':  return { bg: 'bg-violet-100', text: 'text-violet-700' };
        default:          return { bg: 'bg-slate-100',  text: 'text-slate-500'  };
    }
}

function _formatDate(str) {
    if (!str) return '';
    const d = new Date(str + 'T00:00:00');
    if (isNaN(d.getTime())) return str;
    return d.toLocaleDateString('en-AU', { day: 'numeric', month: 'short', year: 'numeric' });
}

function _formatTime(str) {
    if (!str) return '';
    if (typeof str !== 'string') str = String(str);
    const m = str.indexOf('T') !== -1
        ? moment(str)
        : moment(str, ['HH:mm:ss', 'HH:mm', 'H:mm']);
    return m.isValid() ? m.format('h:mm A') : str;
}
