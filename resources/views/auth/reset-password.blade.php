<x-layouts.app :title="'Reset Password - ' . $websiteBranding['site_name']">
    <div class="mx-auto flex min-h-screen max-w-md items-center px-4 py-10">
        <div class="w-full rounded-2xl bg-white p-6 shadow-sm">
            <x-auth.branding />
            <h1 class="text-xl font-bold text-slate-900">Reset password</h1>
            <p class="mt-1 text-sm text-slate-600">Create a new password for your account.</p>

            @if ($errors->any())
                <x-ui.alert class="mt-4" type="error" :message="$errors->first()" />
            @endif

            <form class="mt-5 space-y-4" action="{{ route('password.store') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <x-ui.input label="Email" name="email" type="email" required />
                <x-ui.input label="New password" name="password" type="password" required />
                <x-ui.input label="Confirm password" name="password_confirmation" type="password" required />

                <div class="flex items-center justify-end">
                    <x-ui.button type="submit">Reset password</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
