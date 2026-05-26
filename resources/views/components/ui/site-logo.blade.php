@props([
    'size' => 'md',
])

@php
    use App\Support\WebsiteSettings;

    $sizes = [
        'sm' => 'h-8 w-8 rounded-lg text-xs',
        'md' => 'h-10 w-10 rounded-lg text-sm',
        'lg' => 'h-12 w-12 rounded-xl text-base',
    ];
    $boxClass = $sizes[$size] ?? $sizes['md'];
    $branding = $websiteBranding ?? WebsiteSettings::branding();
    $logoUrl = $branding['site_logo_url'] ?? null;
    $initial = $branding['site_logo_initial'] ?? 'M';
@endphp

@if ($logoUrl)
    <img src="{{ $logoUrl }}" alt="{{ $branding['site_name'] ?? 'Logo' }}" class="{{ $boxClass }} object-contain bg-white/10 p-1" {{ $attributes }}>
@else
    <span {{ $attributes->merge(['class' => "flex shrink-0 items-center justify-center bg-emerald-500/90 font-bold text-white {$boxClass}"]) }}>
        {{ $initial }}
    </span>
@endif
