<x-layouts.app :title="'Login - ' . $websiteBranding['site_name']">
    <div class="mx-auto flex min-h-screen max-w-md items-center px-4 py-10">
        <div class="w-full rounded-2xl bg-white p-6 shadow-sm">
            <x-auth.branding />
            <h1 class="text-xl font-bold text-slate-900">Sign in</h1>
            <p class="mt-1 text-sm text-slate-600">Access your dashboard.</p>

            @if (session('status'))
                <x-ui.alert class="mt-4" :message="session('status')" />
            @endif

            <form class="js-validate-form mt-5 space-y-4" action="{{ route('login.store') }}" method="POST" novalidate data-validate="login">
                @csrf
                <x-ui.input label="Email" name="email" type="email" />
                <x-ui.input label="Password" name="password" type="password" />

                {{-- Remember-me support keeps users signed in across sessions. --}}
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
                    <span>Remember me</span>
                </label>

                <div class="flex items-center justify-between">
                    <a href="{{ route('password.request') }}" class="text-sm text-slate-700 underline hover:text-slate-900">
                        Forgot password?
                    </a>
                    <x-ui.button type="submit">Login</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
