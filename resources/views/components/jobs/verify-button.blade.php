@props(['job'])

@can('verify-jobs')
    <button
        type="button"
        {{ $attributes->merge(['class' => 'verify-job']) }}
        data-id="{{ $job->id }}"
        data-verified="{{ $job->isVerified() ? '1' : '0' }}"
    >{{ $slot->isEmpty() ? ($job->isVerified() ? 'Unverify' : 'Verify') : $slot }}</button>
@endcan
