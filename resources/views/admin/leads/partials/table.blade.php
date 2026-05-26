<x-ui.table :headers="['Contact / Service', 'Contact', 'Job / Payment', 'Status', 'Assigned', 'Actions']">
    @forelse ($leads as $lead)
        <tr>
            <td class="px-4 py-3">
                <p class="font-medium text-slate-800">{{ $lead->client_name ?: $lead->address }}</p>
                <p class="text-xs text-slate-500">
                    {{ is_array($lead->service_types) && $lead->service_types !== [] ? implode(', ', $lead->service_types) : '-' }}
                    @if ($lead->equipmentType)
                        • <span class="inline-flex items-center gap-1">
                            <span class="inline-block h-2 w-2 rounded-full" style="background-color: {{ $lead->equipmentType->color_code ?? '#64748b' }}"></span>
                            {{ $lead->equipmentType->name }}
                        </span>
                    @endif
                </p>
            </td>
            <td class="px-4 py-3 text-slate-600">
                <p>{{ $lead->email ?: '-' }}</p>
                <p class="text-xs">{{ $lead->mobile_number ?: '-' }}</p>
            </td>
            <td class="px-4 py-3 text-slate-600">
                <p class="text-sm">{{ $lead->job_type ?: '-' }}</p>
                <p class="text-xs text-slate-500">
                    @if ($lead->charges !== null)
                        ${{ number_format((float) $lead->charges, 2) }}
                    @else
                        -
                    @endif
                    @if ($lead->payment_mode)
                        • {{ $lead->payment_mode }}
                    @endif
                    @if ($lead->payment_status)
                        • {{ $lead->payment_status }}
                    @endif
                </p>
            </td>
            <td class="px-4 py-3">
                <x-ui.badge>{{ $lead->status }}</x-ui.badge>
                @if ($lead->is_locked)
                    <x-ui.badge type="warning" class="ml-1">Locked</x-ui.badge>
                @endif
                @if ($lead->client)
                    <x-ui.badge type="success" class="ml-1">Customer</x-ui.badge>
                @endif
            </td>
            <td class="px-4 py-3 text-slate-700">{{ $lead->assignedSalesUser?->name ?: 'Unassigned' }}</td>
            <td class="px-4 py-3">
                <div class="flex flex-wrap gap-2">
                    <button class="view-lead rounded-md border border-slate-300 px-2 py-1 text-xs" data-id="{{ $lead->id }}">View</button>
                    @can('manage-leads')
                        <a href="{{ route('admin.leads.edit', $lead) }}" class="rounded-md border border-slate-300 px-2 py-1 text-xs hover:bg-slate-50">Edit</a>
                        <button class="status-lead rounded-md border border-slate-300 px-2 py-1 text-xs" data-id="{{ $lead->id }}">Status</button>
                    @endcan
                    @can('manage-leads')
                        @if (! $lead->is_locked)
                            <button class="delete-lead rounded-md border border-red-300 px-2 py-1 text-xs text-red-700" data-id="{{ $lead->id }}">Delete</button>
                        @endif
                    @endcan
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">No leads found.</td>
        </tr>
    @endforelse
</x-ui.table>

<div class="mt-4">{{ $leads->links() }}</div>
