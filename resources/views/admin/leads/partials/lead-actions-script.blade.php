@props([
    'refreshCallback' => 'loadLeads',
])
<script>
    if (typeof crmDropdown === 'function') crmDropdown('.lead-actions-btn', '.lead-actions-menu');

    function showLeadAlert(message, isError = false) {
        const baseClass = isError
            ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
            : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
        $('#lead-alert').removeClass('hidden').attr('class', baseClass).text(message);
    }

    function openModal(id) { $('#' + id).removeClass('hidden').addClass('flex'); }
    function closeModal(id) { $('#' + id).addClass('hidden').removeClass('flex'); }
    $('[data-close-modal]').on('click', function () { closeModal($(this).data('close-modal')); });

    function refreshLeadsAfterAction() {
        {{ $refreshCallback }}(typeof getLeadFilters === 'function' ? getLeadFilters() : {});
    }

    function openLeadStatusModal(ids) {
        $('#lead-status-form')[0].reset();
        $('#lead-status-form').data('leadIds', ids);
        $('#lead-status-form').find('[name="lead_id"]').val(ids.join(','));
        const $context = $('#lead-status-form').find('.bulk-lead-context');
        if (ids.length > 1) {
            $context.removeClass('hidden').text('Status update will apply to ' + ids.length + ' selected leads.');
        } else {
            $context.addClass('hidden').text('');
        }
        openModal('lead-status-modal');
    }

    $(document).on('click', '.status-lead', function () {
        openLeadStatusModal([Number($(this).data('id'))]);
    });

    $(document).on('click', '#lead-bulk-status', function () {
        const ids = typeof window.selectedLeadIds === 'function' ? window.selectedLeadIds() : [];
        if (!ids.length) return;
        openLeadStatusModal(ids);
    });

    $('#lead-status-form').on('submit', function (event) {
        event.preventDefault();
        const ids = $(this).data('leadIds') || [];
        if (!ids.length) return;
        $.ajax({
            url: "{{ url('/admin/leads') }}/" + ids.join(',') + "/status",
            method: 'POST',
            data: $(this).serialize(),
            headers: { 'Accept': 'application/json' },
            success: function (res) { closeModal('lead-status-modal'); showLeadAlert(res.message); refreshLeadsAfterAction(); },
            error: function (xhr) { showLeadAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Failed to update status.', true); }
        });
    });

    $(document).on('click', '.view-lead', function () {
        const id = $(this).data('id');
        $.get("{{ url('/admin/leads') }}/" + id, function (res) {
            const notes = (res.notes || []).map(n => `<div class="rounded-md border border-slate-200 p-2"><p>${n.note}</p><p class="mt-1 text-xs text-slate-500">${n.user_name} • ${n.created_at}</p></div>`);
            $('#lead-detail-content').html(notes.length ? notes.join('') : '<p class="text-slate-500">No timeline notes yet.</p>');
            $('#lead-note-form').find('[name="lead_id"]').val(res.lead.id);
            openModal('lead-detail-modal');
        });
    });

    $(document).on('click', '#lead-bulk-timeline', function () {
        const ids = typeof window.selectedLeadIds === 'function' ? window.selectedLeadIds() : [];
        if (!ids.length) return;
        $('#lead-note-form')[0].reset();
        $('#lead-note-form').find('[name="lead_id"]').val(ids.join(','));
        $('#lead-detail-content').html('<p class="text-slate-500">Adding a note will apply to ' + ids.length + ' selected leads.</p>');
        openModal('lead-detail-modal');
    });

    $('#lead-note-form').on('submit', function (event) {
        event.preventDefault();
        const id = $(this).find('[name="lead_id"]').val();
        const isBulk = id.indexOf(',') !== -1;
        $.ajax({
            url: "{{ url('/admin/leads') }}/" + id + "/notes",
            method: 'POST',
            data: $(this).serialize(),
            headers: { 'Accept': 'application/json' },
            success: function (res) {
                showLeadAlert(res.message);
                $('#lead-note-form').find('[name="note"]').val('');
                if (isBulk) {
                    closeModal('lead-detail-modal');
                } else {
                    $('.view-lead[data-id="' + id + '"]').first().trigger('click');
                }
            },
            error: function (xhr) { showLeadAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Failed to add note.', true); }
        });
    });

    function deleteLeads(ids) {
        $.ajax({
            url: "{{ url('/admin/leads') }}/" + ids.join(','),
            method: 'POST',
            data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
            headers: { 'Accept': 'application/json' },
            success: function (res) { showLeadAlert(res.message); refreshLeadsAfterAction(); },
            error: function (xhr) { showLeadAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Failed to delete lead' + (ids.length > 1 ? 's' : '') + '.', true); }
        });
    }

    $(document).on('click', '.delete-lead', function () {
        if (!confirm('Delete this lead?')) return;
        deleteLeads([Number($(this).data('id'))]);
    });

    $(document).on('click', '#lead-bulk-delete', function () {
        const ids = typeof window.selectedLeadIds === 'function' ? window.selectedLeadIds() : [];
        if (!ids.length) return;
        if (!confirm('Delete ' + ids.length + ' selected leads?')) return;
        deleteLeads(ids);
    });
</script>
