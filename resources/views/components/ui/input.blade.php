@props([
    'label' => '',
    'name',
    'type' => 'text',
    'value' => '',
])

<div>
    {{-- Shared label/input block keeps forms consistent. --}}
    <label for="{{ $attributes->get('id', $name) }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>
    <input
        id="{{ $attributes->get('id', $name) }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        {{ $attributes->merge(['class' => 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none']) }}
    >
</div>
