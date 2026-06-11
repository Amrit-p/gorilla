@props([
    'startDate'   => null,
    'endDate'     => null,
    'minDate'     => null,
    'maxDate'     => null,
    'name'        => 'date_range',
    'placeholder' => 'Select date range',
    'showRanges'  => false,
    'onChange'    => null,
    'onClear'     => null,
    'onApply'     => null,
])

@php
    $startName = $name . '[start]';
    $endName   = $name . '[end]';
@endphp

@once
    @push('scripts')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
        <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

        <style>
            /* ── Container ──────────────────────────────────────────────── */
            .daterangepicker {
                font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
                font-size: 0.75rem;        /* text-xs */
                color: var(--drp-font-color, #334155);
                background: var(--drp-bg, #ffffff);
                border: 1px solid var(--drp-border, #e2e8f0);
                border-radius: 0.75rem;    /* rounded-xl */
                box-shadow: 0 4px 24px 0 rgba(15,23,42,.10), 0 1px 4px 0 rgba(15,23,42,.06);
                padding: 0;
            }
            .daterangepicker::before,
            .daterangepicker::after { border-bottom-color: var(--drp-border, #e2e8f0); }

            /* ── Calendar header (month/year selects + nav arrows) ──────── */
            .daterangepicker .drp-calendar { padding: 12px 14px; }
            .daterangepicker .calendar-table { border: none; }
            .daterangepicker .calendar-table table { border-spacing: 1px; }

            .daterangepicker th.month {
                font-size: 0.75rem;
                font-weight: 600;
                color: var(--drp-heading, #1e293b);
            }
            .daterangepicker select.monthselect,
            .daterangepicker select.yearselect {
                font-size: 0.7rem;
                border: 1px solid var(--drp-border, #e2e8f0);
                border-radius: 0.4rem;
                background: var(--drp-footer-bg, #f8fafc);
                color: var(--drp-font-color, #334155);
                padding: 2px 4px;
            }
            .daterangepicker select.monthselect:focus,
            .daterangepicker select.yearselect:focus {
                outline: none;
                border-color: var(--drp-focus, #818cf8);
                box-shadow: 0 0 0 2px var(--drp-focus-100, #e0e7ff);
            }

            /* nav arrows */
            .daterangepicker .prev span,
            .daterangepicker .next span {
                border-color: var(--drp-muted-2, #94a3b8);
            }
            .daterangepicker th.prev:hover,
            .daterangepicker th.next:hover {
                background: var(--drp-hover-bg, #f1f5f9);
                border-radius: 0.375rem;
            }

            /* ── Day cells ──────────────────────────────────────────────── */
            .daterangepicker td.available { border-radius: 0.375rem; }
            .daterangepicker td.available:hover {
                background: var(--drp-primary-50, #eef2ff);
                color: var(--drp-primary, #4f46e5);
            }
            .daterangepicker td.off,
            .daterangepicker td.off.in-range,
            .daterangepicker td.off.start-date,
            .daterangepicker td.off.end-date { color: var(--drp-off, #cbd5e1); }

            .daterangepicker td.in-range {
                background: var(--drp-primary-50, #eef2ff);
                color: var(--drp-primary-700, #4338ca);
                border-radius: 0;
            }
            .daterangepicker td.start-date,
            .daterangepicker td.end-date,
            .daterangepicker td.active,
            .daterangepicker td.active:hover {
                background: var(--drp-primary, #4f46e5);
                color: var(--drp-foreground, #ffffff);
                border-radius: 0.375rem;
            }
            .daterangepicker td.start-date.end-date { border-radius: 0.375rem; }
            .daterangepicker td.start-date { border-radius: 0.375rem 0 0 0.375rem; }
            .daterangepicker td.end-date   { border-radius: 0 0.375rem 0.375rem 0; }

            .daterangepicker td.today::after {
                border-bottom-color: var(--drp-primary, #4f46e5);
            }

            /* day-of-week header */
            .daterangepicker th.week,
            .daterangepicker td.week { color: var(--drp-muted-2, #94a3b8); font-size: 0.65rem; }
            .daterangepicker .calendar-table th {
                color: #94a3b8; /* slate-400 */
                font-size: 0.65rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            /* ── Footer (selected display + buttons) ────────────────────── */
            .daterangepicker .drp-buttons {
                border-top: 1px solid var(--drp-hover-bg, #f1f5f9);
                padding: 8px 14px;
                background: var(--drp-footer-bg, #f8fafc);
                border-radius: 0 0 0.75rem 0.75rem;
            }
            .daterangepicker .drp-selected {
                font-size: 0.7rem;
                color: var(--drp-muted, #64748b);
            }
            .daterangepicker .cancelBtn {
                font-size: 0.7rem;
                font-weight: 500;
                background: transparent;
                border: 1px solid var(--drp-border, #e2e8f0);
                color: var(--drp-cancel-color, #475569);
                border-radius: 0.5rem;
                padding: 4px 12px;
                transition: background 0.15s;
            }
            .daterangepicker .cancelBtn:hover {
                background: var(--drp-hover-bg, #f1f5f9);
                border-color: var(--drp-border-hover, #cbd5e1);
            }
            .daterangepicker .applyBtn {
                font-size: 0.7rem;
                font-weight: 500;
                background: var(--drp-primary, #4f46e5);
                border: none;
                color: var(--drp-foreground, #ffffff);
                border-radius: 0.5rem;
                padding: 4px 14px;
                transition: background 0.15s;
            }
            .daterangepicker .applyBtn:hover,
            .daterangepicker .applyBtn:focus { background: var(--drp-primary-700, #4338ca); }

            /* ── Preset ranges panel ────────────────────────────────────── */
            .daterangepicker .ranges ul { padding: 6px; margin: 0; }
            .daterangepicker .ranges li {
                font-size: 0.7rem;
                color: #475569;        /* slate-600 */
                border-radius: 0.375rem;
                padding: 5px 12px;
                transition: background 0.12s;
            }
            .daterangepicker .ranges li:hover { background: var(--drp-primary-50, #eef2ff); color: var(--drp-primary, #4f46e5); }
            .daterangepicker .ranges li.active {
                background: var(--drp-primary, #4f46e5);
                color: var(--drp-foreground, #ffffff);
            }

            /* separator between range panel and calendars */
            .daterangepicker.show-ranges .drp-calendar.left { border-left: 1px solid var(--drp-hover-bg, #f1f5f9); }
        </style>
    @endpush
@endonce

@php
    $pickerId = $attributes->get('id') ?: 'drp-' . uniqid();
@endphp

<div {{ $attributes->only('style') }} class="relative">
    <div class="flex items-center rounded-lg border border-slate-200 bg-slate-50 focus-within:border-indigo-400 focus-within:bg-white focus-within:ring-2 focus-within:ring-indigo-100">
        <span class="pointer-events-none flex items-center pl-2.5">
            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </span>

        <input
            id="{{ $pickerId }}"
            type="text"
            readonly
            placeholder="{{ $placeholder }}"
            {{ $attributes->except(['style', 'id'])->merge(['class' => 'w-full cursor-pointer bg-transparent px-2.5 py-2 text-xs text-slate-700 placeholder-slate-400 outline-none']) }}
        >

        <button
            type="button"
            id="{{ $pickerId }}-clear"
            class="hidden pr-2.5 text-slate-400 hover:text-slate-600"
            title="Clear"
        >
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <input type="hidden" name="{{ $startName }}" id="{{ $pickerId }}-start" value="{{ $startDate }}">
    <input type="hidden" name="{{ $endName }}"   id="{{ $pickerId }}-end"   value="{{ $endDate }}">
</div>

@push('scripts')
<script>
(function () {
    var pickerId     = '{{ $pickerId }}';
    var $input       = $('#' + pickerId);
    var $clearBtn    = $('#' + pickerId + '-clear');
    var $startHidden = $('#' + pickerId + '-start');
    var $endHidden   = $('#' + pickerId + '-end');

    var options = {
        autoUpdateInput: false,
        autoApply: true,
        linkedCalendars: false,
        showDropdowns: true,
        opens: 'left',
        locale: {
            cancelLabel: 'Clear',
            format: 'YYYY-MM-DD',
        },
    };

    @if ($showRanges)
    options.ranges = {
        'Today':      [moment(), moment()],
        'Yesterday':  [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'This Week':  [moment().startOf('isoWeek'), moment().endOf('isoWeek')],
        'Last Week':  [moment().subtract(1, 'week').startOf('isoWeek'), moment().subtract(1, 'week').endOf('isoWeek')],
        'Next Week':  [moment().add(1, 'week').startOf('isoWeek'), moment().add(1, 'week').endOf('isoWeek')],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        'Next Month': [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')],
    };
    options.alwaysShowCalendars = true;
    @endif

    @if ($minDate)  options.minDate = '{{ $minDate }}'; @endif
    @if ($maxDate)  options.maxDate = '{{ $maxDate }}'; @endif
    @if ($startDate && $endDate)
        options.startDate = '{{ $startDate }}';
        options.endDate   = '{{ $endDate }}';
    @endif

    $input.daterangepicker(options);
    var picker = $input.data('daterangepicker');

    @if ($startDate && $endDate)
        $input.val('{{ $startDate }}' + ' – ' + '{{ $endDate }}');
        $clearBtn.removeClass('hidden');
    @endif

    $input.on('apply.daterangepicker', function (e, instance) {
        var start = instance.startDate.format('YYYY-MM-DD');
        var end   = instance.endDate.format('YYYY-MM-DD');
        $input.val(start + ' – ' + end);
        $startHidden.val(start);
        $endHidden.val(end);
        $clearBtn.removeClass('hidden');
        $input.trigger('change');
        @if ($onApply)  ({!! $onApply !!})(instance, $input); @endif
        @if ($onChange) ({!! $onChange !!})(instance, $input); @endif
    });

    $input.on('cancel.daterangepicker', function (e, instance) {
        $input.val('');
        $startHidden.val('');
        $endHidden.val('');
        $clearBtn.addClass('hidden');
        $input.trigger('change');
        @if ($onClear)  ({!! $onClear !!})(instance, $input); @endif
        @if ($onChange) ({!! $onChange !!})(instance, $input); @endif
    });

    $clearBtn.on('click', function () {
        $input.val('');
        $startHidden.val('');
        $endHidden.val('');
        $clearBtn.addClass('hidden');
        picker.setStartDate(moment());
        picker.setEndDate(moment());
        $input.trigger('change');
        @if ($onClear)  ({!! $onClear !!})(picker, $input); @endif
        @if ($onChange) ({!! $onChange !!})(picker, $input); @endif
    });
})();
</script>
@endpush
