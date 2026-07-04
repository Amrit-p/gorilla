@props(['job'])

@can('assign-jobs')
    <button
        type="button"
        {{ $attributes->merge(['class' => 'assign-job']) }}
        data-id="{{ $job->id }}"
        data-client-id="{{ $job->client_id ?? '' }}"
        data-done-by="{{ $job->done_by_user_id ?? '' }}"
        data-employee-ids="{{ json_encode($job->assignedEmployees->pluck('id')) }}"
    >{{ $slot->isEmpty() ? 'Assign' : $slot }}</button>
@endcan
