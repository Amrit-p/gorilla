(function ($) {
    'use strict';

    const INDEX_URL = (window.mowerRoutes || {}).index || '';
    let activeScope = null;
    let activeDate = window.mowerInitialDate || new Date().toISOString().slice(0, 10);

    let alertTimeout = null;

    function showAlert(message, isError) {
        clearTimeout(alertTimeout);
        $('#mower-alert')
            .removeClass('hidden bg-red-50 border-red-200 text-red-700 bg-emerald-50 border-emerald-200 text-emerald-700')
            .addClass(isError
                ? 'bg-red-50 border border-red-200 text-red-700'
                : 'bg-emerald-50 border border-emerald-200 text-emerald-700')
            .text(message);
        alertTimeout = setTimeout(hideAlert, 4000);
    }

    function hideAlert() {
        $('#mower-alert').addClass('hidden').text('');
    }

    function setLoading(loading) {
        $('#mower-job-list').css('opacity', loading ? '0.5' : '1');
    }

    function updateScopeButtons(scope) {
        $('.mower-scope').each(function () {
            const $btn = $(this);
            const isActive = $btn.data('scope') === scope;
            $btn.toggleClass('bg-emerald-700 text-white', isActive)
                .toggleClass('bg-white text-slate-600 shadow-sm', !isActive);
        });
    }

    function updateAnalyticsCards(cards) {
        if (!cards) return;
        if (cards.todays_jobs)    $('#mower-analytics-completed').text(cards.todays_jobs.value    || '0');
        if (cards.completed_hours) $('#mower-analytics-hours').text(cards.completed_hours.value || '0h');
        if (cards.pending_jobs)   $('#mower-analytics-pending').text(cards.pending_jobs.value   || '0');
    }

    function updateExportLink(scope, date) {
        const base = (window.mowerRoutes || {}).exportPdf;
        if (!base) return;
        const url = base + '?scope=' + encodeURIComponent(scope) + '&schedule_date=' + encodeURIComponent(date);
        $('#mower-export-pdf').attr('href', url);
    }

    function loadScope(scope, date) {
        const targetDate = date || activeDate;
        if (scope === activeScope && targetDate === activeDate && !date) {
            return;
        }
        activeScope = scope;
        activeDate = targetDate;

        updateScopeButtons(scope);
        updateExportLink(scope, targetDate);
        hideAlert();
        setLoading(true);

        $.ajax({
            url: INDEX_URL,
            method: 'GET',
            data: { scope: scope, schedule_date: targetDate },
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .done(function (response) {
                $('#mower-job-list').html(response.html || '');
                updateAnalyticsCards(response.analytics || null);
            })
            .fail(function () {
                showAlert('Failed to load jobs. Please try again.', true);
            })
            .always(function () {
                setLoading(false);
            });
    }

    $(document).on('click', '.mower-scope', function () {
        const scope = String($(this).data('scope') || '');
        if (scope) {
            loadScope(scope);
        }
    });

    $(document).on('change', '#mower-schedule-date', function () {
        const date = $(this).val();
        if (!date) return;
        activeScope = null;
        loadScope('today', date);
        // Switch the Today button to active state immediately
        updateScopeButtons('today');
    });

    // ─── Job detail: gallery ────────────────────────────────────────────────

    function renderGallery(galleryId, kind, images, deleteUrlTemplate) {
        const $gallery = $('#' + galleryId).empty();
        if (!images.length) {
            return;
        }
        images.forEach(function (img) {
            const deleteUrl = (deleteUrlTemplate || '').replace('__IMAGE__', img.id);
            const $item = $('<div>').addClass('group relative aspect-square overflow-hidden rounded-lg bg-slate-100').attr('data-image-id', img.id);
            const $link = $('<a>').attr({ href: img.url || '#', target: '_blank', rel: 'noopener' }).addClass('block h-full w-full');
            const $imgEl = $('<img>')
                .attr({ src: img.thumb_url || img.url, alt: kind.charAt(0).toUpperCase() + kind.slice(1) + ' photo', loading: 'lazy', decoding: 'async' })
                .addClass('h-full w-full object-cover')
                .on('error', function () {
                    $(this).closest('a').replaceWith(
                        $('<div>').addClass('flex h-full w-full items-center justify-center bg-slate-200 text-xs text-slate-400').text('No preview')
                    );
                });
            $link.append($imgEl);
            const $del = $('<button>').attr({ type: 'button', 'data-image-id': img.id, 'data-kind': kind, 'aria-label': 'Delete photo' })
                .addClass('mower-delete-image absolute right-1 top-1 rounded-full bg-red-600/90 px-2 py-0.5 text-[10px] font-semibold text-white')
                .text('Delete');
            $item.append($link, $del);
            $gallery.append($item);
        });
    }

    // ─── Job detail: save status ────────────────────────────────────────────

    $(document).on('click', '#mower-save-status', function () {
        const routes = window.mowerJobRoutes || {};
        if (!routes.status) return;
        const status = $('#mower-status').val();
        const $btn = $(this).prop('disabled', true).text('Saving…');

        $.ajax({
            url: routes.status,
            method: 'PATCH',
            data: { status: status, _token: $('meta[name="csrf-token"]').attr('content') },
            dataType: 'json',
        })
        .done(function (response) {
            showAlert(response.message || 'Status updated.', false);
        })
        .fail(function (xhr) {
            const errs = xhr.responseJSON?.errors;
            const msg = errs ? Object.values(errs).flat().join(' ') : (xhr.responseJSON?.message || 'Failed to update status.');
            showAlert(msg, true);
        })
        .always(function () {
            $btn.prop('disabled', false).text('Save status');
        });
    });

    // ─── Job detail: payment reason visibility ──────────────────────────────

    function togglePaymentReason() {
        const val = $('#mower-payment-status').val();
        const needsReason = val === 'Pending' || val === 'Partial';
        const isPartial = val === 'Partial';
        $('#mower-payment-reason-wrap').toggleClass('hidden', !needsReason);
        $('#mower-paid-amount-wrap').toggleClass('hidden', !isPartial);
    }

    $(document).on('change', '#mower-payment-status', togglePaymentReason);

    // ─── Job detail: save payment ───────────────────────────────────────────

    $(document).on('click', '#mower-save-payment', function () {
        const routes = window.mowerJobRoutes || {};
        if (!routes.payment) return;
        const status = $('#mower-payment-status').val();
        const reason = String($('#mower-payment-reason').val()).trim();
        const paidAmount = String($('#mower-paid-amount').val()).trim();
        const needsReason = status === 'Pending' || status === 'Partial';
        const isPartial = status === 'Partial';

        if (needsReason && !reason) {
            showAlert('Please enter a reason for ' + status + ' payment.', true);
            return;
        }
        if (isPartial && !paidAmount) {
            showAlert('Please enter the amount received from the customer.', true);
            return;
        }

        const $btn = $(this).prop('disabled', true).text('Saving…');

        $.ajax({
            url: routes.payment,
            method: 'PATCH',
            data: {
                payment_status: status,
                payment_pending_reason: needsReason ? reason : '',
                paid_amount: isPartial ? paidAmount : '',
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            dataType: 'json',
        })
        .done(function (response) {
            showAlert(response.message || 'Payment updated.', false);
        })
        .fail(function (xhr) {
            const errs = xhr.responseJSON?.errors;
            const msg = errs ? Object.values(errs).flat().join(' ') : (xhr.responseJSON?.message || 'Failed to save payment.');
            showAlert(msg, true);
        })
        .always(function () {
            $btn.prop('disabled', false).text('Save payment');
        });
    });

    // ─── Job detail: log time on site ──────────────────────────────────────

    $(document).on('click', '#mower-save-time', function () {
        const routes = window.mowerJobRoutes || {};
        if (!routes.consumedTime) return;
        const minutes = parseInt($('#mower-consumed-time').val(), 10);

        if (!minutes || minutes < 1) {
            showAlert('Please enter a valid number of minutes.', true);
            return;
        }

        const $btn = $(this).prop('disabled', true).text('Saving…');

        $.ajax({
            url: routes.consumedTime,
            method: 'PATCH',
            data: { consumed_time_minutes: minutes, _token: $('meta[name="csrf-token"]').attr('content') },
            dataType: 'json',
        })
        .done(function (response) {
            showAlert(response.message || 'Time logged.', false);
        })
        .fail(function (xhr) {
            const errs = xhr.responseJSON?.errors;
            const msg = errs ? Object.values(errs).flat().join(' ') : (xhr.responseJSON?.message || 'Failed to log time.');
            showAlert(msg, true);
        })
        .always(function () {
            $btn.prop('disabled', false).text('Log time');
        });
    });

    // ─── Job detail: upload photos ──────────────────────────────────────────

    function setUploadLoading(kind, loading, count) {
        const labelId = 'mower-' + kind + '-label';
        const textId  = 'mower-' + kind + '-upload-text';
        const inputId = 'mower-' + kind + '-input';
        const $label  = $('#' + labelId);
        const $text   = $('#' + textId);

        if (loading) {
            $label.addClass('opacity-50 pointer-events-none cursor-wait');
            $('#' + inputId).prop('disabled', true);
            $text.text('Uploading ' + count + ' photo' + (count !== 1 ? 's' : '') + '…');
        } else {
            $label.removeClass('opacity-50 pointer-events-none cursor-wait');
            $('#' + inputId).prop('disabled', false);
            $text.text(kind === 'before' ? 'Add before photos' : 'Add after photos');
        }
    }

    function uploadImages(inputId, galleryId, kind, routeKey, deleteTemplateKey) {
        const input = document.getElementById(inputId);
        if (!input || !input.files.length) return;
        const routes = window.mowerJobRoutes || {};
        if (!routes[routeKey]) return;

        const count = input.files.length;
        const formData = new FormData();
        Array.from(input.files).forEach(function (file) { formData.append('images[]', file); });
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        $(input).val('');
        setUploadLoading(kind, true, count);

        $.ajax({
            url: routes[routeKey],
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
        })
        .done(function (response) {
            const images = response.images || [];
            renderGallery(galleryId, kind, images, routes[deleteTemplateKey]);
            showAlert(
                images.length
                    ? (response.message || (count + ' photo' + (count !== 1 ? 's' : '') + ' uploaded.'))
                    : 'No images returned — please try again.',
                images.length === 0
            );
            const $gallery = document.getElementById(galleryId);
            if ($gallery) {
                $gallery.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        })
        .fail(function (xhr) {
            const errs = xhr.responseJSON?.errors;
            const msg = errs ? Object.values(errs).flat().join(' ') : (xhr.responseJSON?.message || 'Upload failed. Please try again.');
            showAlert(msg, true);
            const $label = document.getElementById('mower-' + kind + '-label');
            if ($label) { $label.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }
        })
        .always(function () {
            setUploadLoading(kind, false, 0);
        });
    }

    $(document).on('change', '#mower-before-input', function () {
        uploadImages('mower-before-input', 'mower-before-gallery', 'before', 'before', 'deleteBeforeTemplate');
    });

    $(document).on('change', '#mower-after-input', function () {
        uploadImages('mower-after-input', 'mower-after-gallery', 'after', 'after', 'deleteAfterTemplate');
    });

    // ─── Job detail: delete photo ───────────────────────────────────────────

    $(document).on('click', '.mower-delete-image', function () {
        const routes = window.mowerJobRoutes || {};
        const imageId = $(this).data('image-id');
        const kind = String($(this).data('kind') || '');
        const isAfter = kind === 'after';
        const template = isAfter ? routes.deleteAfterTemplate : routes.deleteBeforeTemplate;
        const galleryId = isAfter ? 'mower-after-gallery' : 'mower-before-gallery';
        if (!template || !imageId) return;

        const $item = $(this).closest('[data-image-id]').css('opacity', '0.5');

        $.ajax({
            url: template.replace('__IMAGE__', imageId),
            method: 'DELETE',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            dataType: 'json',
        })
        .done(function (response) {
            renderGallery(galleryId, kind, response.images || [], template);
            showAlert(response.message || 'Photo deleted.', false);
        })
        .fail(function (xhr) {
            $item.css('opacity', '1');
            showAlert(xhr.responseJSON?.message || 'Failed to delete photo.', true);
        });
    });

    // ─── Job detail: next visit instructions ───────────────────────────────

    $(document).on('click', '#mower-save-remark', function () {
        const routes = window.mowerJobRoutes || {};
        if (!routes.remark) return;

        const description = String($('#mower-remark-text').val()).trim();
        if (!description) {
            showAlert('Please enter instructions before saving.', true);
            return;
        }

        const $btn = $(this).prop('disabled', true).text('Saving…');

        $.ajax({
            url: routes.remark,
            method: 'POST',
            data: { description: description, _token: $('meta[name="csrf-token"]').attr('content') },
            dataType: 'json',
        })
            .done(function (response) {
                showAlert(response.message || 'Instructions saved.', false);
                $('#mower-remark-text').val('');
            })
            .fail(function (xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to save. Please try again.';
                showAlert(msg, true);
            })
            .always(function () {
                $btn.prop('disabled', false).text('Save instructions');
            });
    });

    $(function () {
        const $active = $('.mower-scope').filter(function () {
            return $(this).hasClass('bg-emerald-700');
        }).first();
        activeScope = String($active.data('scope') || 'today');
        updateExportLink(activeScope, activeDate);
    });

}(jQuery));
