function remarkCardHtml(remark) {
    return (
        '<div class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">' +
        '<p class="text-sm text-slate-800 whitespace-pre-wrap">' + jQuery('<div>').text(remark.description).html() + '</p>' +
        '<p class="mt-2 text-xs text-slate-400">' + jQuery('<div>').text(remark.user_name).html() + ' &mdash; ' + jQuery('<div>').text(remark.created_at).html() + '</p>' +
        '</div>'
    );
}

function loadJobRemarks(jobId) {
    if (!window.jobFormRoutes?.clientRemarks || !jQuery('#job-remarks-card').length) {
        return;
    }

    const $last = jQuery('#job-last-remark');
    const $list = jQuery('#job-all-remarks-list');
    const $btn  = jQuery('#job-remarks-toggle-btn');

    $last.html('<p class="animate-pulse text-sm text-slate-400">Loading remarks…</p>');
    $list.addClass('hidden').empty();
    $btn.addClass('hidden').text('');

    jQuery.get(window.jobFormRoutes.clientRemarks, { job_id: jobId || '' }, function (res) {
        if (!res.lastRemark) {
            $last.html('<p class="text-sm text-slate-400">No remarks recorded for this job yet.</p>');
            return;
        }

        $last.html(remarkCardHtml(res.lastRemark));

        const older = (res.allRemarks || []).slice(1);
        if (older.length) {
            older.forEach(function (r) { $list.append(remarkCardHtml(r)); });
            $btn
                .removeClass('hidden')
                .text('Show all ' + res.allRemarks.length + ' remarks')
                .off('click')
                .on('click', function () {
                    const open = $list.hasClass('hidden');
                    $list.toggleClass('hidden', !open);
                    jQuery(this).text(open ? 'Hide older remarks' : 'Show all ' + res.allRemarks.length + ' remarks');
                });
        }
    });
}

jQuery(function () {
    loadJobRemarks(window.jobFormRoutes?.jobId || jQuery('#job-id').val());
});
