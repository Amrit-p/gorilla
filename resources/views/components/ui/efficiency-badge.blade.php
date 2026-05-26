@props([
    'efficiency' => 'Average',
])

@php
    $type = match ($efficiency) {
        'Good' => 'success',
        'Beginner' => 'warning',
        default => 'default',
    };
@endphp

<x-ui.badge :type="$type">{{ $efficiency }}</x-ui.badge>
