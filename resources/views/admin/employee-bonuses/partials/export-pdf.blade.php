<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
@include('admin.partials.export-pdf-styles')
</head>
<body>
    <div class="title-block">
        <h1>EMPLOYEE BONUSES REPORT</h1>
        <p>Generated: {{ now()->format('d M Y, H:i') }} &nbsp;&bull;&nbsp; Total Records: {{ $bonuses->count() }}</p>
    </div>

    @php $isMower = auth()->user()?->hasRole(\App\Support\CrmRoles::MOWER); @endphp
    <table>
        <colgroup>
            <col style="width:4%">                              {{-- # --}}
            <col style="width:{{ $isMower ? '26%' : '22%' }}"> {{-- Employee --}}
            <col style="width:10%">                             {{-- Employee ID --}}
            <col style="width:10%">                             {{-- Amount --}}
            <col style="width:10%">                             {{-- Bonus Date --}}
            <col style="width:{{ $isMower ? '40%' : '30%' }}"> {{-- Description --}}
            @unless ($isMower)
            <col style="width:14%">                             {{-- Added By --}}
            @endunless
        </colgroup>
        <thead>
            <tr>
                <th class="center">#</th>
                <th>Employee</th>
                <th class="center">Employee ID</th>
                <th class="right">Amount ($)</th>
                <th class="center">Bonus Date</th>
                <th>Description</th>
                @unless ($isMower)
                <th>Added By</th>
                @endunless
            </tr>
        </thead>
        <tbody>
            @forelse ($bonuses as $i => $bonus)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $bonus->employee?->name ?? '—' }}</td>
                    <td class="center">#{{ $bonus->employee?->user_unique_id ?? '—' }}</td>
                    <td class="right">{{ number_format($bonus->amount, 2) }}</td>
                    <td class="center">{{ $bonus->bonus_date?->format('d/m/Y') }}</td>
                    <td>{{ $bonus->description ?: '—' }}</td>
                    @unless ($isMower)
                    <td>{{ $bonus->creator?->name ?? '—' }}</td>
                    @endunless
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isMower ? 6 : 7 }}" class="no-records">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">{{ config('app.name') }} &bull; Generated {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
