@props([
    'showColorCode' => false,
    'record' => null,
])

<x-ui.input label="Name" name="name" :value="$record?->name" />
@if ($showColorCode)
    <div>
        <x-ui.input label="Color code" name="color_code" :value="$record?->color_code ?? '#64748b'" placeholder="#ef4444" />
        <p class="mt-1 text-xs text-slate-500">Hex color used on maps and equipment labels.</p>
    </div>
@endif
<x-ui.input label="Sort order" name="sort_order" type="number" min="0" :value="$record?->sort_order ?? 0" />
<x-ui.select
    label="Status"
    name="is_active"
    :options="['1' => 'Active', '0' => 'Inactive']"
    :value="$record ? ($record->is_active ? '1' : '0') : '1'"
/>
