@props([
    'type' => 'button',
    'variant' => 'primary',
])

@php
    $classes = $variant === 'secondary'
        ? 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-100'
        : 'bg-slate-900 text-white hover:bg-slate-700';
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => "rounded-md px-4 py-2 text-sm font-medium transition {$classes}"]) }}
>
    {{ $slot }}
</button>
