@props([
    'config' => [],
    'placement' => 'top',
])

@php
    /**
     * Shared pagination for CRM list tables.
     *
     * "All" never selects every row: crm_per_page() resolves it to a fixed chunk
     * and the table streams the rest over AJAX as the sentinel scrolls into view.
     * Render this twice — placement="top" for the picker and page links,
     * placement="bottom" for the sentinel and the behaviour hook.
     *
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     */
    $paginator = $config['paginator'];
    $infinite = (bool) ($config['infinite'] ?? false);
    $perPage = (string) ($config['perPage'] ?? \App\Support\CrmConstants::defaultPagination());
    $perPageOptions = \App\Support\CrmConstants::paginationOptions();
    $chunkUrl = $config['chunkUrl'] ?? '';
    $chunkParams = $config['params'] ?? [];
    $filtersFn = $config['filtersFn'] ?? '';
    $reloadFn = $config['reloadFn'] ?? '';
    $target = $config['target'] ?? '#jobs-table-container';
    $itemLabel = $config['itemLabel'] ?? 'row';
@endphp

@if ($placement === 'top')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <label for="crm-per-page" class="text-xs font-medium uppercase tracking-wide text-slate-400">Rows</label>
            <select id="crm-per-page" data-reload="{{ $reloadFn }}"
                class="crm-per-page cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                @foreach ($perPageOptions as $option)
                    <option value="{{ $option }}" @selected(! $infinite && $perPage === (string) $option)>{{ $option }}</option>
                @endforeach
                <option value="{{ \App\Support\CrmConstants::PAGINATION_ALL }}" @selected($infinite)>All</option>
            </select>
            <span class="text-xs text-slate-400">
                @if ($infinite)
                    <span id="crm-pagination-loaded">{{ $paginator->count() }}</span> of {{ $paginator->total() }} loaded
                @else
                    {{ $paginator->total() }} total
                @endif
            </span>
        </div>

        @unless ($infinite)
            <div class="crm-pagination-links" data-reload="{{ $reloadFn }}" data-per-page="{{ $perPage }}">{{ $paginator->links() }}</div>
        @endunless
    </div>
@else
    @if ($infinite)
        <div class="crm-scroll-sentinel mt-4 flex items-center justify-center gap-2 py-5 text-xs text-slate-400"
            data-url="{{ $chunkUrl }}"
            data-target="{{ $target }}"
            data-params="{{ json_encode($chunkParams) }}"
            data-filters="{{ $filtersFn }}"
            data-per-page="{{ \App\Support\CrmConstants::PAGINATION_ALL }}"
            data-next-page="{{ $paginator->currentPage() + 1 }}"
            data-last-page="{{ $paginator->lastPage() }}"
            data-total="{{ $paginator->total() }}"
            data-label="{{ $itemLabel }}">
            @if ($paginator->hasMorePages())
                Scroll for more&hellip;
            @else
                All {{ $paginator->total() }} {{ \Illuminate\Support\Str::plural($itemLabel, $paginator->total()) }} loaded.
            @endif
        </div>
    @else
        <div class="crm-pagination-links mt-4" data-reload="{{ $reloadFn }}" data-per-page="{{ $perPage }}">{{ $paginator->links() }}</div>
    @endif

    <script>window.crmTablePagination && window.crmTablePagination.init();</script>
@endif
