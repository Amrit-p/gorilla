@props([
    'items' => [],
])

<nav aria-label="Breadcrumb" class="mb-4 text-sm text-slate-600">
    <ol class="flex flex-wrap items-center gap-1">
        @foreach ($items as $index => $item)
            <li class="flex items-center gap-1">
                @if ($index > 0)
                    <span class="text-slate-400">/</span>
                @endif
                @if (! empty($item['url']) && $index < count($items) - 1)
                    <a href="{{ $item['url'] }}" class="font-medium text-slate-700 hover:text-slate-900">{{ $item['label'] }}</a>
                @else
                    <span class="font-medium text-slate-900">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
