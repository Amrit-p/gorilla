(function ($) {
    'use strict';

    let INDEX_URL = '';
    const filters = {
        scope: 'today',
        date_range: null,
    };

    let currentRequest = null;
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
        const $list = $('#mower-job-list');
        $list.css('opacity', loading ? '0.5' : '1');

        const overlayId = 'mower-loading-overlay';
        if (loading) {
            if ($list.css('position') === 'static') { $list.css('position', 'relative'); }
            if (!document.getElementById(overlayId)) {
                const $overlay = $('<div>')
                    .attr('id', overlayId)
                    .css({
                        position: 'absolute',
                        inset: '0',
                        display: 'flex',
                        'align-items': 'center',
                        'justify-content': 'center',
                        'background': 'rgba(255,255,255,0.6)',
                        'backdrop-filter': 'blur(2px)',
                        'z-index': 999,
                    });
                const $spinner = $('<div>').css({
                    width: '36px',
                    height: '36px',
                    'border-radius': '50%',
                    border: '4px solid #e5e7eb',
                    'border-top-color': '#059669',
                    animation: 'mower-spin 1s linear infinite'
                });

                $overlay.append($spinner);
                $list.prepend($overlay);

                // add keyframes if not present
                if (!document.getElementById('mower-loading-style')) {
                    const style = document.createElement('style');
                    style.id = 'mower-loading-style';
                    style.innerHTML = '@keyframes mower-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
                    document.head.appendChild(style);
                }
            }
        } else {
            $('#' + overlayId).remove();
        }
    }

    function updateScopeButtons(filters = {}) {
        if (typeof filters !== 'object') {
            console.error("filters must be object given ", typeof filters);
            return;
        }

        $('.mower-scope').each(function () {
            const $btn = $(this);
            const isActive = $btn.data('scope') === filters.scope;
            $btn.toggleClass('bg-emerald-700 text-white', isActive)
                .toggleClass('bg-white text-slate-600 shadow-sm', !isActive);
        });
    }

    function updateAnalyticsCards(cards, opts) {
        opts = opts || {};
        if (!cards) return;
        if (cards.range_jobs) $('#mower-analytics-completed').text(cards.range_jobs.value || '0');
        if (cards.completed_hours) $('#mower-analytics-hours').text(cards.completed_hours.value || '0h');
        // Only update upcoming card when not suppressed (e.g., when date-range change shouldn't affect it)
        if (!opts.suppressUpcoming && cards.upcoming_jobs) {
            $('#mower-analytics-upcoming').text(cards.upcoming_jobs.value || '0');
        }
    }

    function updateExportLink(filters) {
        filters = filters || {};
        const base = (window.mowerRoutes || {}).exportPdf;
        if (!base) return;
        const params = new URLSearchParams();
        params.set('scope', filters.scope || 'today');
        const dr = filters.date_range;
        const today = moment ? moment().format('YYYY-MM-DD') : new Date().toISOString().slice(0, 10);
        params.set('date_range[start]', (dr && dr.start) ? dr.start : today);
        params.set('date_range[end]', (dr && dr.end) ? dr.end : today);
        $('#mower-export-pdf').attr('href', base + '?' + params.toString());
    }

    function loadScope(filters) {
        filters = filters || {};
        updateScopeButtons(filters);
        updateExportLink(filters);
        hideAlert();
        setLoading(true);

        const data = { scope: filters.scope };
        const dr = filters.date_range;
        if (dr && dr.start) {
            data.date_range = { start: dr.start, end: dr.end || dr.start };
        }

        if (currentRequest && typeof currentRequest.abort === 'function') {
            try { currentRequest.abort(); } catch (e) { /* ignore */ }
        }

        currentRequest = $.ajax({
            url: INDEX_URL,
            method: 'GET',
            data: data,
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .done(function (response) {
                try {
                    const html = response && response.html ? response.html : '<div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">No jobs in this list.</div>';
                    $('#mower-job-list').html(html);
                    updateAnalyticsCards(response && response.analytics ? response.analytics : null, {});
                } catch (err) {
                    console.error('Failed to render mower jobs response', err, response);
                    $('#mower-job-list').html('<div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">Error loading jobs.</div>');
                }
            })
            .fail(function (xhr, textStatus) {
                if (textStatus === 'abort') return;
                showAlert('Failed to load jobs. Please try again.', true);
            })
            .always(function () {
                setLoading(false);
                currentRequest = null;
            });
    }

    $(document).on('click', '.mower-scope', function () {
        const scope = String($(this).data('scope') || '');
        if (!scope) return;

        if (scope === 'today' || scope === 'upcoming') {
            const today = moment ? moment().format('YYYY-MM-DD') : new Date().toISOString().slice(0, 10);
            filters.date_range = { start: today, end: today };
            const $pickerInput = $('#mower-schedule-date');
            const picker = $pickerInput.data('daterangepicker');
            if (picker) {
                picker.setStartDate(today);
                picker.setEndDate(today);
            }
            $pickerInput.val(today + ' – ' + today);
            $pickerInput.siblings('button').removeClass('hidden');
        }

        filters.scope = scope;
        loadScope({ ...filters });
    });

    $(document).on('change', '#mower-schedule-date', function () {
        const start = $('#mower-schedule-date-start').val();
        const end = $('#mower-schedule-date-end').val();
        if (start && end) {
            filters.date_range = { start: start, end: end };
        } else {
            filters.date_range = null;
        }
        loadScope({ ...filters });
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
    function saveStatusAction(button) {
        const routes = window.mowerJobRoutes || {};
        if (!routes.status) return $.Deferred().resolve();
        const status = $('#mower-status').val();
        const $btn = button ? $(button).prop('disabled', true).text('Saving…') : null;

        const jq = $.ajax({
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
                if ($btn) { $btn.prop('disabled', false).text('Save status'); }
            });

        return jq;
    }


    // ─── Job detail: payment reason visibility ──────────────────────────────

    function togglePaymentReason() {
        const val = $('#mower-payment-status').val();
        const isPending = val === 'Pending';
        const isPartial = val === 'Partial';
        $('#mower-payment-reason-wrap').toggleClass('hidden', !isPending);
        $('#mower-paid-amount-wrap').toggleClass('hidden', !isPartial);
    }

    $(document).on('change', '#mower-payment-status', togglePaymentReason);


    // ─── Job detail: save payment ───────────────────────────────────────────
    function savePaymentAction(button) {
        const routes = window.mowerJobRoutes || {};
        if (!routes.payment) return $.Deferred().resolve();
        const status = $('#mower-payment-status').val();
        const reason = String($('#mower-payment-reason').val()).trim();
        const firstPayment = String($('#mower-first-payment').val()).trim();
        const secondPayment = String($('#mower-second-payment').val()).trim();
        const isPending = status === 'Pending';
        const isPartial = status === 'Partial';

        if (isPending && !reason) {
            showAlert('Please enter a reason for pending payment.', true);
            return $.Deferred().reject();
        }
        if (isPartial && !firstPayment) {
            showAlert('Please enter the First Payment amount.', true);
            return $.Deferred().reject();
        }
        if (isPartial && !secondPayment) {
            showAlert('Please enter the Second Payment details.', true);
            return $.Deferred().reject();
        }

        const $btn = button ? $(button).prop('disabled', true).text('Saving…') : null;

        const jq = $.ajax({
            url: routes.payment,
            method: 'PATCH',
            data: {
                payment_status: status,
                payment_pending_reason: isPending ? reason : '',
                first_payment: isPartial ? firstPayment : '',
                second_payment: isPartial ? secondPayment : '',
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
                if ($btn) { $btn.prop('disabled', false).text('Save payment'); }
            });

        return jq;
    }


    // ─── Job detail: log time on site ──────────────────────────────────────
    function saveTimeAction(button) {
        const routes = window.mowerJobRoutes || {};
        if (!routes.consumedTime) return $.Deferred().resolve();
        const minutes = parseInt($('#mower-consumed-time').val(), 10);

        if (!minutes || minutes < 1) {
            showAlert('Please enter a valid number of minutes.', true);
            return $.Deferred().reject();
        }

        const $btn = button ? $(button).prop('disabled', true).text('Saving…') : null;

        const jq = $.ajax({
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
                if ($btn) { $btn.prop('disabled', false).text('Log time'); }
            });

        return jq;
    }



    function setUploadLoading(kind, loading, count) {
        const labelId = 'mower-' + kind + '-label';
        const textId = 'mower-' + kind + '-upload-text';
        const inputId = 'mower-' + kind + '-input';
        const $label = $('#' + labelId);
        const $text = $('#' + textId);

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


    function saveRemarkAction(button) {
        const routes = window.mowerJobRoutes || {};
        if (!routes.remark) return $.Deferred().resolve();

        const description = String($('#mower-remark-text').val()).trim();
        if (!description) {
            showAlert('Please enter instructions before saving.', true);
            return $.Deferred().reject();
        }

        const $btn = button ? $(button).prop('disabled', true).text('Saving…') : null;

        const jq = $.ajax({
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
                if ($btn) { $btn.prop('disabled', false).text('Save instructions'); }
            });

        return jq;
    }



    $(document).on('click', '#mower-save-all', function () {
        if (window.mowerJobIsVerified) return;
        const routes = window.mowerJobRoutes || {};
        if (!routes.update) return;

        const paymentStatus = $('#mower-payment-status').val();
        const isPendingPayment = paymentStatus === 'Pending';
        const isPartial = paymentStatus === 'Partial';
        const reason = String($('#mower-payment-reason').val()).trim();
        const firstPayment = String($('#mower-first-payment').val()).trim();
        const secondPayment = String($('#mower-second-payment').val()).trim();

        if (isPendingPayment && !reason) {
            showAlert('Please enter a reason for pending payment.', true);
            return;
        }
        if (isPartial && !firstPayment) {
            showAlert('Please enter the First Payment amount.', true);
            return;
        }
        if (isPartial && !secondPayment) {
            showAlert('Please enter the Second Payment details.', true);
            return;
        }

        const minutes = parseInt($('#mower-consumed-time').val(), 10) || 0;
        const description = String($('#mower-remark-text').val()).trim();

        const $btn = $(this).prop('disabled', true).text('Saving…');

        const data = {
            status: $('#mower-status').val(),
            payment_status: paymentStatus,
            payment_pending_reason: isPendingPayment ? reason : '',
            first_payment: isPartial ? firstPayment : '',
            second_payment: isPartial ? secondPayment : '',
            consumed_time_minutes: minutes || '',
            description: description,
            _token: $('meta[name="csrf-token"]').attr('content'),
        };

        $.ajax({
            url: routes.update,
            method: 'PATCH',
            data: data,
            dataType: 'json',
        })
            .done(function (response) {
                showAlert(response.message || 'Changes saved.', false);
                if (description) {
                    $('#mower-remark-text').val('');
                }
            })
            .fail(function (xhr) {
                const errs = xhr.responseJSON?.errors;
                const msg = errs ? Object.values(errs).flat().join(' ') : (xhr.responseJSON?.message || 'Failed to save changes.');
                showAlert(msg, true);
            })
            .always(function () {
                $btn.prop('disabled', false).text('Save changes');
            });
    });

    $(function () {
        INDEX_URL = (window.mowerRoutes || {}).index || '';

        filters.scope = window.mowerInitialScope
            || ($('.mower-scope.bg-emerald-700').first().data('scope'))
            || 'today';

        if (window.mowerInitialDate) {
            filters.date_range = {
                start: window.mowerInitialDate,
                end: window.mowerInitialEndDate || window.mowerInitialDate,
            };
        }

        updateScopeButtons(filters);
        updateExportLink(filters);
    });

}(jQuery));
