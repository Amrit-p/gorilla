{{--
    The list of payouts for one mower and month, rendered into the payouts modal
    opened from the monthly breakdown's "Payouts" column. Editing reuses the
    shared payout modal via .js-open-payout; deleting hits the destroy route.
--}}
@if ($payouts->isEmpty())
    <div class="rounded-xl border border-dashed border-slate-300 bg-white px-4 py-10 text-center text-sm text-slate-400">
        No payouts recorded for {{ $mower->name }} this month.
    </div>
@else
    <div class="overflow-hidden rounded-xl border border-slate-200">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/80 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-4 py-3">Created</th>
                    <th class="px-4 py-3">Created by</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                    <th class="px-4 py-3 text-right">Bonus</th>
                    <th class="px-4 py-3 text-center">Jobs</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payouts as $payout)
                    <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50/60" data-payout-id="{{ $payout->id }}">
                        <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                            {{ optional($payout->created_at)->format('d M Y, H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ $payout->creator?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-slate-800">${{ number_format((float) $payout->amount, 2) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums {{ (float) $payout->bonus > 0 ? 'text-pink-700' : 'text-slate-500' }}">${{ number_format((float) $payout->bonus, 2) }}</td>
                        <td class="px-4 py-3 text-center tabular-nums">
                            @if ($payout->jobs_count > 0)
                                <button type="button"
                                        class="salary-payout-jobs-cell inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-200"
                                        data-payout-id="{{ $payout->id }}"
                                        data-job-count="{{ $payout->jobs_count }}"
                                        title="View the jobs in this payout">
                                    {{ $payout->jobs_count }}
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                    </svg>
                                </button>
                            @else
                                <span class="text-slate-400">0</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button"
                                        class="salary-payouts-edit inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-50"
                                        data-payout-id="{{ $payout->id }}"
                                        data-amount="{{ number_format((float) $payout->amount, 2, '.', '') }}"
                                        data-bonus="{{ number_format((float) $payout->bonus, 2, '.', '') }}"
                                        data-comment="{{ $payout->comment }}"
                                        data-created="{{ optional($payout->created_at)->format('d M Y, H:i') }}">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                    </svg>
                                    Edit
                                </button>
                                <button type="button"
                                        class="salary-payouts-delete inline-flex items-center gap-1 rounded-lg border border-red-200 bg-white px-2.5 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-50"
                                        data-payout-id="{{ $payout->id }}">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @if ($payout->comment)
                        <tr class="border-b border-slate-100 last:border-0" data-payout-comment-for="{{ $payout->id }}">
                            <td colspan="6" class="px-4 pb-3 pt-0 text-xs italic text-slate-500">“{{ $payout->comment }}”</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@endif
