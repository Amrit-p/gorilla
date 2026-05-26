<x-layouts.dashboard :title="'Profile Management'" subtitle="Update your account details and password">
    <x-ui.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Profile'],
    ]" />

    <div class="mx-auto max-w-4xl space-y-4">
        <div id="profile-alert" class="hidden"></div>
        <div id="password-alert" class="hidden"></div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Profile Details</h2>
                <form id="profile-form" class="js-validate-form mt-4 space-y-4" action="{{ route('profile.update') }}" method="POST" novalidate data-validate="profile">
                    @csrf
                    @method('PATCH')
                    <x-ui.input label="Full name" name="name" :value="auth()->user()->name" />
                    <x-ui.input label="Email" name="email" type="email" :value="auth()->user()->email" />
                    <x-ui.input label="Phone" name="phone" :value="auth()->user()->phone" />
                    <x-ui.button type="submit">Save Profile</x-ui.button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Change Password</h2>
                <form id="password-form" class="js-validate-form mt-4 space-y-4" action="{{ route('profile.password.update') }}" method="POST" novalidate data-validate="password">
                    @csrf
                    @method('PUT')
                    <x-ui.input label="Current password" name="current_password" type="password" />
                    <x-ui.input label="New password" name="password" type="password" />
                    <x-ui.input label="Confirm new password" name="password_confirmation" type="password" />
                    <x-ui.button type="submit">Update Password</x-ui.button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function renderAlert(elementId, message, type = 'success') {
            const baseClass = type === 'error'
                ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            $('#' + elementId).removeClass('hidden').attr('class', baseClass).text(message);
        }

        function submitAjaxForm(formSelector, alertId) {
            $(formSelector).on('submit', function (event) {
                event.preventDefault();
                const $form = $(this);
                if (!$form.valid()) {
                    return;
                }
                $.ajax({
                    url: $form.attr('action'),
                    method: $form.find('input[name="_method"]').val() || 'POST',
                    data: $form.serialize(),
                    headers: { 'Accept': 'application/json' },
                    success: function (response) {
                        renderAlert(alertId, response.message || 'Saved successfully.');
                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        renderAlert(alertId, Object.values(errors)[0]?.[0] || 'Something went wrong.', 'error');
                    }
                });
            });
        }

        submitAjaxForm('#profile-form', 'profile-alert');
        submitAjaxForm('#password-form', 'password-alert');
    </script>
</x-layouts.dashboard>
