<x-layouts.dashboard title="New Follow-up">
    <div class="space-y-5">

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.followups.index') }}"
               class="text-sm text-slate-500 hover:text-slate-700">← Follow-ups</a>
            <h2 class="text-lg font-semibold text-slate-900">New Follow-up</h2>
        </div>

        <form method="POST" action="{{ route('admin.followups.store') }}">
            @csrf

            @include('admin.followups._form', [
                'followup'      => null,
                'options'       => $options,
                'followableType' => $followableType ?? null,
                'followableId'   => $followableId ?? null,
            ])

            <div class="flex gap-3">
                <button type="submit"
                        class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Save follow-up
                </button>
                <a href="{{ route('admin.followups.index') }}"
                   class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>

    </div>
</x-layouts.dashboard>
