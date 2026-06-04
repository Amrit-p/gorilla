/**
 * Job form: step wizard, customer autofill, mower workloads & suggestions.
 */
(function ($) {
    'use strict';

    const STEPS = ['customer', 'site', 'mower'];
    const NEXT_LABELS = {
        customer: 'Continue to site details',
        site: 'Continue to mower assignment',
    };

    let maxReachedIndex = 0;

    function getWizard() {
        return $('#job-wizard');
    }

    function currentStepIndex() {
        return STEPS.indexOf(getCurrentStep());
    }

    function getCurrentStep() {
        const $active = $('.job-wizard-step.is-active').first();
        return ($active.data('step') || 'customer').toString();
    }

    function ensureValidator() {
        const $form = $('#job-form');
        if (!$form.data('validator') && window.CrmFormValidation) {
            window.CrmFormValidation.initForm($form);
        }
        return $form.validate();
    }

    function showStepValidationAlert(message) {
        const $alert = $('#job-form-alert');
        if (!$alert.length) {
            return;
        }
        $alert
            .removeClass('hidden')
            .attr(
                'class',
                'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
            )
            .text(message || 'Please fix the highlighted fields before continuing.');
    }

    function clearStepValidationAlert() {
        $('#job-form-alert').addClass('hidden').text('');
    }

    function validateStepPanel($panel) {
        const validator = ensureValidator();
        if (!validator || !$panel.length) {
            return true;
        }

        let valid = true;
        const validatedNames = {};

        $panel.find('input, select, textarea').each(function () {
            const $field = $(this);
            const type = ($field.attr('type') || '').toLowerCase();
            if (type === 'checkbox' || type === 'file') {
                return;
            }
            if (type === 'hidden' && !$field.is('[data-validate-group-proxy]')) {
                return;
            }
            const name = $field.attr('name');
            if (name) {
                if (validatedNames[name]) {
                    return;
                }
                validatedNames[name] = true;
            }
            if (!validator.element(this)) {
                valid = false;
            }
        });

        return valid;
    }

    function validateCurrentStep() {
        const step = getCurrentStep();
        const $panel = $('#job-tab-' + step);
        const valid = validateStepPanel($panel);

        if (!valid) {
            showStepValidationAlert();
            const $firstError = $panel.find('.border-red-500, [data-validate-group].ring-red-400').first();
            if ($firstError.length) {
                $('html, body').animate({ scrollTop: $firstError.offset().top - 120 }, 200);
            }
            return false;
        }

        clearStepValidationAlert();
        return true;
    }

    function validateAllJobSteps() {
        let firstInvalidStep = null;
        let allValid = true;

        STEPS.forEach(function (step) {
            const $panel = $('#job-tab-' + step);
            if (!validateStepPanel($panel)) {
                allValid = false;
                if (!firstInvalidStep) {
                    firstInvalidStep = step;
                }
            }
        });

        if (!allValid && firstInvalidStep) {
            maxReachedIndex = Math.max(maxReachedIndex, STEPS.indexOf(firstInvalidStep));
            switchJobTab(firstInvalidStep, { force: true });
            showStepValidationAlert('Please complete all required fields on each step before submitting.');
        } else {
            clearStepValidationAlert();
        }

        return allValid;
    }

    function updateStepperUi(step) {
        const activeIndex = STEPS.indexOf(step);

        $('.job-wizard-step').each(function () {
            const $item = $(this);
            const index = parseInt($item.data('step-index'), 10) - 1;
            const $badge = $item.find('.job-wizard-step-badge');
            const isActive = index === activeIndex;
            const isComplete = index < activeIndex;
            const isReachable = index <= maxReachedIndex;

            $item.toggleClass('is-active', isActive);
            $item.toggleClass('opacity-50', !isActive && !isComplete);
            $item.toggleClass('cursor-pointer', isReachable && !isActive);
            $item.attr('aria-current', isActive ? 'step' : false);

            if (isComplete) {
                $badge
                    .removeClass('border-slate-300 bg-white text-slate-500 border-emerald-600 bg-emerald-600 text-white')
                    .addClass('border-emerald-600 bg-emerald-600 text-white')
                    .html('&#10003;');
            } else {
                $badge
                    .removeClass('border-emerald-600 bg-emerald-600 text-white')
                    .addClass(
                        isActive
                            ? 'border-emerald-600 bg-emerald-600 text-white'
                            : 'border-slate-300 bg-white text-slate-500'
                    )
                    .text(index + 1);
            }

            $item.find('p.text-sm').toggleClass('text-slate-900', isActive || isComplete).toggleClass('text-slate-700', !isActive && !isComplete);
        });

        $('#job-wizard-back').toggleClass('hidden', step === 'customer');
        $('#job-wizard-next').toggleClass('hidden', step === 'mower');
        $('#job-form-submit').toggleClass('hidden', step !== 'mower');

        if (step === 'customer') {
            $('#job-wizard-next').text(NEXT_LABELS.customer);
        } else if (step === 'site') {
            $('#job-wizard-next').text(NEXT_LABELS.site);
        }
    }

    function switchJobTab(step, options) {
        options = options || {};
        if (STEPS.indexOf(step) === -1) {
            return;
        }

        const targetIndex = STEPS.indexOf(step);
        if (!options.force && targetIndex > maxReachedIndex) {
            return;
        }

        $('#job-tab-customer, #job-tab-site, #job-tab-mower').addClass('hidden');
        $('#job-tab-' + step).removeClass('hidden');

        $('.job-wizard-step').removeClass('is-active');
        $('.job-wizard-step[data-step="' + step + '"]').addClass('is-active');

        updateStepperUi(step);

        if (step === 'mower') {
            refreshWorkloads();
        }

        $('html, body').animate({ scrollTop: $('#job-wizard').offset().top - 24 }, 200);
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

    function renderWorkloads(workloads) {
        const $container = $('#job-mower-workloads');
        if (!$container.length) {
            return;
        }
        if (!workloads || !workloads.length) {
            $container.html('<p class="text-sm text-slate-500">No active mowers found.</p>');
            return;
        }
        const html = workloads
            .map(function (row) {
                return (
                    '<div class="rounded-lg border border-slate-200 px-3 py-2 text-sm" data-mower-id="' +
                    row.id +
                    '"><div class="flex items-center justify-between gap-2"><span class="font-medium text-slate-800">' +
                    row.name +
                    '</span><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">' +
                    row.efficiency +
                    '</span></div><p class="mt-1 text-xs text-slate-500">' +
                    row.assigned_minutes +
                    ' min • ' +
                    row.job_count +
                    ' jobs • load ' +
                    row.adjusted_load +
                    '</p></div>'
                );
            })
            .join('');
        $container.html(html);
    }

    function refreshWorkloads() {
        if (!window.jobFormRoutes?.workloads) {
            return;
        }
        const date = $('#job-scheduled-date').val() || '';
        $.get(window.jobFormRoutes.workloads, { scheduled_date: date }, function (res) {
            renderWorkloads(res.workloads || []);
        });
    }

    function suggestMower() {
        if (!window.jobFormRoutes?.suggestions) {
            return;
        }
        const date = $('#job-scheduled-date').val() || '';
        const duration = $('#job-estimated-duration').val() || 60;
        $.get(
            window.jobFormRoutes.suggestions,
            {
                scheduled_date: date,
                estimated_duration_minutes: duration,
            },
            function (res) {
                if (res.workloads) {
                    renderWorkloads(res.workloads);
                }
                const suggestion = res.suggestion;
                if (!suggestion) {
                    $('#job-suggestion-text').text('No mower suggestion available.');
                    return;
                }
                $('#job-suggestion-text').text(suggestion.mower_name + ': ' + suggestion.reason);
                $('#job-done-by').val(suggestion.mower_id);
                $('#job-employee-ids').val([String(suggestion.mower_id)]);
            }
        );
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
        $('#job-payment-reason-wrap').toggleClass('hidden', $('#job-payment-status').val() !== 'Pending');
    }

    window.jobWizardGoToFirstInvalidStep = function () {
        for (let i = 0; i < STEPS.length; i++) {
            const $panel = $('#job-tab-' + STEPS[i]);
            if (!validateStepPanel($panel)) {
                maxReachedIndex = Math.max(maxReachedIndex, i);
                switchJobTab(STEPS[i], { force: true });
                showStepValidationAlert();
                return;
            }
        }
    };

    window.validateAllJobSteps = validateAllJobSteps;

    function initWizard() {
        if (!getWizard().length) {
            return;
        }

        const isEdit = $('#job-form').find('input[name="_method"][value="PATCH"]').length > 0;
        maxReachedIndex = isEdit ? STEPS.length - 1 : 0;
        switchJobTab('customer', { force: true });

        $('#job-wizard-next').on('click', function () {
            if (!validateCurrentStep()) {
                return;
            }
            const index = currentStepIndex();
            maxReachedIndex = Math.max(maxReachedIndex, index + 1);
            switchJobTab(STEPS[index + 1], { force: true });
        });

        $('#job-wizard-back').on('click', function () {
            const index = currentStepIndex();
            if (index > 0) {
                switchJobTab(STEPS[index - 1], { force: true });
            }
        });

        $(document).on('click', '.job-wizard-step', function () {
            const $step = $(this);
            const index = parseInt($step.data('step-index'), 10) - 1;
            const step = $step.data('step');
            if (index > maxReachedIndex) {
                return;
            }
            if (index > currentStepIndex() && !validateCurrentStep()) {
                return;
            }
            switchJobTab(step, { force: true });
        });
    }

    $('#job-client-id').on('change', function () {
        fillFromClient($(this).find('option:selected'));
        refreshWorkloads();
    });

    $('#job-scheduled-date').on('change', refreshWorkloads);
    $('#job-refresh-workloads').on('click', refreshWorkloads);
    $('#job-suggest-mower').on('click', suggestMower);

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
        initWizard();

        const $client = $('#job-client-id option:selected');
        if ($client.val()) {
            fillFromClient($client);
        }
        syncJobEquipmentMarkerColor();
    });
})(window.jQuery);
