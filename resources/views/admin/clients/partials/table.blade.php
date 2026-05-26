<x-ui.table :headers="['Customer', 'Contact', 'Profile', 'Jobs', 'Actions']">
    @forelse ($clients as $client)
        <tr>
            <td class="px-4 py-3">
                <a href="{{ route('admin.clients.show', $client) }}" class="font-medium text-emerald-700 hover:underline">
                    #{{ $client->customer_unique_id }} — {{ $client->name }}
                </a>
                <p class="text-xs text-slate-500">{{ $client->address ?: '—' }}</p>
                @if ($client->lead_id)
                    <x-ui.badge type="success" class="mt-1">From lead</x-ui.badge>
                @endif
            </td>
            <td class="px-4 py-3 text-slate-600">
                <p>{{ $client->email ?: '—' }}</p>
                <p class="text-xs">{{ $client->phone ?: '—' }}</p>
            </td>
            <td class="px-4 py-3 text-slate-600">
                <p class="text-sm">{{ $client->customer_type ?: "Don't Know" }}</p>
                <p class="text-xs text-slate-500">
                    {{ $client->job_type ?: $client->client_type }}
                    @if ($client->parking_status)
                        • {{ $client->parking_status }}
                    @endif
                </p>
                <p class="text-xs text-slate-500">{{ $client->payment_status ?: '—' }}</p>
            </td>
            <td class="px-4 py-3">
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $client->jobs_count ?? 0 }} jobs</span>
            </td>
            <td class="px-4 py-3">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.clients.show', $client) }}" class="rounded-md border border-slate-300 px-2 py-1 text-xs hover:bg-slate-50">View</a>
                    <a href="{{ route('admin.clients.edit', $client) }}" class="rounded-md border border-slate-300 px-2 py-1 text-xs hover:bg-slate-50">Edit</a>
                    <button class="delete-client rounded-md border border-red-300 px-2 py-1 text-xs text-red-700" data-id="{{ $client->id }}">Delete</button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">No customers found.</td>
        </tr>
    @endforelse
</x-ui.table>
<div class="mt-4">{{ $clients->links() }}</div>
