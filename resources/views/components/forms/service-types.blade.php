@props([
    'name' => 'service_types[]',
    'label' => 'Service types',
    'selected' => [],
    'required' => true,
    'serviceTypes' => null,
])

@php
    $fieldKey = str_replace('[]', '', $name);
    $options = $serviceTypes ?? \App\Support\ServiceTypes::all();
    $selectedValues = old($fieldKey, $selected);
    if (! is_array($selectedValues)) {
        $selectedValues = $selectedValues ? [(string) $selectedValues] : [];
    }
    $groupSelector = 'input[name="'.$name.'"]';
@endphp

<div
    @if ($required) data-validate-group="{{ $groupSelector }}" data-validate-required="1" @endif
    {{ $attributes->merge(['class' => 'sm:col-span-2']) }}
>
    <label class="mb-2 block text-sm font-medium text-slate-700">{{ $label }}</label>
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-2">
        @foreach ($options as $serviceType)
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input
                    type="checkbox"
                    name="{{ $name }}"
                    value="{{ $serviceType }}"
                    @checked(in_array($serviceType, $selectedValues, true))
                    class="rounded border-slate-300"
                >
                {{ $serviceType }}
            </label>
        @endforeach
    </div>
</div>
