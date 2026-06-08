<x-layouts.dashboard title="Discussions">
    <div class="space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Discussions</h2>
                <p class="text-sm text-slate-600">Internal business discussions — admin only.</p>
            </div>
            <a href="{{ route('admin.discussions.create') }}"
               class="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                New discussion
            </a>
        </div>

        {{-- Flash --}}
        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="flex flex-wrap gap-3 rounded-2xl border border-slate-200 bg-white p-4">
            <input id="discussion-search" type="text" placeholder="Search by title…"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm sm:w-64">
            <select id="discussion-category-filter"
                    class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">All categories</option>
                <option value="worker">Worker</option>
                <option value="budget">Budget</option>
                <option value="expansion">Expansion</option>
                <option value="crm_update">CRM Update</option>
                <option value="general">General</option>
            </select>
        </div>

        {{-- List --}}
        <div class="rounded-2xl border border-slate-200 bg-white">
            @if ($discussions->isEmpty())
                <div class="flex flex-col items-center justify-center gap-3 py-16 text-center">
                    <svg class="h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p class="text-sm font-medium text-slate-500">No discussions yet.</p>
                    <a href="{{ route('admin.discussions.create') }}"
                       class="text-sm font-medium text-slate-900 underline underline-offset-2">
                        Create your first one
                    </a>
                </div>
            @else
                <ul id="discussions-list" class="divide-y divide-slate-100">
                    @foreach ($discussions as $discussion)
                        @php
                            $attachmentCount = $discussion->sections->sum(fn ($s) => $s->attachments->count());
                            $badgeClass = match($discussion->category) {
                                'budget'     => 'bg-yellow-100 text-yellow-700',
                                'worker'     => 'bg-blue-100 text-blue-700',
                                'expansion'  => 'bg-purple-100 text-purple-700',
                                'crm_update' => 'bg-emerald-100 text-emerald-700',
                                default      => 'bg-slate-100 text-slate-600',
                            };
                            $categoryLabel = match($discussion->category) {
                                'worker'     => 'Worker',
                                'budget'     => 'Budget',
                                'expansion'  => 'Expansion',
                                'crm_update' => 'CRM Update',
                                default      => 'General',
                            };
                        @endphp
                        <li class="discussion-item flex flex-wrap items-center gap-4 px-5 py-4"
                            data-category="{{ $discussion->category }}">
                            <div class="min-w-0 flex-1">
                                <p class="discussion-title truncate text-sm font-semibold text-slate-900">
                                    {{ $discussion->title }}
                                </p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $discussion->date->format('d M Y') }}
                                    &middot;
                                    {{ $discussion->sections->count() }} {{ Str::plural('section', $discussion->sections->count()) }}
                                    @if ($attachmentCount > 0)
                                        &middot; {{ $attachmentCount }} {{ Str::plural('file', $attachmentCount) }}
                                    @endif
                                </p>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                {{ $categoryLabel }}
                            </span>
                            <div class="flex shrink-0 gap-2">
                                <a href="{{ route('admin.discussions.show', $discussion) }}"
                                   class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                    View
                                </a>
                                <a href="{{ route('admin.discussions.edit', $discussion) }}"
                                   class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                    Edit
                                </a>
                                <button type="button"
                                        class="delete-discussion-btn rounded-md border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                                        data-action="{{ route('admin.discussions.destroy', $discussion) }}">
                                    Delete
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>

    {{-- Delete confirmation modal --}}
    <div id="delete-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
            <h3 class="text-base font-semibold text-slate-900">Delete discussion?</h3>
            <p class="mt-1 text-sm text-slate-600">This will permanently remove the discussion and all its files.</p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" id="delete-modal-cancel"
                        class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>
                <form id="delete-modal-form" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Delete modal
        $(document).on('click', '.delete-discussion-btn', function () {
            $('#delete-modal-form').attr('action', $(this).data('action'));
            $('#delete-modal').removeClass('hidden').addClass('flex');
        });

        $('#delete-modal-cancel').on('click', function () {
            $('#delete-modal').removeClass('flex').addClass('hidden');
        });

        $('#delete-modal').on('click', function (e) {
            if ($(e.target).is('#delete-modal')) {
                $(this).removeClass('flex').addClass('hidden');
            }
        });

        // Client-side search + category filter
        function applyFilters() {
            const search = $('#discussion-search').val().toLowerCase().trim();
            const category = $('#discussion-category-filter').val();

            $('#discussions-list .discussion-item').each(function () {
                const title = $(this).find('.discussion-title').text().toLowerCase();
                const cat   = $(this).data('category');
                const matchesSearch   = !search || title.includes(search);
                const matchesCategory = !category || cat === category;
                $(this).toggle(matchesSearch && matchesCategory);
            });
        }

        $('#discussion-search').on('keyup input', applyFilters);
        $('#discussion-category-filter').on('change', applyFilters);
    </script>
    @endpush
</x-layouts.dashboard>
