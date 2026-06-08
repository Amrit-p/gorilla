<x-layouts.dashboard title="Edit Discussion">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Edit Discussion</h2>
                <p class="text-sm text-slate-600 truncate max-w-lg">{{ $discussion->title }}</p>
            </div>
            <a href="{{ route('admin.discussions.index') }}"
               class="text-sm font-medium text-slate-600 hover:text-slate-900">
                ← Back
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.discussions.update', $discussion) }}"
              method="POST"
              enctype="multipart/form-data"
              class="space-y-5">
            @csrf
            @method('PATCH')
            @include('admin.discussions._form')
        </form>
    </div>
</x-layouts.dashboard>
