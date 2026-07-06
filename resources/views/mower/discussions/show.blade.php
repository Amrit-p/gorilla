<x-layouts.mower :title="$discussion->title" :showBack="true" :backUrl="route('mower.discussions.index')">

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
    @endphp

    <div class="space-y-4">

        {{-- Meta card --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="bg-gradient-to-br from-emerald-900 to-emerald-700 px-5 py-4">
                <span class="mb-2 inline-flex items-center rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-semibold text-white/90 ring-1 ring-white/20">
                    {{ $categoryLabel }}
                </span>
                <h1 class="mt-1 text-lg font-bold leading-snug text-white">{{ $discussion->title }}</h1>
            </div>
            <div class="flex flex-wrap gap-5 px-5 py-3.5">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Date</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $discussion->date->format('d M Y') }}</p>
                </div>
                @if ($discussion->createdBy)
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Created by</p>
                        <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $discussion->createdBy->name }}</p>
                    </div>
                @endif
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sections</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $discussion->sections->count() }}</p>
                </div>
            </div>
        </div>

        {{-- Sections --}}
        @forelse ($discussion->sections as $index => $section)
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-700 text-[11px] font-bold text-white">
                        {{ $index + 1 }}
                    </span>
                    <h3 class="flex-1 text-sm font-semibold text-slate-900">{{ $section->heading }}</h3>
                </div>

                <div class="px-4 py-3.5">
                    @if ($section->body)
                        <p class="whitespace-pre-wrap text-sm leading-relaxed text-slate-700">{{ $section->body }}</p>
                    @else
                        <p class="text-sm italic text-slate-400">No content for this section.</p>
                    @endif
                </div>

                @if ($section->attachments->isNotEmpty())
                    @php
                        $images = $section->attachments->where('file_type', 'image');
                        $docs   = $section->attachments->whereNotIn('file_type', ['image']);
                    @endphp
                    <div class="border-t border-slate-100 bg-slate-50/60 px-4 py-3.5">
                        <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                            Attachments &middot; {{ $section->attachments->count() }}
                        </p>

                        @if ($images->isNotEmpty())
                            <div class="mb-3 flex flex-wrap gap-2">
                                @foreach ($images as $img)
                                    <a href="{{ $img->url }}"
                                       class="relative overflow-hidden rounded-xl border border-slate-200 shadow-sm">
                                        <img src="{{ $img->url }}"
                                             alt="{{ $img->original_name }}"
                                             class="h-24 w-24 object-cover">
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if ($docs->isNotEmpty())
                            <div class="flex flex-col gap-2">
                                @foreach ($docs as $doc)
                                    <a href="{{ $doc->url }}"
                                       class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xs font-medium text-slate-700 shadow-sm active:bg-slate-50">
                                        @if ($doc->file_type === 'pdf')
                                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-red-50">
                                                <svg class="h-3.5 w-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                            </span>
                                        @else
                                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-blue-50">
                                                <svg class="h-3.5 w-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </span>
                                        @endif
                                        <span class="min-w-0 flex-1 truncate">{{ $doc->original_name }}</span>
                                        <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white py-12 text-center shadow-sm">
                <p class="text-sm text-slate-400">No sections in this discussion.</p>
            </div>
        @endforelse

    </div>

</x-layouts.mower>
