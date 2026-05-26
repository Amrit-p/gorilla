@props([
    'status' => 'Active',
])

@php
    $type = $status === 'Active' ? 'success' : 'danger';
@endphp

<x-ui.badge :type="$type">{{ $status }}</x-ui.badge>
