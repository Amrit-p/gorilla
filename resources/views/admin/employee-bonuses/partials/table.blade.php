@php $isMower = auth()->user()?->hasRole(\App\Support\CrmRoles::MOWER); @endphp
<x-ui.table :headers="$isMower ? ['Employee', 'Amount', 'Bonus Date', 'Description'] : ['Employee', 'Amount', 'Bonus Date', 'Description', 'Added By', 'Actions']">
    @forelse ($bonuses as $bonus)
        <tr>
            <td class="px-4 py-3">
                <p class="font-medium text-slate-800">{{ $bonus->employee->name ?? '—' }}</p>
                <p class="text-xs text-slate-500">#{{ $bonus->employee->user_unique_id ?? '' }}</p>
            </td>
            <td class="px-4 py-3 text-sm font-semibold text-emerald-700">
                ${{ number_format($bonus->amount, 2) }}
            </td>
            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-700">
                {{ $bonus->bonus_date?->format('M d, Y') }}
            </td>
            <td class="px-4 py-3 text-sm text-slate-600">
                {{ $bonus->description ?: '—' }}
            </td>
            @unless ($isMower)
            <td class="px-4 py-3 text-sm text-slate-600">
                {{ $bonus->creator->name ?? '—' }}
            </td>
            <td class="whitespace-nowrap px-4 py-3">
                <div class="flex gap-2">
                    <a href="{{ route('admin.employee-bonuses.edit', $bonus) }}"
                       class="rounded-md border border-slate-300 px-2 py-1 text-xs hover:bg-slate-50">
                        Edit
                    </a>
                    <button type="button"
                            class="delete-bonus rounded-md border border-red-300 px-2 py-1 text-xs text-red-700 hover:bg-red-50"
                            data-id="{{ $bonus->id }}">
                        Delete
                    </button>
                </div>
            </td>
            @endunless
        </tr>
    @empty
        <tr>
            <td colspan="{{ $isMower ? 4 : 6 }}" class="px-4 py-10 text-center text-sm text-slate-400">
                <p>No bonuses found.</p>
                @php $filterUserId = $filters['user_id'] ?? null; @endphp
                @if ($filterUserId)
                    @can('create', \App\Models\EmployeeBonus::class)
                        <a href="{{ route('admin.employee-bonuses.create') }}?user_id={{ $filterUserId }}"
                           class="mt-3 inline-flex items-center gap-1.5 rounded-md border border-pink-300 bg-pink-50 px-3 py-1.5 text-xs font-medium text-pink-700 hover:bg-pink-100">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Add New Bonus
                        </a>
                    @endcan
                @endif
            </td>
        </tr>
    @endforelse
</x-ui.table>

<div class="mt-4">{{ $bonuses->links() }}</div>
