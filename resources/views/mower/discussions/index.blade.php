<x-layouts.mower :title="'My Discussions'" :showBack="true" :backUrl="route('mower.index')">

    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-base font-semibold text-slate-800">Discussions</h1>
            @if ($discussions->isEmpty())
                <p class="text-xs text-slate-500">No discussions yet</p>
            @else
                <p class="text-xs text-slate-500">{{ $discussions->count() }} {{ Str::plural('discussion', $discussions->count()) }}</p>
            @endif
        </div>
    </div>

    @if ($discussions->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white py-16 text-center">
            <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50">
                <svg class="h-7 w-7 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-700">No discussions yet</p>
            <p class="mt-1 text-xs text-slate-400">Your discussions will appear here</p>
        </div>
    @else
        <div class="space-y-5">
            @foreach ($grouped as $dateString => $items)
                @php
                    $date = \Carbon\Carbon::parse($dateString);
                    if ($date->isToday()) {
                        $label = 'Today';
                    } elseif ($date->isYesterday()) {
                        $label = 'Yesterday';
                    } else {
                        $label = $date->format('d F, Y');
                    }
                @endphp

                <div>
                    <div class="mb-2 flex items-center gap-2">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</span>
                        <div class="h-px flex-1 bg-slate-200"></div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        @foreach ($items as $discussion)
                            @php
                                $categoryLabel = match($discussion->category) {
                                    'worker'     => 'Worker',
                                    'budget'     => 'Budget',
                                    'expansion'  => 'Expansion',
                                    'crm_update' => 'CRM Update',
                                    default      => 'General',
                                };
                                $categoryColour = match($discussion->category) {
                                    'budget'     => 'bg-yellow-100 text-yellow-700',
                                    'worker'     => 'bg-blue-100 text-blue-700',
                                    'expansion'  => 'bg-purple-100 text-purple-700',
                                    'crm_update' => 'bg-emerald-100 text-emerald-700',
                                    default      => 'bg-slate-100 text-slate-600',
                                };
                                $isLast = $loop->last;
                            @endphp

                            <a href="{{ route('mower.discussions.show', $discussion) }}"
                               class="flex items-start gap-3 px-4 py-3.5 transition-colors active:bg-slate-50 {{ $isLast ? '' : 'border-b border-slate-100' }}">

                                <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-50">
                                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z" />
                                    </svg>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ $discussion->title }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $categoryColour }}">
                                            {{ $categoryLabel }}
                                        </span>
                                        <span class="text-[11px] text-slate-400">
                                            {{ $discussion->sections->count() }} {{ Str::plural('section', $discussion->sections->count()) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-1 shrink-0 text-slate-300">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</x-layouts.mower>
