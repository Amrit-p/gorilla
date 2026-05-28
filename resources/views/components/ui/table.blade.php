@props([
    'headers' => [],
])

<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full text-left text-sm">
        <thead class="border-b border-slate-200 bg-slate-50">
            <tr class="divide-x divide-slate-100">
                @foreach ($headers as $header)
                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            {{ $slot }}
        </tbody>
    </table>
</div>
