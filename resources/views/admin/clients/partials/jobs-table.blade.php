<x-ui.table :headers="['Schedule', 'Services', 'Status', 'Crew', 'Payment']">
    @forelse ($jobs as $job)
        <tr>
            <td class="px-4 py-3 text-slate-700">
                <p class="font-medium">{{ $job->scheduled_date?->format('M j, Y') ?: '—' }}</p>
                <p class="text-xs text-slate-500">{{ $job->scheduled_time ? \Illuminate\Support\Carbon::parse($job->scheduled_time)->format('g:i A') : '—' }}</p>
            </td>
            <td class="px-4 py-3 text-sm text-slate-600">
                {{ is_array($job->required_services) && $job->required_services !== [] ? implode(', ', $job->required_services) : '—' }}
            </td>
            <td class="px-4 py-3">
                <x-ui.badge>{{ $job->status }}</x-ui.badge>
            </td>
            <td class="px-4 py-3 text-sm text-slate-600">{{ $job->doneByUser?->name ?: 'Unassigned' }}</td>
            <td class="px-4 py-3 text-sm text-slate-600">
                {{ $job->payment_mode ?: '—' }}
                @if ($job->payment_status)
                    <span class="text-xs text-slate-500">• {{ $job->payment_status }}</span>
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">No jobs for this customer yet.</td>
        </tr>
    @endforelse
</x-ui.table>

<div class="mt-4">{{ $jobs->links() }}</div>
