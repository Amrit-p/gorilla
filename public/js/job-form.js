/**
 * Job form: create/edit field behavior (client autofill, payment reason toggle, map marker sync).
 */
(function ($) {
    'use strict';

    function validateAllJobSteps() {
        const $form = $('#job-form');
        if (!$form.data('validator') && window.CrmFormValidation) {
            window.CrmFormValidation.initForm($form);
        }
        return $form.valid();
    }

    function fillFromClient($option) {
        if (!$option.length || !$option.val()) {
            return;
        }
        $('#job-client-address').val($option.data('address') || '');
        $('#job-latitude').val($option.data('lat') || '');
        $('#job-longitude').val($option.data('lng') || '');
        if ($option.data('equipment-id')) {
            $('#job-equipment-type-id').val(String($option.data('equipment-id')));
        }
        syncJobEquipmentMarkerColor();
        if (typeof window.crmRefreshAddressPicker === 'function') {
            try {
                window.crmRefreshAddressPicker('job');
            } catch (e) {
                // Google Maps marker not ready yet
            }
        }
        if ($option.data('zone-id')) {
            $('#job-zone-id').val(String($option.data('zone-id')));
        }
        if ($option.data('recurrence-id')) {
            $('#job-recurrence-id').val(String($option.data('recurrence-id')));
        }
        if ($option.data('payment-mode')) {
            $('[name="payment_mode"]').val($option.data('payment-mode'));
        }
        if ($option.data('payment-status')) {
            $('#job-payment-status').val($option.data('payment-status'));
            toggleJobPaymentReason();
        }
        if ($option.data('customer-type')) {
            $('#job-customer-type').val($option.data('customer-type'));
        }
        if ($option.data('site-instructions')) {
            $('#job-site-instructions').val($option.data('site-instructions'));
        }
        var serviceTypes = $option.data('service-types');
        if (serviceTypes) {
            var types = typeof serviceTypes === 'string' ? JSON.parse(serviceTypes) : serviceTypes;
            $('input[name="required_services[]"]').prop('checked', false);
            types.forEach(function (type) {
                $('input[name="required_services[]"][value="' + type + '"]').prop('checked', true);
            });
        }
    }

    function syncJobEquipmentMarkerColor() {
        const $selected = $('#job-equipment-type-id option:selected');
        const color = $selected.data('color') || '#64748b';
        if (typeof window.crmSetAddressPickerMarkerColor === 'function') {
            try {
                window.crmSetAddressPickerMarkerColor('job', color);
            } catch (e) {
                // Google Maps marker not ready yet
            }
        }
    }

    function toggleJobPaymentReason() {
        const status = $('#job-payment-status').val();
        $('#job-payment-reason-wrap').toggleClass('hidden', !['Pending', 'Partial'].includes(status));
    }

    window.validateAllJobSteps = validateAllJobSteps;

    $('#job-client-id').on('change', function () {
        const $option = $(this).find('option:selected');
        if ($option.length && $option.val()) {
            fillFromClient($option);
        }
    });

    $('#job-equipment-type-id').on('change', syncJobEquipmentMarkerColor);

    $('#job-payment-status').on('change', function () {
        toggleJobPaymentReason();
        const validator = $('#job-form').data('validator');
        const $reason = $('[name="payment_pending_reason"]');
        if (validator && $reason.length) {
            validator.element($reason[0]);
        }
    });

    $(function () {
        toggleJobPaymentReason();

        const $clientSelect = $('#job-client-id');
        if ($clientSelect.is('select')) {
            const $client = $clientSelect.find('option:selected');
            const isEditMode = $('#job-form').find('input[name="_method"][value="PATCH"]').length > 0;
            if ($client.val() && !isEditMode) {
                fillFromClient($client);
            }
        }
        syncJobEquipmentMarkerColor();
    });
})(window.jQuery);
