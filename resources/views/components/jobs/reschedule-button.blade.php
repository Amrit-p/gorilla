@props(['job'])

@can('assign-jobs')
    <button
        type="button"
        {{ $attributes->merge(['class' => 'schedule-job']) }}
        data-id="{{ $job->id }}"
        data-scheduled-date="{{ $job->scheduled_date?->format('Y-m-d') ?? '' }}"
        data-scheduled-time="{{ $job->scheduled_time ?? '' }}"
    >{{ $slot->isEmpty() ? 'Reschedule' : $slot }}</button>
@endcan
