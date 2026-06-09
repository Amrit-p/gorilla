<x-ui.table :headers="['Name', 'Phone', 'Email', 'Contracts', 'Actions']">
    @forelse ($contractors as $contractor)
        <tr>
            <td class="px-4 py-3 font-medium text-slate-800">
                <a href="{{ route('admin.contractors.detail', $contractor) }}" class="hover:underline text-slate-900">{{ $contractor->name }}</a>
            </td>
            <td class="px-4 py-3 text-slate-600">{{ $contractor->phone }}</td>
            <td class="px-4 py-3 text-slate-600">{{ $contractor->email ?? '—' }}</td>
            <td class="px-4 py-3 text-slate-600">{{ $contractor->contracts_count ?? $contractor->contracts()->count() }}</td>
            <td class="px-4 py-3">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.contractors.detail', $contractor) }}"
                        class="rounded-md border border-slate-300 px-2 py-1 text-xs hover:bg-slate-50">View</a>
                    <button type="button" class="edit-contractor rounded-md border border-slate-300 px-2 py-1 text-xs hover:bg-slate-50"
                        data-id="{{ $contractor->id }}">Edit</button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">No contractors found.</td>
        </tr>
    @endforelse
</x-ui.table>

<div class="mt-4">{{ $contractors->links() }}</div>
