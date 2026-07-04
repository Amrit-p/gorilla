<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-200 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <th class="px-4 py-3">Month</th>
                <th class="px-4 py-3 text-right">Total Sales</th>
                <th class="px-4 py-3 text-right">Bonus</th>
                <th class="px-4 py-3 text-right">Percentage</th>
                <th class="px-4 py-3 text-right">Payout</th>
                <th class="px-4 py-3 text-right">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr class="border-b border-slate-100"
                    data-mower-id="{{ $row->mower_id }}"
                    data-year="{{ $row->year }}"
                    data-month="{{ $row->month }}"
                    data-sales="{{ $row->total_sales }}"
                    data-bonus="{{ $row->total_bonus }}">
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $row->month_label }}</td>
                    <td class="px-4 py-3 text-right text-slate-700">${{ number_format($row->total_sales, 2) }}</td>
                    <td class="px-4 py-3 text-right mower-bonus-cell cursor-pointer text-slate-700 hover:bg-pink-50 hover:text-pink-700"
                        data-user-id="{{ $row->mower_id }}"
                        data-mower-name="{{ $row->mower_name }}">
                        ${{ number_format($row->total_bonus, 2) }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="relative inline-block">
                            <input type="number" step="0.01" min="0" max="100"
                                class="salary-percentage-input w-20 rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-right text-sm text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100 disabled:bg-slate-100 disabled:text-slate-400"
                                value="{{ number_format($row->percentage, 2) }}"
                                @disabled($row->receipt_id !== null)>
                            <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-xs text-slate-400">%</span>
                        </div>
                    </td>
                    <td class="salary-payout-cell px-4 py-3 text-right font-semibold text-emerald-700 cursor-help"
                        title="{{ $row->formula_description }}">
                        ${{ number_format($row->salary_amount, 2) }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if ($row->receipt_id !== null)
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.salary-receipts.show', $row->receipt_id) }}"
                                   class="rounded-md border border-slate-300 px-2 py-1 text-xs hover:bg-slate-50">
                                    View
                                </a>
                                <button type="button"
                                        class="salary-delete-btn rounded-md border border-red-300 px-2 py-1 text-xs text-red-700 hover:bg-red-50"
                                        data-receipt-id="{{ $row->receipt_id }}">
                                    Delete
                                </button>
                            </div>
                        @else
                            <button type="button" class="salary-generate-btn rounded-md bg-slate-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-700">
                                Generate Receipt
                            </button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
