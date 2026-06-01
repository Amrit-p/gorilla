<x-ui.table :headers="['ID', 'Name', 'Email', 'Role', 'Efficiency', 'Incentive %', 'Status', 'Actions']">
        @forelse ($users as $user)
            <tr>
                <td class="px-4 py-3 font-mono text-sm text-slate-700">#{{ $user->user_unique_id }}</td>
                <td class="px-4 py-3 text-slate-700">
                    <p class="font-medium">{{ $user->name }}</p>
                    <p class="text-xs text-slate-500">{{ $user->phone ?: '—' }}</p>
                </td>
                <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                <td class="px-4 py-3 text-slate-700">{{ $user->roles->pluck('name')->first() ?? 'No role' }}</td>
                <td class="px-4 py-3">
                    <x-ui.efficiency-badge :efficiency="$user->efficiency ?? 'Average'" />
                </td>
                <td class="px-4 py-3 text-slate-700">
                    {{ $user->incentive_percentage !== null ? $user->incentive_percentage . '%' : '—' }}
                </td>
                <td class="px-4 py-3">
                    <x-ui.user-status-badge :status="$user->status ?? ($user->is_active ? 'Active' : 'Inactive')" />
                </td>
                <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="edit-user rounded-md border border-slate-300 px-2 py-1 text-xs hover:bg-slate-50" data-id="{{ $user->id }}">Edit</button>
                        <button type="button" class="toggle-status rounded-md border border-slate-300 px-2 py-1 text-xs hover:bg-slate-50" data-id="{{ $user->id }}" data-active="{{ $user->is_active ? 1 : 0 }}">
                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                        <button type="button" class="delete-user rounded-md border border-red-300 px-2 py-1 text-xs text-red-700 hover:bg-red-50" data-id="{{ $user->id }}">Delete</button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-4 py-6 text-center text-sm text-slate-500">No users found for selected filters.</td>
            </tr>
        @endforelse
</x-ui.table>

<div class="mt-4">{{ $users->links() }}</div>
