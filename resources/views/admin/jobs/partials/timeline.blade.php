<div class="rounded-2xl border border-slate-200 bg-white p-5">
    <h3 class="text-sm font-semibold text-slate-900">Activity timeline</h3>
    <ol class="mt-4 space-y-4 border-l border-slate-200 pl-4">
        @forelse ($timeline as $entry)
            <li class="relative">
                <span class="absolute -left-[21px] top-1.5 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-4 ring-white"></span>
                <p class="text-sm font-medium text-slate-800">{{ $entry->description ?: $entry->action }}</p>
                <p class="text-xs text-slate-500">
                    {{ $entry->user?->name ?: 'System' }}
                    • {{ $entry->created_at?->diffForHumans() }}
                </p>
            </li>
        @empty
            <li class="text-sm text-slate-500">No timeline activity yet.</li>
        @endforelse
    </ol>
</div>
