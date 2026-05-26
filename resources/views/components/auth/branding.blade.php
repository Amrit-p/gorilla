<div {{ $attributes->merge(['class' => 'mb-4 flex items-center gap-3']) }}>
    <x-ui.site-logo size="md" />
    <div>
        <p class="text-base font-bold text-slate-900">{{ $websiteBranding['site_name'] }}</p>
        <p class="text-xs text-slate-500">{{ $websiteBranding['site_tagline'] }}</p>
    </div>
</div>
