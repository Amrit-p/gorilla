@php
    $mapsLoaderVersion = @filemtime(public_path('js/google-maps-loader.js')) ?: 1;
    $addressPickerVersion = @filemtime(public_path('js/google-address-picker.js')) ?: 1;
@endphp
<script src="{{ asset('js/google-maps-loader.js') }}?v={{ $mapsLoaderVersion }}"></script>
<script src="{{ asset('js/google-address-picker.js') }}?v={{ $addressPickerVersion }}"></script>
