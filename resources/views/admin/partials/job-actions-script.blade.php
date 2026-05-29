<script>
    crmDropdown('.job-actions-btn', '.job-actions-menu');
    function showJobAlert(message, isError = false) {
        const baseClass = isError
            ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
            : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
        $('#job-alert').removeClass('hidden').attr('class', baseClass).text(message);
    }
    function openModal(id) { $('#' + id).removeClass('hidden').addClass('flex'); }
    function closeModal(id) { $('#' + id).addClass('hidden').removeClass('flex'); }
    $('[data-close-modal]').on('click', function () { closeModal($(this).data('close-modal')); });

    $(document).on('click', '.assign-job', function () {
        $('#assign-job-form')[0].reset();
        $('#assign-job-form').find('[name="job_id"]').val($(this).data('id'));
        openModal('assign-job-modal');
    });
    $('#assign-job-form').on('submit', function (e) {
        e.preventDefault();
        const id = $(this).find('[name="job_id"]').val();
        $.ajax({
            url: "{{ url('/admin/jobs') }}/" + id + "/assign",
            method: 'POST',
            data: $(this).serialize(),
            headers: { Accept: 'application/json' },
            success: function (res) { closeModal('assign-job-modal'); showJobAlert(res.message); loadJobs(); },
            error: function (xhr) { showJobAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Failed to assign.', true); }
        });
    });

    $(document).on('click', '.status-job', function () {
        $('#status-job-form').find('[name="job_id"]').val($(this).data('id'));
        $('#status-job-form').find('[name="status"]').val($(this).data('status') || 'Started');
        openModal('status-job-modal');
    });
    $('#status-job-form').on('submit', function (e) {
        e.preventDefault();
        const id = $(this).find('[name="job_id"]').val();
        $.ajax({
            url: "{{ url('/admin/jobs') }}/" + id + "/status",
            method: 'POST',
            data: $(this).serialize(),
            headers: { Accept: 'application/json' },
            success: function (res) { closeModal('status-job-modal'); showJobAlert(res.message); loadJobs(); },
            error: function (xhr) { showJobAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Failed to update status.', true); }
        });
    });

    $(document).on('click', '.delete-job', function () {
        const id = $(this).data('id');
        if (!confirm('Delete this job?')) return;
        $.ajax({
            url: "{{ url('/admin/jobs') }}/" + id,
            method: 'POST',
            data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
            headers: { Accept: 'application/json' },
            success: function (res) { showJobAlert(res.message); loadJobs(); },
            error: function () { showJobAlert('Failed to delete job.', true); }
        });
    });
</script>
