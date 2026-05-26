@props([
    // Title shown at the top of the stat card.
    'title' => 'Card Title',
    // Main value for the stat card.
    'value' => '0',
])

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <p class="text-sm font-medium text-slate-500">{{ $title }}</p>
    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $value }}</p>
</div>
