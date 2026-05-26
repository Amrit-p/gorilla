@props(['status'])

@php
    $type = \App\Enums\JobWorkflowStatus::badgeTypeFor($status);
@endphp

<x-ui.badge :type="$type">{{ $status }}</x-ui.badge>
