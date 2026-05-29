<x-ui.table :headers="['Customer', 'Recurrence', 'Zone', 'Contact', 'Profile', 'Jobs', 'Actions']">
    @forelse ($clients as $client)
        <tr class="divide-x divide-slate-100 transition-colors hover:bg-slate-50/70">

            {{-- Customer --}}
            <td class="px-4 py-4">
                <a href="{{ route('admin.clients.show', $client) }}" class="text-sm font-semibold text-slate-800 leading-snug hover:text-emerald-700 hover:underline">
                    #{{ $client->customer_unique_id }} — {{ $client->name }}
                </a>
                <p class="mt-0.5 text-xs text-slate-500">{{ $client->address ?: '—' }}</p>
                @if ($client->lead_id)
                    <x-ui.badge type="success" class="mt-1">From lead</x-ui.badge>
                @endif
            </td>
            
            {{-- Recurrence --}}
            <td class="whitespace-nowrap px-4 py-4">
                <span class="text-sm text-slate-600">{{ $client->recurrence?->name ?? '—' }}</span>
            </td>
            {{-- Zone --}}
            <td class="whitespace-nowrap px-4 py-4">
                @if ($client->zone)
                    <div class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                        </svg>
                        <span class="text-sm text-slate-600">{{ $client->zone->name }}</span>
                    </div>
                @else
                    <span class="text-sm text-slate-400">—</span>
                @endif
            </td>

            {{-- Contact --}}
            <td class="whitespace-nowrap px-4 py-4">
                @if ($client->email)
                    <div class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                        </svg>
                        <span class="text-xs text-slate-600">{{ $client->email }}</span>
                    </div>
                @endif
                @if ($client->phone)
                    <div class="{{ $client->email ? 'mt-1' : '' }} flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
                        </svg>
                        <span class="text-xs text-slate-500">{{ $client->phone }}</span>
                    </div>
                @endif
                @if (! $client->email && ! $client->phone)
                    <span class="text-xs text-slate-400">—</span>
                @endif
            </td>

            {{-- Profile --}}
            <td class="whitespace-nowrap px-4 py-4">
                <div class="flex flex-wrap items-center gap-1">
                    <span class="text-sm text-slate-700">{{ $client->customer_type ?: "Don't Know" }}</span>
                    @if ($client->job_type || $client->client_type)
                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-xs text-slate-500">{{ $client->job_type ?: $client->client_type }}</span>
                    @endif
                    @if ($client->parking_status)
                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-xs text-slate-500">{{ $client->parking_status }}</span>
                    @endif
                    @if ($client->payment_status)
                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-xs text-slate-500">{{ $client->payment_status }}</span>
                    @endif
                </div>
            </td>

            {{-- Jobs --}}
            <td class="whitespace-nowrap px-4 py-4">
                <a href="{{ route('admin.clients.show', ['client' => $client, 'tab' => 'jobs']) }}" target="_blank" class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-200">{{ $client->jobs_count ?? 0 }} jobs</a>
            </td>

            {{-- Actions --}}
            <td class="whitespace-nowrap px-4 py-4">
                <div class="relative inline-block">
                    <button class="client-actions-btn inline-flex items-center justify-center rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 focus:outline-none" data-id="{{ $client->id }}" aria-label="Actions">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 6a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3ZM12 13.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3ZM12 21a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/>
                        </svg>
                    </button>
                    <div class="client-actions-menu hidden w-44 rounded-xl border border-slate-200 bg-white py-1 shadow-xl ring-1 ring-slate-900/5">
                        <a href="{{ route('admin.clients.show', $client) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
                            <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            View
                        </a>
                        @can('manage-customers')
                            <a href="{{ route('admin.clients.edit', $client) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                                </svg>
                                Edit
                            </a>
                            <div class="my-1 border-t border-slate-100"></div>
                            <button class="delete-client flex w-full items-center gap-2.5 px-3.5 py-2 text-sm text-red-600 transition-colors hover:bg-red-50" data-id="{{ $client->id }}">
                                <svg class="h-3.5 w-3.5 shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                </svg>
                                Delete
                            </button>
                        @endcan
                    </div>
                </div>
            </td>

        </tr>
    @empty
        <tr>
            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">No customers found.</td>
        </tr>
    @endforelse
</x-ui.table>
<div class="mt-4">{{ $clients->links() }}</div>
