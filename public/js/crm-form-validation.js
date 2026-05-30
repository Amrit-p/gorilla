/**
 * Client-side validation for CRM intake forms (jQuery Validate).
 */
(function ($) {
    'use strict';

    if (!$ || !$.validator) {
        return;
    }

    $.validator.addMethod(
        'minChecked',
        function (value, element, params) {
            const min = typeof params === 'number' ? params : params[1] || 1;
            const $group = $(element).closest('[data-validate-group]');
            if ($group.length) {
                return $group.find('input[type="checkbox"]:checked').length >= min;
            }
            const selector = params[0];
            if (typeof selector === 'string' && selector) {
                return $(selector).filter(':checked').length >= min;
            }
            return false;
        },
        'Please select at least one option.'
    );

    $.validator.addMethod(
        'dateISO',
        function (value, element) {
            return this.optional(element) || /^\d{4}-\d{2}-\d{2}$/.test(String(value).trim());
        },
        'Please enter a valid date.'
    );

    const fieldClass = 'border-red-500 focus:border-red-500';
    const normalClass = 'border-slate-300 focus:border-slate-500';

    const defaults = {
        errorElement: 'p',
        errorClass: 'mt-1 text-xs text-red-600',
        validClass: 'is-valid',
        ignore: [],
        errorPlacement: function (error, element) {
            const group = element.closest('[data-validate-group]');
            if (group.length || element.is('[data-validate-group-proxy]')) {
                error.appendTo(element.closest('[data-validate-group]'));
                return;
            }
            if (element.attr('type') === 'checkbox' || element.attr('type') === 'radio') {
                error.insertAfter(element.closest('label'));
                return;
            }
            error.insertAfter(element);
        },
        highlight: function (element) {
            const $el = $(element);
            if ($el.is('[data-validate-group-proxy]') || $el.attr('type') === 'checkbox') {
                $el.closest('[data-validate-group]').addClass('rounded-md ring-1 ring-red-400');
                return;
            }
            $el.addClass(fieldClass).removeClass(normalClass);
        },
        unhighlight: function (element) {
            const $el = $(element);
            if ($el.is('[data-validate-group-proxy]') || $el.attr('type') === 'checkbox') {
                const group = $el.closest('[data-validate-group]');
                if (!group.length) {
                    return;
                }
                const required = group.data('validate-required');
                const checked = group.find('input[type="checkbox"]:checked').length;
                if (!required || checked >= 1) {
                    group.removeClass('rounded-md ring-1 ring-red-400');
                }
                return;
            }
            $el.removeClass(fieldClass).addClass(normalClass);
        },
    };

    const messages = {
        required: 'This field is required.',
        email: 'Please enter a valid email address.',
        number: 'Please enter a valid number.',
        min: 'Value is too low.',
        max: 'Value is too high.',
        maxlength: 'Too many characters.',
        minlength: 'Too few characters.',
        equalTo: 'Fields do not match.',
    };

    const profiles = {
        lead: {
            rules: {
                client_name: { required: true, maxlength: 120 },
                address: { required: true, maxlength: 255 },
                weed_spray: { required: true },
                equipment_type_id: { required: true },
                re_completion_days: { required: true },
                job_type: { required: true },
                charges: { number: true, min: 0 },
                mobile_number: { maxlength: 30 },
                email: { email: true, maxlength: 255 },
                payment_mode: { required: true },
                payment_status: { required: true },
                remarks: {
                    required: function () {
                        return $('#lead-payment-status').val() === 'Pending';
                    },
                    maxlength: 5000,
                },
            },
            messages: {
                remarks: { required: 'Remarks are required when payment status is Pending.' },
            },
        },
        'lead-edit': {
            rules: {
                client_name: { required: true, maxlength: 120 },
                address: { required: true, maxlength: 255 },
                weed_spray: { required: true },
                equipment_type_id: { required: true },
                re_completion_days: { required: true },
                job_type: { required: true },
                charges: { number: true, min: 0 },
                mobile_number: { maxlength: 30 },
                email: { email: true, maxlength: 255 },
                payment_mode: { required: true },
                payment_status: { required: true },
                status: { required: true },
                remarks: {
                    required: function () {
                        return $('#lead-payment-status').val() === 'Pending';
                    },
                    maxlength: 5000,
                },
            },
            messages: {
                remarks: { required: 'Remarks are required when payment status is Pending.' },
            },
        },
        client: {
            rules: {
                address: { required: true, maxlength: 255 },
                equipment_type_id: { required: true },
                weed_spray: { required: true },
                re_completion_days: { required: true },
                job_type: { required: true },
                charges: { number: true, min: 0 },
                phone: { maxlength: 30 },
                email: { email: true, maxlength: 255 },
                payment_mode: { required: true },
                customer_type: { required: true },
                remarks_type: { maxlength: 120 },
                payment_status: { required: true },
                safety_other: {
                    maxlength: 120,
                    required: function () {
                        return (
                            $('.client-safety-checkbox:checked').filter(function () {
                                return $(this).data('safety-option') === 'Any Other';
                            }).length > 0
                        );
                    },
                },
                payment_status_reason: {
                    required: function () {
                        return $('#client-payment-status').val() === 'Pending';
                    },
                    maxlength: 255,
                },
            },
        },
        job: {
            rules: {
                client_id: { required: true },
                equipment_type_id: { required: true },
                client_address: { required: true, maxlength: 255 },
                scheduled_date: { required: true, dateISO: true },
                scheduled_time: { required: true },
                estimated_duration_minutes: { required: true, number: true, min: 15, max: 1440 },
                parking_status: { required: true },
                customer_type: { required: true },
                payment_mode: { required: true },
                payment_status: { required: true },
                pet_warning: { maxlength: 1000 },
                payment_pending_reason: {
                    required: function () {
                        return $('#job-payment-status').val() === 'Pending';
                    },
                    maxlength: 255,
                },
            },
        },
        'job-edit': {
            rules: {
                client_id: { required: true },
                equipment_type_id: { required: true },
                client_address: { required: true, maxlength: 255 },
                scheduled_date: { required: true, dateISO: true },
                scheduled_time: { required: true },
                estimated_duration_minutes: { required: true, number: true, min: 15, max: 1440 },
                parking_status: { required: true },
                customer_type: { required: true },
                payment_mode: { required: true },
                payment_status: { required: true },
                status: { required: true },
            },
        },
        user: {
            rules: {
                name: { required: true, maxlength: 120 },
                email: { required: true, email: true },
                phone: { maxlength: 30 },
                role: { required: true },
                efficiency: { required: true },
                status: { required: true },
                password: { required: true, minlength: 8 },
                password_confirmation: { required: true, equalTo: '[name="password"]' },
            },
        },
        'user-edit': {
            rules: {
                name: { required: true, maxlength: 120 },
                email: { required: true, email: true },
                phone: { maxlength: 30 },
                role: { required: true },
                efficiency: { required: true },
                status: { required: true },
                password: { minlength: 8 },
                password_confirmation: { equalTo: '[name="password"]' },
            },
        },
        profile: {
            rules: {
                name: { required: true, maxlength: 120 },
                email: { required: true, email: true },
                phone: { maxlength: 30 },
            },
        },
        password: {
            rules: {
                current_password: { required: true },
                password: { required: true, minlength: 8 },
                password_confirmation: { required: true, equalTo: '[name="password"]' },
            },
        },
        login: {
            rules: {
                email: { required: true, email: true },
                password: { required: true },
            },
        },
    };

    function attachCheckboxGroupRules($form) {
        $form.find('[data-validate-group]').each(function () {
            const $group = $(this);
            const $first = $group.find('input[type="checkbox"]').first();
            if (!$first.length || !$group.data('validate-required')) {
                return;
            }
            let $proxy = $group.find('input[data-validate-group-proxy]');
            if (!$proxy.length) {
                const fieldKey = ($first.attr('name') || 'group').replace(/\[\]$/, '');
                $proxy = $('<input>', {
                    type: 'hidden',
                    name: fieldKey + '_validate',
                    value: '1',
                    'data-validate-group-proxy': '1',
                });
                $group.append($proxy);
            }
            $proxy.rules('add', {
                minChecked: 1,
            });
        });
    }

    $(document).on('change', '[data-validate-group] input[type="checkbox"]', function () {
        const $proxy = $(this).closest('[data-validate-group]').find('input[data-validate-group-proxy]');
        const validator = $(this).closest('form').data('validator');
        if ($proxy.length && validator) {
            validator.element($proxy[0]);
        }
    });

    function mapServerErrors(errors) {
        const mapped = {};
        Object.keys(errors).forEach(function (key) {
            const message = errors[key][0];
            if (key.indexOf('service_types') === 0) {
                mapped['service_types[]'] = message;
            } else if (key.indexOf('required_services') === 0) {
                mapped['required_services[]'] = message;
            } else if (key === 'service_types' || key === 'required_services') {
                mapped[key + '[]'] = message;
            } else if (key.indexOf('safety_concerns') === 0) {
                mapped['safety_concerns[]'] = message;
            } else {
                mapped[key] = message;
            }
        });
        return mapped;
    }

    function applyServerErrors() {
        if (!window.__serverValidationErrors) {
            return;
        }
        const mapped = mapServerErrors(window.__serverValidationErrors);
        $('.js-validate-form').each(function () {
            const $form = $(this);
            if (!$form.data('validator')) {
                initForm($form);
            }
            $form.validate().showErrors(mapped);
        });
    }

    function initForm($form) {
        const profile = $form.data('validate');
        const config = profiles[profile];
        if (!config) {
            return;
        }
        $form.validate($.extend(true, {}, defaults, config, { messages: messages }));
        attachCheckboxGroupRules($form);
    }

    function initAll() {
        $('.js-validate-form').each(function () {
            initForm($(this));
        });
        applyServerErrors();
    }

    $(document).on('change', '#lead-payment-status', function () {
        const $remarks = $('#lead-remarks');
        const validator = $remarks.closest('form').data('validator');
        if ($remarks.length && validator) {
            validator.element($remarks[0]);
        }
    });

    $(document).ready(initAll);

    window.CrmFormValidation = {
        init: initAll,
        initForm: initForm,
    };
})(jQuery);
