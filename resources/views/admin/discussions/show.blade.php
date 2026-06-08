<x-layouts.dashboard title="{{ $discussion->title }}">
    @push('styles')
    <style>
        .section-view-card { transition: opacity 0.2s ease, transform 0.2s ease; }
        .section-view-card.excluded { opacity: 0.35; }
        .section-sidebar-item { transition: opacity 0.15s ease; }
        .section-sidebar-item.deselected { opacity: 0.55; }
    </style>
    @endpush

    @php
        $categoryLabel = match($discussion->category) {
            'worker'     => 'Worker',
            'budget'     => 'Budget',
            'expansion'  => 'Expansion',
            'crm_update' => 'CRM Update',
            default      => 'General',
        };
        $categoryBadge = match($discussion->category) {
            'budget'     => 'bg-yellow-100 text-yellow-700',
            'worker'     => 'bg-blue-100 text-blue-700',
            'expansion'  => 'bg-purple-100 text-purple-700',
            'crm_update' => 'bg-emerald-100 text-emerald-700',
            default      => 'bg-slate-100 text-slate-600',
        };
        $totalAttachments = $discussion->sections->sum(fn ($s) => $s->attachments->count());
    @endphp

    <div class="space-y-4">

        {{-- Breadcrumb + Edit --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex min-w-0 items-center gap-2 text-sm">
                <a href="{{ route('admin.discussions.index') }}"
                   class="flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-slate-900">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Discussions
                </a>
                <svg class="h-3.5 w-3.5 shrink-0 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="truncate font-semibold text-slate-800">{{ $discussion->title }}</span>
            </div>
            <a href="{{ route('admin.discussions.edit', $discussion) }}"
               class="flex shrink-0 items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
        </div>

        {{-- Two-column: main + sidebar --}}
        <div class="flex items-start gap-5">

            {{-- ═══════════════ MAIN CONTENT ═══════════════ --}}
            <div class="min-w-0 flex-1 space-y-4">

                {{-- Discussion meta card --}}
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    {{-- Dark header strip --}}
                    <div class="bg-gradient-to-br from-slate-900 to-slate-700 px-6 py-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <span class="mb-2.5 inline-flex items-center rounded-full bg-white/15 px-2.5 py-0.5 text-xs font-semibold text-white/90 ring-1 ring-white/20">
                                    {{ $categoryLabel }}
                                </span>
                                <h1 class="mt-1 text-xl font-bold leading-snug text-white">
                                    {{ $discussion->title }}
                                </h1>
                            </div>
                            @if ($discussion->createdBy)
                                <div class="text-right">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Created by</p>
                                    <p class="mt-0.5 text-sm font-semibold text-white">{{ $discussion->createdBy->name }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    {{-- Meta pills row --}}
                    <div class="flex flex-wrap gap-6 border-t border-slate-100 px-6 py-4">
                        <div>
                            <p class="mb-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Date</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $discussion->date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="mb-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Category</p>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $categoryBadge }}">
                                {{ $categoryLabel }}
                            </span>
                        </div>
                        @if ($discussion->worker)
                            <div>
                                <p class="mb-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Worker</p>
                                <p class="text-sm font-semibold text-slate-800">{{ $discussion->worker->name }}</p>
                            </div>
                        @endif
                        <div>
                            <p class="mb-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Sections</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $discussion->sections->count() }}</p>
                        </div>
                        @if ($totalAttachments > 0)
                            <div>
                                <p class="mb-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Attachments</p>
                                <p class="text-sm font-semibold text-slate-800">{{ $totalAttachments }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Section cards --}}
                @forelse ($discussion->sections as $index => $section)
                    <div class="section-view-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                         data-section-id="{{ $section->id }}">

                        {{-- Section heading bar --}}
                        <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white shadow-sm">
                                {{ $index + 1 }}
                            </span>
                            <h3 class="flex-1 text-sm font-semibold text-slate-900">{{ $section->heading }}</h3>
                        </div>

                        {{-- Body text --}}
                        <div class="px-5 py-4">
                            @if ($section->body)
                                <p class="whitespace-pre-wrap text-sm leading-relaxed text-slate-700">{{ $section->body }}</p>
                            @else
                                <p class="text-sm italic text-slate-400">No content for this section.</p>
                            @endif
                        </div>

                        {{-- Attachments --}}
                        @if ($section->attachments->isNotEmpty())
                            @php
                                $images = $section->attachments->where('file_type', 'image');
                                $docs   = $section->attachments->where('file_type', '!=', 'image');
                            @endphp
                            <div class="border-t border-slate-100 bg-slate-50/60 px-5 py-4">
                                <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                    Attachments &middot; {{ $section->attachments->count() }}
                                </p>

                                {{-- Image grid --}}
                                @if ($images->isNotEmpty())
                                    <div class="mb-3 flex flex-wrap gap-2.5">
                                        @foreach ($images as $img)
                                            <a href="{{ $img->url }}" target="_blank" rel="noopener"
                                               class="group relative overflow-hidden rounded-xl border border-slate-200 shadow-sm transition hover:border-slate-400 hover:shadow-md">
                                                <img src="{{ $img->url }}"
                                                     alt="{{ $img->original_name }}"
                                                     class="h-28 w-28 object-cover">
                                                {{-- Dark overlay on hover --}}
                                                <div class="absolute inset-0 bg-black/0 transition duration-200 group-hover:bg-black/40"></div>
                                                {{-- Zoom icon --}}
                                                <div class="absolute inset-0 flex items-center justify-center opacity-0 transition duration-200 group-hover:opacity-100">
                                                    <svg class="h-6 w-6 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                                    </svg>
                                                </div>
                                                {{-- Filename caption --}}
                                                <div class="absolute bottom-0 left-0 right-0 translate-y-full bg-black/60 px-2 py-1.5 text-[10px] leading-tight text-white transition duration-200 group-hover:translate-y-0 truncate">
                                                    {{ $img->original_name }}
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Document / PDF chips --}}
                                @if ($docs->isNotEmpty())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($docs as $doc)
                                            <a href="{{ $doc->url }}" target="_blank" rel="noopener"
                                               class="group flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:shadow-md">
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
                                                <span class="max-w-[14rem] truncate">{{ $doc->original_name }}</span>
                                                <svg class="h-3 w-3 shrink-0 text-slate-400 opacity-0 transition group-hover:opacity-100"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <div class="rounded-2xl border border-slate-200 bg-white py-16 text-center shadow-sm">
                        <p class="text-sm text-slate-400">No sections in this discussion.</p>
                    </div>
                @endforelse

            </div>
            {{-- ═══ end main content ═══ --}}


            {{-- ═══════════════ PDF SIDEBAR ═══════════════ --}}
            <aside class="shrink-0 sticky top-4">

                {{-- ─── Collapsed strip (hidden by default) ─── --}}
                <div id="sidebar-collapsed-view"
                     style="display:none"
                     class="cursor-pointer flex-col items-center gap-3 rounded-2xl border border-slate-200 bg-white py-4 px-2.5 shadow-sm transition hover:border-slate-300 hover:shadow-md"
                     title="Expand PDF export panel">
                    <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400"
                          style="writing-mode:vertical-rl;transform:rotate(180deg);">
                        PDF Export
                    </span>
                    <svg class="h-4 w-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                    </svg>
                </div>

                {{-- ─── Expanded panel ─── --}}
                <div id="sidebar-expanded-view" class="w-72 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    {{-- Panel header --}}
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-3.5">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="text-sm font-semibold text-slate-800">Export as PDF</h3>
                        </div>
                        <button type="button" id="panel-collapse-btn"
                                class="rounded-md p-1.5 text-slate-400 transition hover:bg-slate-200 hover:text-slate-700"
                                title="Collapse panel">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Instruction --}}
                    <p class="px-4 pt-3 pb-1 text-xs text-slate-500">
                        Uncheck sections to exclude them from the exported PDF.
                    </p>

                    {{-- Form --}}
                    <form id="export-form" method="POST"
                          action="{{ route('admin.discussions.export-pdf', $discussion) }}">
                        @csrf

                        {{-- Section toggle list --}}
                        <div class="max-h-[52vh] overflow-y-auto px-2 py-2">
                            @foreach ($discussion->sections as $section)
                                <label class="section-sidebar-item flex cursor-pointer select-none items-start gap-2.5 rounded-lg px-2.5 py-2.5 transition hover:bg-slate-50"
                                       data-for="{{ $section->id }}">
                                    <input type="checkbox"
                                           name="include_sections[]"
                                           value="{{ $section->id }}"
                                           class="section-toggle mt-0.5 h-3.5 w-3.5 flex-none cursor-pointer rounded border-slate-300 accent-slate-900"
                                           data-section="{{ $section->id }}"
                                           checked>
                                    <span class="text-xs leading-4 text-slate-700">
                                        {{ $section->heading }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        {{-- Footer actions --}}
                        <div class="space-y-3 border-t border-slate-100 p-4">
                            <div class="flex items-center justify-between text-xs">
                                <button type="button" id="select-all-btn"
                                        class="font-medium text-slate-600 underline underline-offset-2 hover:text-slate-900">
                                    Select all
                                </button>
                                <span id="selected-count" class="font-semibold text-slate-700"></span>
                                <button type="button" id="deselect-all-btn"
                                        class="font-medium text-slate-600 underline underline-offset-2 hover:text-slate-900">
                                    None
                                </button>
                            </div>
                            <button type="submit"
                                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 active:scale-[0.98]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Download PDF
                            </button>
                        </div>

                    </form>
                </div>
                {{-- end expanded panel --}}

            </aside>
            {{-- ═══ end sidebar ═══ --}}

        </div>
        {{-- end two-column --}}

    </div>

    @push('scripts')
    <script>
        const TOTAL    = {{ $discussion->sections->count() }};
        const PREF_KEY = 'discussion_sidebar_{{ $discussion->id }}';

        // ── Sidebar collapse / expand ──────────────────────
        function collapseSidebar() {
            $('#sidebar-expanded-view').css('display', 'none');
            $('#sidebar-collapsed-view').css('display', 'flex');
            try { localStorage.setItem(PREF_KEY, '1'); } catch (e) {}
        }
        function expandSidebar() {
            $('#sidebar-collapsed-view').css('display', 'none');
            $('#sidebar-expanded-view').css('display', '');
            try { localStorage.setItem(PREF_KEY, '0'); } catch (e) {}
        }

        $('#panel-collapse-btn').on('click', collapseSidebar);
        $('#sidebar-collapsed-view').on('click', expandSidebar);

        try { if (localStorage.getItem(PREF_KEY) === '1') collapseSidebar(); } catch (e) {}

        // ── Section visibility sync ────────────────────────
        function syncSections() {
            let count = 0;

            $('.section-toggle').each(function () {
                const id      = $(this).data('section');
                const checked = $(this).is(':checked');
                const $label  = $(this).closest('.section-sidebar-item');
                const $card   = $('.section-view-card[data-section-id="' + id + '"]');

                $card.toggleClass('excluded', !checked);
                $label.toggleClass('deselected', !checked);
                if (checked) count++;
            });

            $('#selected-count').text(count + ' / ' + TOTAL);
        }

        $(document).on('change', '.section-toggle', syncSections);

        $('#select-all-btn').on('click', function () {
            $('.section-toggle').prop('checked', true);
            syncSections();
        });
        $('#deselect-all-btn').on('click', function () {
            $('.section-toggle').prop('checked', false);
            syncSections();
        });

        syncSections();
    </script>
    @endpush
</x-layouts.dashboard>
