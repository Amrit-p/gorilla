<x-layouts.dashboard :title="'User Management'">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Users</h2>
                <p class="text-sm text-slate-600">Manage team members, roles, efficiency, and status.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Add User
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div id="user-alert" class="hidden"></div>

        <form id="filters-form" class="grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-5">
            <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search ID, name, email, phone" class="filter rounded-md border border-slate-300 px-3 py-2 text-sm lg:col-span-2">
            <select name="status" class="filter rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">All statuses</option>
                @foreach ($statuses as $statusOption)
                    <option value="{{ $statusOption }}" @selected($filters['status'] === $statusOption)>{{ $statusOption }}</option>
                @endforeach
            </select>
            <select name="efficiency" class="filter rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">All efficiency</option>
                @foreach ($efficiencies as $efficiencyOption)
                    <option value="{{ $efficiencyOption }}" @selected($filters['efficiency'] === $efficiencyOption)>{{ $efficiencyOption }}</option>
                @endforeach
            </select>
            <select name="role" class="filter rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">All roles</option>
                @foreach ($filterRoles as $role)
                    <option value="{{ $role }}" @selected($filters['role'] === $role)>{{ $role }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 sm:col-span-2 lg:col-span-5 lg:max-w-xs">
                Apply Filters
            </button>
        </form>

        <div id="users-table-container" class="relative">
            @include('admin.users.partials.table', ['users' => $users])
        </div>
    </div>

    <style>
        #users-loading-overlay {
            position: absolute;
            inset: 0;
            z-index: 20;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
        }
        #users-loading-overlay .users-spinner {
            width: 28px;
            height: 28px;
            border: 2.5px solid #e0e7ff;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: users-spin 0.7s linear infinite;
        }
        @keyframes users-spin {
            to { transform: rotate(360deg); }
        }
    </style>

    <x-ui.modal id="edit-user-modal" title="Edit User">
        <form id="edit-user-form" class="js-validate-form space-y-3" data-validate="user-edit" novalidate>
            @csrf
            @method('PATCH')
            <input type="hidden" name="user_id">
            <div id="edit-user-unique-id" class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-mono text-slate-600"></div>
            @include('components.forms.user-fields', [
                'roles' => $roles,
                'efficiencies' => $efficiencies,
                'statuses' => $statuses,
            ])
            <x-ui.input label="New Password (optional)" name="password" type="password" />
            <x-ui.input label="Confirm New Password" name="password_confirmation" type="password" />
            <x-ui.button type="submit">Update User</x-ui.button>
        </form>
    </x-ui.modal>

    <script>
        function showUserAlert(message, isError = false) {
            const baseClass = isError
                ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            $('#user-alert').removeClass('hidden').attr('class', baseClass).text(message);
        }

        function closeModal(id) {
            $('#' + id).addClass('hidden').removeClass('flex');
        }

        function openModal(id) {
            $('#' + id).removeClass('hidden').addClass('flex');
        }

        $('[data-close-modal]').on('click', function () {
            closeModal($(this).data('close-modal'));
        });

        function showUsersLoading() {
            if ($('#users-loading-overlay').length) return;
            $('#users-table-container').append(
                '<div id="users-loading-overlay">' +
                '<div style="display:flex;flex-direction:column;align-items:center;gap:10px;">' +
                '<div class="users-spinner"></div>' +
                '<span style="font-size:0.7rem;font-weight:500;color:#64748b;letter-spacing:0.05em;">Loading…</span>' +
                '</div>' +
                '</div>'
            );
        }

        function fetchUsers(url = "{{ route('admin.users.index') }}") {
            showUsersLoading();
            $.get(url, $('#filters-form').serialize(), function (response) {
                $('#users-table-container').html(response.html);
            }).fail(function () {
                $('#users-loading-overlay').remove();
                showUserAlert('Failed to load users. Please try again.', true);
            });
        }

        $('#filters-form').on('submit', function (event) {
            event.preventDefault();
            fetchUsers();
        });

        $('#filters-form select.filter').on('change', function () {
            fetchUsers();
        });

        let userSearchTimer;
        $('#filters-form').on('input', 'input[type="text"].filter', function () {
            clearTimeout(userSearchTimer);
            userSearchTimer = setTimeout(function () {
                fetchUsers();
            }, 400);
        });

        $(document).on('click', '.edit-user', function () {
            const userId = $(this).data('id');
            $.get("{{ url('/admin/users') }}/" + userId, function (user) {
                const $form = $('#edit-user-form');
                $form.find('[name="user_id"]').val(user.id);
                $('#edit-user-unique-id').text('User ID: #' + user.user_unique_id);
                $form.find('[name="name"]').val(user.name);
                $form.find('[name="email"]').val(user.email);
                $form.find('[name="phone"]').val(user.phone || '');
                $form.find('[name="role"]').val(user.role);
                $form.find('[name="efficiency"]').val(user.efficiency);
                $form.find('[name="status"]').val(user.status);
                $form.find('[name="password"]').val('');
                $form.find('[name="password_confirmation"]').val('');
                openModal('edit-user-modal');
            });
        });

        $('#edit-user-form').on('submit', function (event) {
            event.preventDefault();
            const $form = $(this);
            if (!$form.valid()) {
                return;
            }
            const userId = $form.find('[name="user_id"]').val();
            $.ajax({
                url: "{{ url('/admin/users') }}/" + userId,
                method: 'POST',
                data: $form.serialize(),
                headers: { 'Accept': 'application/json' },
                success: function (response) {
                    closeModal('edit-user-modal');
                    showUserAlert(response.message);
                    fetchUsers();
                },
                error: function (xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    showUserAlert(Object.values(errors)[0]?.[0] || 'Unable to update user.', true);
                }
            });
        });

        $(document).on('click', '.toggle-status', function () {
            const userId = $(this).data('id');
            const isActive = Number($(this).data('active')) === 1 ? 0 : 1;
            $.ajax({
                url: "{{ url('/admin/users') }}/" + userId + "/status",
                method: 'POST',
                data: { _token: "{{ csrf_token() }}", _method: 'PATCH', is_active: isActive },
                headers: { 'Accept': 'application/json' },
                success: function (response) {
                    showUserAlert(response.message);
                    fetchUsers();
                },
                error: function (xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    showUserAlert(Object.values(errors)[0]?.[0] || 'Unable to change status.', true);
                }
            });
        });

        $(document).on('click', '.delete-user', function () {
            const userId = $(this).data('id');
            if (!confirm('Are you sure you want to delete this user?')) {
                return;
            }
            $.ajax({
                url: "{{ url('/admin/users') }}/" + userId,
                method: 'POST',
                data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
                headers: { 'Accept': 'application/json' },
                success: function (response) {
                    showUserAlert(response.message);
                    fetchUsers();
                },
                error: function () {
                    showUserAlert('Unable to delete user.', true);
                }
            });
        });

        $(document).on('click', '#users-table-container .pagination a', function (event) {
            event.preventDefault();
            fetchUsers($(this).attr('href'));
        });
    </script>
</x-layouts.dashboard>
