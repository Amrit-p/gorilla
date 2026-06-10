<x-layouts.dashboard title="Follow-ups">
    <div class="space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Follow-ups</h2>
                <p class="text-sm text-slate-600">Track and manage follow-up actions.</p>
            </div>
            <a href="{{ route('admin.followups.create') }}"
               class="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                New follow-up
            </a>
        </div>

        {{-- Flash --}}
        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.followups.index') }}"
              class="flex flex-wrap gap-3 rounded-2xl border border-slate-200 bg-white p-4">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="Search outcome…"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm sm:w-64">
            <select name="status" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">All statuses</option>
                @foreach ($options['statuses'] as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            <select name="followable_type" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">All types</option>
                @foreach ($options['followableTypes'] as $type)
                    <option value="{{ $type }}" @selected(($filters['followable_type'] ?? '') === $type)>
                        {{ class_basename($type) }}
                    </option>
                @endforeach
            </select>
            <button type="submit"
                    class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Filter
            </button>
            @if (array_filter($filters))
                <a href="{{ route('admin.followups.index') }}"
                   class="rounded-md px-3 py-2 text-sm text-slate-500 hover:text-slate-700">
                    Clear
                </a>
            @endif
        </form>

        {{-- List --}}
        <div class="rounded-2xl border border-slate-200 bg-white">
            @if ($followups->isEmpty())
                <div class="flex flex-col items-center justify-center gap-3 py-16 text-center">
                    <svg class="h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-sm font-medium text-slate-500">No follow-ups found.</p>
                    <a href="{{ route('admin.followups.create') }}"
                       class="text-sm font-medium text-slate-900 underline underline-offset-2">
                        Create the first one
                    </a>
                </div>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($followups as $followup)
                        <li class="flex flex-wrap items-start gap-4 px-5 py-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    @php $followableUrl = $followup->followableUrl(); @endphp
                                    @if ($followableUrl)
                                        <a href="{{ $followableUrl }}" class="text-sm font-semibold text-slate-900 hover:underline">
                                            {{ class_basename($followup->followable_type) }} #{{ $followup->followable_id }}
                                        </a>
                                    @else
                                        <span class="text-sm font-semibold text-slate-900">
                                            {{ class_basename($followup->followable_type) }} #{{ $followup->followable_id }}
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $followup->status->badgeClass() }}">
                                        {{ $followup->status->label() }}
                                    </span>
                                </div>
                                <p class="mt-1 line-clamp-2 text-sm text-slate-600">{{ $followup->outcome }}</p>
                                <p class="mt-1 text-xs text-slate-400">
                                    Created {{ $followup->created_at->diffForHumans() }}
                                    @if ($followup->assignedTo)
                                        &middot; Assigned to {{ $followup->assignedTo->name }}
                                    @endif
                                    @if ($followup->next_followup_at)
                                        &middot; Next: {{ $followup->next_followup_at->format('d M Y') }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex shrink-0 gap-2">
                                <a href="{{ route('admin.followups.show', $followup) }}"
                                   class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                    View
                                </a>
                                <a href="{{ route('admin.followups.edit', $followup) }}"
                                   class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                    Edit
                                </a>
                                <button type="button"
                                        class="delete-followup-btn rounded-md border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                                        data-action="{{ route('admin.followups.destroy', $followup) }}">
                                    Delete
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>

                @if ($followups->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4">
                        {{ $followups->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>

    {{-- Delete confirmation modal --}}
    <div id="delete-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
            <h3 class="text-base font-semibold text-slate-900">Delete follow-up?</h3>
            <p class="mt-1 text-sm text-slate-600">This will permanently remove the follow-up record.</p>
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
        $(document).on('click', '.delete-followup-btn', function () {
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
    </script>
    @endpush
</x-layouts.dashboard>
