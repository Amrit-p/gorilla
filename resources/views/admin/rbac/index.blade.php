<x-layouts.dashboard :title="'Roles & Permissions'">
    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Role & Permission Management</h1> 
            </div>
            <a href="{{ route('dashboard.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                Back to dashboard
            </a>
        </div>

        <div id="rbac-alert" class="mb-4 hidden"></div>

        <div class="overflow-x-auto rounded-2xl bg-white shadow-sm">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Name</th>
                        <th class="px-4 py-3 font-semibold">Email</th>
                        <th class="px-4 py-3 font-semibold">Current Role</th>
                        <th class="px-4 py-3 font-semibold">Assign Role</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-4 py-3 text-slate-800">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ $user->roles->pluck('name')->first() ?? 'No Role' }}</td>
                            <td class="px-4 py-3">
                                <form class="role-form flex items-center gap-2" data-url="{{ route('admin.rbac.users.role.update', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" class="rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role }}" @selected($user->hasRole($role))>{{ $role }}</option>
                                        @endforeach
                                    </select>
                                    <x-ui.button type="submit">Update</x-ui.button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $users->links() }}</div>
    </div>

    <script>
        function showRbacAlert(message, isError = false) {
            const baseClass = isError
                ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            $('#rbac-alert').removeClass('hidden').attr('class', baseClass).text(message);
        }

        $('.role-form').on('submit', function (event) {
            event.preventDefault();
            const $form = $(this);

            $.ajax({
                url: $form.data('url'),
                method: 'PATCH',
                data: $form.serialize(),
                headers: { 'Accept': 'application/json' },
                success: function (response) {
                    showRbacAlert(response.message || 'Role updated successfully.');
                    setTimeout(() => window.location.reload(), 600);
                },
                error: function (xhr) {
                    const response = xhr.responseJSON || {};
                    const errors = response.errors || {};
                    const firstError = Object.values(errors)[0]?.[0] || 'Unable to update role.';
                    showRbacAlert(firstError, true);
                }
            });
        });
    </script>
</x-layouts.dashboard>
