<x-ui.table :headers="['Name', 'Color', 'Sort', 'Status', 'Actions']">
    @forelse ($records as $record)
        <tr>
            <td class="px-4 py-3 font-medium text-slate-800">{{ $record->name }}</td>
            <td class="px-4 py-3">
                <span class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <span class="inline-block h-4 w-4 rounded-full border border-slate-200" style="background-color: {{ $record->color_code }}"></span>
                    <span class="font-mono text-xs">{{ $record->color_code }}</span>
                </span>
            </td>
            <td class="px-4 py-3 text-slate-600">{{ $record->sort_order }}</td>
            <td class="px-4 py-3">
                <x-ui.user-status-badge :status="$record->is_active ? 'Active' : 'Inactive'" />
            </td>
            <td class="px-4 py-3">
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="edit-master rounded-md border border-slate-300 px-2 py-1 text-xs hover:bg-slate-50" data-id="{{ $record->id }}">Edit</button>
                    <button type="button" class="toggle-master-status rounded-md border border-slate-300 px-2 py-1 text-xs hover:bg-slate-50" data-id="{{ $record->id }}" data-active="{{ $record->is_active ? 1 : 0 }}">
                        {{ $record->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button type="button" class="delete-master rounded-md border border-red-300 px-2 py-1 text-xs text-red-700 hover:bg-red-50" data-id="{{ $record->id }}">Delete</button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">No records found.</td>
        </tr>
    @endforelse
</x-ui.table>

<div class="mt-4">{{ $records->links() }}</div>
