@include('components.masters.form-fields', ['showColorCode' => false])

<div>
    <x-ui.input label="Days between occurrences" name="interval_days" type="number" min="1" :value="$record?->interval_days" />
    <p class="mt-1 text-xs text-slate-500">Auto-filled from the name (e.g. "Weekly", "Bi-Weekly", "3 Weekly"). Edit it if that doesn't look right, or leave it blank for a one-time recurrence — this is what schedules the next job when a recurring job is completed.</p>
</div>

<script>
    (function () {
        function suggestIntervalDays(name) {
            var normalised = String(name || '').trim().toLowerCase();
            if (!normalised || normalised.indexOf('one-time') !== -1 || normalised.indexOf('one time') !== -1) {
                return null;
            }
            if (normalised === 'monthly' || normalised === 'quarterly') {
                return null;
            }

            var fixedDayKeywords = { daily: 1, weekly: 7, 'bi-weekly': 14, biweekly: 14, fortnightly: 14 };
            if (Object.prototype.hasOwnProperty.call(fixedDayKeywords, normalised)) {
                return fixedDayKeywords[normalised];
            }

            var match = normalised.match(/^(\d+)\s*[-_]?\s*(days?|weeks?|weekly)$/);
            if (match) {
                var count = Math.max(1, parseInt(match[1], 10));
                return match[2].indexOf('week') === 0 ? count * 7 : count;
            }

            return null;
        }

        var daysTouched = false;

        $(document).on('input', '#master-form [name="name"]', function () {
            if (daysTouched) return;
            var $days = $('#master-form [name="interval_days"]');
            if (!$days.length) return;
            var days = suggestIntervalDays($(this).val());
            $days.val(days !== null ? days : '');
        });

        $(document).on('input', '#master-form [name="interval_days"]', function () {
            daysTouched = true;
        });

        $(document).on('click', '#open-create-master', function () {
            daysTouched = false;
        });

        $(document).on('click', '.edit-master', function () {
            // Don't let the name field's autofill clobber a saved value while editing.
            daysTouched = true;
        });
    })();
</script>
