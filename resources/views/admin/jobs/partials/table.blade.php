<x-ui.table :headers="['Customer / Address', 'Schedule', 'Services', 'Payment', 'Mowers', 'Actions']">
    @forelse ($jobs as $job)
        <tr>
            <td class="px-4 py-3">
                <a href="{{ route('admin.jobs.show', $job) }}" class="font-medium text-emerald-700 hover:underline">
                    {{ $job->client?->name ?: 'N/A' }}
                </a>
                @if ($job->client?->customer_unique_id)
                    <span class="text-xs text-slate-500">#{{ $job->client->customer_unique_id }}</span>
                @endif
                <p class="text-xs text-slate-500">{{ $job->client_address ?: '-' }}</p>
            </td>
            <td class="px-4 py-3 text-slate-600">
                <p>{{ optional($job->scheduled_date)->format('d M Y') }}</p>
                <p class="text-xs">
                    {{ $job->scheduled_time ? \Illuminate\Support\Carbon::parse($job->scheduled_time)->format('h:i A') : '-' }}
                    @if ($job->estimated_duration_minutes)
                        • {{ $job->estimated_duration_minutes }} min
                    @endif
                </p>
                <div class="mt-1"><x-jobs.status-badge :status="$job->status" /></div>
            </td>
            <td class="px-4 py-3 text-slate-600">
                <p class="text-sm">{{ is_array($job->required_services) ? implode(', ', $job->required_services) : '-' }}</p>
                <p class="text-xs text-slate-500">{{ $job->parking_status ?: '-' }}</p>
            </td>
            <td class="px-4 py-3 text-slate-600">
                <p class="text-sm">{{ $job->payment_mode ?: '-' }}</p>
                <p class="text-xs text-slate-500">{{ $job->payment_status ?: '-' }}</p>
            </td>
            <td class="px-4 py-3 text-slate-700">
                <p class="text-sm">{{ $job->doneByUser?->name ?: '—' }}</p>
                @if ($job->assignedEmployees->isNotEmpty())
                    <p class="text-xs text-slate-500">{{ $job->assignedEmployees->pluck('name')->join(', ') }}</p>
                @endif
            </td>
            <td class="px-4 py-3">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.jobs.show', $job) }}" class="rounded-md border border-slate-300 px-2 py-1 text-xs">View</a>
                    @can('manage-job-records')
                        <a href="{{ route('admin.jobs.edit', $job) }}" class="rounded-md border border-slate-300 px-2 py-1 text-xs">Edit</a>
                    @endcan
                    @can('assign-jobs')
                        <button class="assign-job rounded-md border border-slate-300 px-2 py-1 text-xs" data-id="{{ $job->id }}">Assign</button>
                        <button class="status-job rounded-md border border-slate-300 px-2 py-1 text-xs" data-id="{{ $job->id }}" data-status="{{ $job->status }}">Status</button>
                    @endcan
                    @can('manage-job-records')
                        <button class="delete-job rounded-md border border-red-300 px-2 py-1 text-xs text-red-700" data-id="{{ $job->id }}">Delete</button>
                    @endcan
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">No jobs found.</td>
        </tr>
    @endforelse
</x-ui.table>
<div class="mt-4">{{ $jobs->links() }}</div>
