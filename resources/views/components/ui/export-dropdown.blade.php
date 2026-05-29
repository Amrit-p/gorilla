@props([
    'excelHref'  => '#',
    'pdfHref'    => '#',
    'excelId'    => 'export-excel-link',
    'pdfId'      => 'export-pdf-link',
    'wrapperId'  => 'export-dropdown-wrap',
    'btnId'      => 'export-dropdown-btn',
    'menuId'     => 'export-dropdown-menu',
])

<div class="relative" id="{{ $wrapperId }}" data-export-dropdown>
    <button id="{{ $btnId }}" type="button"
        class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50">
        <svg class="h-3 w-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
        </svg>
        Export
        <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
        </svg>
    </button>

    <div id="{{ $menuId }}"
        class="absolute right-0 z-30 mt-1 hidden w-40 rounded-xl border border-slate-200 bg-white py-1 shadow-xl ring-1 ring-slate-900/5">
        <a id="{{ $excelId }}" href="{{ $excelHref }}"
            class="flex items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
            <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6M3.75 7.5h16.5M3.75 4.5h16.5M3.75 10.5h16.5M5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25V5.25A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25v13.5A2.25 2.25 0 0 0 5.25 21Z"/>
            </svg>
            Excel (.xlsx)
        </a>
        <a id="{{ $pdfId }}" href="{{ $pdfHref }}"
            class="flex items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
            <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
            </svg>
            PDF (.pdf)
        </a>
    </div>
</div>

@once
<script>
    $(document).on('click', '[data-export-dropdown] button', function (e) {
        e.stopPropagation();
        $(this).siblings('div').toggleClass('hidden');
    });
    $(document).on('click', function (e) {
        if (!$(e.target).closest('[data-export-dropdown]').length) {
            $('[data-export-dropdown] div').addClass('hidden');
        }
    });
</script>
@endonce
