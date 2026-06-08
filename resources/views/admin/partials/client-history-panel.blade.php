@props(['tabbed' => false])

@once
<style>
.ch-clamped{overflow:hidden;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;}
</style>
@endonce

@if(!$tabbed)
<div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <button type="button" class="client-history-toggle flex w-full items-center justify-between gap-3 px-4 py-2.5 text-left hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-2.5">
            <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-slate-100">
                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                </svg>
            </span>
            <span class="text-sm font-semibold text-slate-700">Client History</span>
            <span class="client-history-accounting hidden rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200/60"></span>
        </div>
        <svg class="client-history-chevron h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/>
        </svg>
    </button>
    <div class="client-history-body border-t border-slate-100">
@else
<div class="client-history-body">
    <span class="client-history-accounting hidden"></span>{{-- keeps JS selector working --}}
@endif

        {{-- Loading --}}
        <div class="client-history-loading flex items-center justify-center gap-2 py-8 text-xs text-slate-400">
            <svg class="h-4 w-4 animate-spin text-slate-300" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            Loading history…
        </div>

        {{-- Content --}}
        <div class="client-history-content hidden">
            <div class="divide-y divide-slate-100 overflow-y-auto" style="max-height:320px;">

                {{-- Accounting level (tabbed only — shown as top banner) --}}
                @if($tabbed)
                <div class="client-history-level-banner hidden items-center gap-2 bg-amber-50 px-4 py-2.5">
                    <svg class="h-3.5 w-3.5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185Z"/>
                    </svg>
                    <span class="text-xs font-semibold text-amber-800">Accounting Level:</span>
                    <span class="client-history-level-name text-xs text-amber-700"></span>
                </div>
                @endif

                {{-- Service history grouped by job date --}}
                <div class="client-history-service-section hidden px-4 py-3">
                    <p class="mb-2 flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                        Service History
                    </p>
                    <div class="client-history-service space-y-2"></div>
                </div>

                {{-- Mower remarks --}}
                <div class="client-history-remarks-section hidden px-4 py-3">
                    <p class="mb-2 flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                        Mower Remarks
                    </p>
                    <div class="client-history-remarks space-y-2"></div>
                </div>

            </div>

            {{-- Empty state --}}
            <div class="client-history-empty hidden" style="display:none;flex-direction:column;align-items:center;justify-content:center;gap:4px;padding:2rem 0;text-align:center;">
                <svg class="h-10 w-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                </svg>
                <p class="text-sm font-medium text-slate-400">No history found</p>
                <p class="text-xs text-slate-300">This client has no previous service records.</p>
            </div>
        </div>

@if(!$tabbed)
    </div>{{-- /client-history-body --}}
</div>{{-- /card --}}
@else
</div>{{-- /client-history-body --}}
@endif
