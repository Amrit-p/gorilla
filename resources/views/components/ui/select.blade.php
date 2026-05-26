@props([
    'label' => '',
    'name',
    'options' => [],
    'value' => '',
    'placeholder' => 'Select option',
])

<div>
    @if ($label !== '')
        <label for="{{ $attributes->get('id', $name) }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif
    <select
        id="{{ $attributes->get('id', $name) }}"
        name="{{ $name }}"
        {{ $attributes->merge(['class' => 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none']) }}
    >
        @if ($placeholder !== '')
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            @php
                $val = is_int($optionValue) ? $optionLabel : $optionValue;
                $text = is_int($optionValue) ? $optionLabel : $optionLabel;
            @endphp
            <option value="{{ $val }}" @selected((string) old($name, $value) === (string) $val)>{{ $text }}</option>
        @endforeach
    </select>
</div>
