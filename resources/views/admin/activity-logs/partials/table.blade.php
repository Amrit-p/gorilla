<x-ui.table :headers="['Time', 'Actor', 'Action', 'Description', 'IP', 'Context']">
    @forelse ($logs as $log)
        <tr>
            <td class="px-4 py-3 text-xs text-slate-600">{{ $log->created_at?->format('d M Y, h:i A') }}</td>
            <td class="px-4 py-3">
                <p class="text-sm font-medium text-slate-800">{{ $log->user?->name ?: 'System' }}</p>
                <p class="text-xs text-slate-500">{{ $log->user?->email ?: '-' }}</p>
            </td>
            <td class="px-4 py-3"><x-ui.badge>{{ $log->action }}</x-ui.badge></td>
            <td class="px-4 py-3 text-sm text-slate-700">{{ $log->description ?: '-' }}</td>
            <td class="px-4 py-3 text-xs text-slate-600">{{ $log->ip_address ?: '-' }}</td>
            <td class="px-4 py-3 text-xs text-slate-600">{{ json_encode($log->context ?? []) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">No activity log entries found.</td>
        </tr>
    @endforelse
</x-ui.table>
<div class="mt-4">{{ $logs->links() }}</div>
