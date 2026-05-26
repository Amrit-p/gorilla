@props([
    'prefix' => 'lead',
    'addressName' => 'address',
    'addressLabel' => 'Address',
    'addressInputId' => null,
    'latitudeInputId' => null,
    'longitudeInputId' => null,
    'mapPreviewId' => null,
    'addressValue' => '',
    'latitudeValue' => null,
    'longitudeValue' => null,
    'mapHeight' => '220px',
    'showCoordinates' => false,
    'placeholder' => 'Start typing an address...',
    /** default | sidebar — sidebar omits full-width grid span for two-column forms */
    'variant' => 'default',
    'showCurrentLocation' => false,
    'markerColor' => '#64748b',
])

@php
    use App\Support\GoogleMapsSettings;

    $addressInputId = $addressInputId ?: $prefix.'-address-input';
    $latitudeInputId = $latitudeInputId ?: $prefix.'-latitude';
    $longitudeInputId = $longitudeInputId ?: $prefix.'-longitude';
    $mapPreviewId = $mapPreviewId ?: $prefix.'-map-preview';
    $hintId = $prefix.'-map-hint';

    $mapsEnabled = GoogleMapsSettings::isPlacesEnabled();
    $center = GoogleMapsSettings::defaultCenter();
    $defaultLat = $latitudeValue ?: (string) $center['lat'];
    $defaultLng = $longitudeValue ?: (string) $center['lng'];
@endphp

@php
    $wrapperClass = match ($variant) {
        'sidebar' => 'flex h-full flex-col space-y-3',
        default => 'space-y-3 sm:col-span-2',
    };
@endphp

<div class="{{ $wrapperClass }}" data-crm-address-picker="{{ $prefix }}" @if ($variant === 'sidebar') data-layout="sidebar" @endif>
    <x-ui.input
        :label="$addressLabel"
        :name="$addressName"
        :id="$addressInputId"
        :value="$addressValue"
        :placeholder="$placeholder"
        autocomplete="off"
    />

    @if ($showCoordinates)
        <div class="grid grid-cols-2 gap-3">
            <x-ui.input
                label="Latitude"
                name="latitude"
                :id="$latitudeInputId"
                :value="$latitudeValue"
                :readonly="$mapsEnabled"
            />
            <x-ui.input
                label="Longitude"
                name="longitude"
                :id="$longitudeInputId"
                :value="$longitudeValue"
                :readonly="$mapsEnabled"
            />
        </div>
    @else
        <input type="hidden" name="latitude" id="{{ $latitudeInputId }}" value="{{ $latitudeValue }}">
        <input type="hidden" name="longitude" id="{{ $longitudeInputId }}" value="{{ $longitudeValue }}">
    @endif

    <div>
        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
            <p class="text-sm font-medium text-slate-700">Map preview</p>
            @if ($showCurrentLocation && $mapsEnabled)
                <button
                    type="button"
                    class="crm-use-current-location rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50"
                    data-picker-prefix="{{ $prefix }}"
                >
                    Use my current location
                </button>
            @endif
        </div>
        <div
            id="{{ $mapPreviewId }}"
            class="w-full flex-1 overflow-hidden rounded-lg border border-slate-200 bg-slate-100 min-h-[280px]"
            style="height: {{ $mapHeight }}"
            data-default-lat="{{ $defaultLat }}"
            data-default-lng="{{ $defaultLng }}"
        ></div>
        <p id="{{ $hintId }}" class="mt-1 text-xs text-slate-500">
            @if ($mapsEnabled)
                Search for an address, or drag the marker / click the map — the address and coordinates update automatically.
            @else
                Add a Google Maps API key in Website Settings (or GOOGLE_MAPS_API_KEY in .env) to enable autocomplete and map preview. Enter latitude and longitude manually below.
            @endif
        </p>
    </div>
</div>

@once
    @push('scripts')
        @include('components.scripts.google-maps')
    @endpush
@endonce

@push('scripts')
    <script>
        window.crmAddressPickerQueue = window.crmAddressPickerQueue || [];
        window.crmAddressPickerQueue.push({
            prefix: @json($prefix),
            addressInputId: @json($addressInputId),
            latitudeInputId: @json($latitudeInputId),
            longitudeInputId: @json($longitudeInputId),
            mapPreviewId: @json($mapPreviewId),
            hintId: @json($hintId),
            mapsEnabled: @json($mapsEnabled),
            showCurrentLocation: @json($showCurrentLocation),
            markerColor: @json($markerColor),
            loader: @json(GoogleMapsSettings::loaderConfig()),
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof window.crmFlushAddressPickerQueue === 'function') {
                    window.crmFlushAddressPickerQueue();
                }
            });
        } else if (typeof window.crmFlushAddressPickerQueue === 'function') {
            window.crmFlushAddressPickerQueue();
        }
    </script>
@endpush
