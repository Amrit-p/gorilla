<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.meta.website-seo')

    {{-- Tailwind CSS by CDN (project rule: no npm build pipeline) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- jQuery CDN for upcoming AJAX modules --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    {{-- Main content slot shared by all pages --}}
    {{ $slot }}

    @include('components.scripts.jquery-validate')
    @stack('scripts')
</body>
</html>
