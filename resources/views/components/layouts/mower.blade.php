@props([
    'title' => 'Mower',
    'showBack' => false,
    'backUrl' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#166534">
    <title>{{ $title }} — {{ $websiteBranding['site_name'] ?? config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <style>
        .mower-safe-bottom { padding-bottom: max(1rem, env(safe-area-inset-bottom)); }
        .mower-touch { min-height: 44px; }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <header class="sticky top-0 z-30 border-b border-emerald-800 bg-emerald-700 text-white shadow-md">
        <div class="mx-auto flex max-w-lg items-center gap-3 px-4 py-3">
            @if ($showBack && $backUrl)
                <a href="{{ $backUrl }}" class="mower-touch flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-800/50" aria-label="Back">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold">{{ $title }}</p>
                <p class="truncate text-xs text-emerald-100">{{ auth()->user()->name }}</p>
            </div>
            <a href="{{ route('mower.notifications.index') }}" class="mower-touch relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-800/50" aria-label="Notifications">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                @if (($unreadNotificationCount ?? 0) > 0)
                    <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-bold text-white">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                @endif
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="mower-touch rounded-lg bg-emerald-800 px-3 py-2 text-xs font-medium">Logout</button>
            </form>
        </div>
    </header>

    <main class="mower-safe-bottom mx-auto min-h-[calc(100vh-56px)] max-w-lg px-4 py-4">
        {{ $slot }}
    </main>

    @stack('scripts')
</body>
</html>
