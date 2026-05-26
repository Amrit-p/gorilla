@props([
    'id' => 'modal',
    'title' => 'Modal Title',
    'maxWidth' => 'max-w-lg',
])

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4">
    <div class="w-full {{ $maxWidth }} max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-5 shadow-xl">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900">{{ $title }}</h3>
            <button type="button" data-close-modal="{{ $id }}" class="rounded-md border border-slate-300 px-2 py-1 text-xs text-slate-700">
                Close
            </button>
        </div>
        {{ $slot }}
    </div>
</div>
