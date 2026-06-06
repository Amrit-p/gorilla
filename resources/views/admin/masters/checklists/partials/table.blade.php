<x-ui.table :headers="['Name', 'Points', 'Created', 'Actions']">
    @forelse ($records as $record)
        <tr>
            <td class="px-4 py-3 font-medium text-slate-800">{{ $record->name }}</td>
            <td class="px-4 py-3 text-slate-600">{{ $record->points_count }}</td>
            <td class="px-4 py-3 text-slate-600">{{ $record->created_at->format('d M Y') }}</td>
            <td class="px-4 py-3">
                <button type="button" class="edit-checklist rounded-md border border-slate-300 px-2 py-1 text-xs hover:bg-slate-50" data-id="{{ $record->id }}">Edit</button>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">No checklists found.</td>
        </tr>
    @endforelse
</x-ui.table>

<div class="mt-4">{{ $records->links() }}</div>
