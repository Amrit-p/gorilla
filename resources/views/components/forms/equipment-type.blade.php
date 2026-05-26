@props([
    'name' => 'equipment_type_id',
    'id' => null,
    'label' => 'Equipment type',
    'equipmentTypes' => null,
    'selected' => null,
    'hint' => null,
])

@php
    $fieldId = $id ?? 'equipment-type-'.md5($name);
    $options = $equipmentTypes ?? \App\Support\EquipmentTypes::selectOptions();
    $fieldKey = str_replace('[]', '', $name);
    $selectedValue = old($fieldKey, $selected);
@endphp

<div {{ $attributes->merge(['class' => '']) }}>
    <label for="{{ $fieldId }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>
    <select
        name="{{ $name }}"
        id="{{ $fieldId }}"
        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
    >
        <option value="">Select equipment</option>
        @foreach ($options as $equipmentType)
            <option
                value="{{ $equipmentType['id'] }}"
                data-color="{{ $equipmentType['color_code'] ?? '#64748b' }}"
                @selected((string) $selectedValue === (string) $equipmentType['id'])
            >
                {{ $equipmentType['name'] }}
            </option>
        @endforeach
    </select>
    @if ($hint)
        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif
    @if (! empty($options))
        <div class="mt-2 flex flex-wrap gap-2" aria-hidden="true">
            @foreach ($options as $equipmentType)
                <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs text-slate-600">
                    <span
                        class="inline-block h-2.5 w-2.5 rounded-full"
                        style="background-color: {{ $equipmentType['color_code'] ?? '#64748b' }}"
                    ></span>
                    {{ $equipmentType['name'] }}
                </span>
            @endforeach
        </div>
    @endif
</div>
