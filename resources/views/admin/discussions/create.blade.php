<x-layouts.dashboard title="New Discussion">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">New Discussion</h2>
                <p class="text-sm text-slate-600">Record a new internal business discussion.</p>
            </div>
            <a href="{{ route('admin.discussions.index') }}"
               class="text-sm font-medium text-slate-600 hover:text-slate-900">
                ← Back
            </a>
        </div>

        <form action="{{ route('admin.discussions.store') }}"
              method="POST"
              enctype="multipart/form-data"
              class="space-y-5">
            @csrf
            @include('admin.discussions._form', ['discussion' => null])
        </form>
    </div>
</x-layouts.dashboard>
