<x-layouts.app :title="'Forgot Password - ' . $websiteBranding['site_name']">
    <div class="mx-auto flex min-h-screen max-w-md items-center px-4 py-10">
        <div class="w-full rounded-2xl bg-white p-6 shadow-sm">
            <x-auth.branding />
            <h1 class="text-xl font-bold text-slate-900">Forgot password</h1>
            <p class="mt-1 text-sm text-slate-600">
                Enter your email and we will send a reset link.
            </p>

            @if (session('status'))
                <x-ui.alert class="mt-4" :message="session('status')" />
            @endif

            @if ($errors->any())
                <x-ui.alert class="mt-4" type="error" :message="$errors->first()" />
            @endif

            <form class="mt-5 space-y-4" action="{{ route('password.email') }}" method="POST">
                @csrf
                <x-ui.input label="Email" name="email" type="email" required />

                <div class="flex items-center justify-between">
                    <a href="{{ route('login') }}" class="text-sm text-slate-700 underline hover:text-slate-900">
                        Back to login
                    </a>
                    <x-ui.button type="submit">Send reset link</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
