<x-layouts.dashboard title="Follow-up #{{ $followup->id }}">
    <div class="space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.followups.index') }}"
                   class="text-sm text-slate-500 hover:text-slate-700">← Follow-ups</a>
                <h2 class="text-lg font-semibold text-slate-900">Follow-up #{{ $followup->id }}</h2>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $followup->status->badgeClass() }}">
                    {{ $followup->status->label() }}
                </span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.followups.edit', $followup) }}"
                   class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Edit
                </a>
                <button type="button" id="delete-btn"
                        class="rounded-md border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                    Delete
                </button>
            </div>
        </div>

        {{-- Flash --}}
        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Meta card --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-xs font-medium text-slate-500">Target</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">
                        {{ class_basename($followup->followable_type) }} #{{ $followup->followable_id }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-slate-500">Created by</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $followup->createdBy?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-slate-500">Created</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $followup->created_at->format('d M Y, H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-slate-500">Next follow-up</dt>
                    <dd class="mt-1 text-sm text-slate-900">
                        {{ $followup->next_followup_at?->format('d M Y, H:i') ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-slate-500">Last updated</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $followup->updated_at->diffForHumans() }}</dd>
                </div>
            </dl>
        </div>

        {{-- Outcome --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h3 class="mb-3 text-sm font-semibold text-slate-700">Outcome</h3>
            <p class="whitespace-pre-wrap text-sm text-slate-900">{{ $followup->outcome }}</p>
        </div>

        {{-- Notes --}}
        @if ($followup->notes)
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h3 class="mb-3 text-sm font-semibold text-slate-700">Notes</h3>
                <p class="whitespace-pre-wrap text-sm text-slate-900">{{ $followup->notes }}</p>
            </div>
        @endif

    </div>

    {{-- Delete confirmation modal --}}
    <div id="delete-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
            <h3 class="text-base font-semibold text-slate-900">Delete follow-up?</h3>
            <p class="mt-1 text-sm text-slate-600">This will permanently remove this follow-up record.</p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" id="delete-modal-cancel"
                        class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>
                <form method="POST" action="{{ route('admin.followups.destroy', $followup) }}">
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
        $('#delete-btn').on('click', function () {
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
