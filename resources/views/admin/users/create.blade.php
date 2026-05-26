<x-layouts.dashboard :title="'Create User'" subtitle="Add team members and assign roles">
    <x-ui.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Users', 'url' => route('admin.users.index')],
        ['label' => 'Create User'],
    ]" />

    <div class="mx-auto max-w-lg space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Create User</h2>
            <p class="text-sm text-slate-600">A unique user ID is assigned automatically (starting at #{{ config('mowing.user_unique_id_start', 1001) }}).</p>
        </div>

        <div id="create-user-alert" class="hidden"></div>

        <form id="create-user-form" method="POST" action="{{ route('admin.users.store') }}" novalidate class="js-validate-form space-y-4 rounded-2xl border border-slate-200 bg-white p-6" data-validate="user">
            @csrf
            @include('components.forms.user-fields', [
                'roles' => $roles,
                'efficiencies' => $efficiencies,
                'statuses' => $statuses,
            ])
            <x-ui.input label="Password" name="password" type="password" />
            <x-ui.input label="Confirm Password" name="password_confirmation" type="password" />
            <div class="flex flex-wrap gap-3">
                <x-ui.button type="submit">Create User</x-ui.button>
                <a href="{{ route('admin.users.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        function showCreateAlert(message, isError = false) {
            const baseClass = isError
                ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            $('#create-user-alert').removeClass('hidden').attr('class', baseClass).text(message);
        }

        $('#create-user-form').on('submit', function (event) {
            event.preventDefault();
            const $form = $(this);
            if (!$form.valid()) {
                return;
            }

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                headers: { 'Accept': 'application/json' },
                success: function (response) {
                    showCreateAlert(response.message || 'User created.');
                    setTimeout(function () {
                        window.location.href = "{{ route('admin.users.index') }}";
                    }, 500);
                },
                error: function (xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    const firstError = Object.values(errors)[0]?.[0] || 'Unable to create user.';
                    showCreateAlert(firstError, true);
                }
            });
        });
    </script>
</x-layouts.dashboard>
