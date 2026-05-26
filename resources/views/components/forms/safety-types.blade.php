@props([
    'name' => 'safety_concerns[]',
    'label' => 'Safety',
    'selected' => [],
    'required' => true,
    'safetyOptions' => null,
])

@php
    $fieldKey = str_replace('[]', '', $name);
    $options = $safetyOptions ?? \App\Support\SafetyTypes::all();
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
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
        @foreach ($options as $safetyOption)
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input
                    type="checkbox"
                    name="{{ $name }}"
                    value="{{ $safetyOption }}"
                    @checked(in_array($safetyOption, $selectedValues, true))
                    class="safety-option-checkbox rounded border-slate-300"
                    data-value="{{ $safetyOption }}"
                >
                {{ $safetyOption }}
            </label>
        @endforeach
    </div>
</div>
