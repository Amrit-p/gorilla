<div class="overflow-x-auto">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="px-3 py-2 font-semibold">Mower</th>
                <th class="px-3 py-2 font-semibold">Completed</th>
                <th class="px-3 py-2 font-semibold">Hours</th>
                <th class="px-3 py-2 font-semibold">Efficiency</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($mowerPerformance as $row)
                <tr>
                    <td class="px-3 py-2 font-medium text-slate-800">{{ $row['name'] }}</td>
                    <td class="px-3 py-2 text-slate-700">{{ $row['completed_jobs'] }}</td>
                    <td class="px-3 py-2 text-slate-700">{{ $row['hours'] }}h</td>
                    <td class="px-3 py-2 text-slate-600">{{ $row['efficiency'] ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-3 py-6 text-center text-slate-500">No completed jobs in this range.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
