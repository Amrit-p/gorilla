@props([
    'headers' => [],
])

<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-slate-100 text-slate-700">
            <tr>
                @foreach ($headers as $header)
                    <th class="px-4 py-3 font-semibold">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            {{ $slot }}
        </tbody>
    </table>
</div>
