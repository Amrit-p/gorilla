@props([
    'label',
    'value',
    'subtitle' => null,
    'accent' => 'emerald',
])

@php
    $accentClasses = match ($accent) {
        'sky' => 'bg-sky-600',
        'amber' => 'bg-amber-600',
        'teal' => 'bg-teal-600',
        'violet' => 'bg-violet-600',
        'slate' => 'bg-slate-600',
        default => 'bg-emerald-600',
    };
@endphp

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm']) }}>
    <div class="{{ $accentClasses }} px-3 py-2 text-xs font-semibold text-white">{{ $label }}</div>
    <div class="space-y-1 p-3">
        <p class="text-2xl font-bold text-slate-900">{{ $value }}</p>
        @if ($subtitle)
            <p class="text-xs text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>
</div>
