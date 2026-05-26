@props([
    'images' => [],
    'galleryId' => 'job-gallery',
    'kind' => 'before',
    'readonly' => false,
])

<div id="{{ $galleryId }}" class="mt-3 grid grid-cols-3 gap-2" data-kind="{{ $kind }}">
    @foreach ($images as $image)
        <div class="group relative aspect-square overflow-hidden rounded-lg bg-slate-100" data-image-id="{{ $image['id'] }}">
            <a href="{{ $image['url'] }}" target="_blank" rel="noopener" class="block h-full w-full">
                <img
                    src="{{ $image['thumb_url'] }}"
                    alt="{{ ucfirst($kind) }} photo"
                    loading="lazy"
                    decoding="async"
                    class="h-full w-full object-cover"
                >
            </a>
            @unless ($readonly)
                <button
                    type="button"
                    class="mower-delete-image absolute right-1 top-1 rounded-full bg-red-600/90 px-2 py-0.5 text-[10px] font-semibold text-white"
                    data-image-id="{{ $image['id'] }}"
                    data-kind="{{ $kind }}"
                    aria-label="Delete photo"
                >
                    Delete
                </button>
            @endunless
        </div>
    @endforeach
</div>
