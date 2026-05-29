<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
@include('admin.partials.export-pdf-styles')
</head>
<body>
    <div class="title-block">
        <h1>JOBS REPORT</h1>
        <p>Generated: {{ now()->format('d M Y, H:i') }} &nbsp;&bull;&nbsp; Total Records: {{ $jobs->count() }}</p>
    </div>

    <table style="table-layout:fixed;">
        <colgroup>
            <col style="width:2%">  {{-- # --}}
            <col style="width:8%">  {{-- Client Name --}}
            <col style="width:8%">  {{-- Address --}}
            <col style="width:5%">  {{-- Zone --}}
            <col style="width:6%">  {{-- Scheduled Date --}}
            <col style="width:5%">  {{-- Time --}}
            <col style="width:4%">  {{-- Est. (min) --}}
            <col style="width:9%">  {{-- Services --}}
            <col style="width:6%">  {{-- Equipment --}}
            <col style="width:5%">  {{-- Recurrence --}}
            <col style="width:6%">  {{-- Status --}}
            <col style="width:4%">  {{-- Priority --}}
            <col style="width:6%">  {{-- Payment Mode --}}
            <col style="width:6%">  {{-- Payment Status --}}
            <col style="width:7%">  {{-- Done By --}}
            <col style="width:7%">  {{-- Assigned Mowers --}}
            <col style="width:6%">  {{-- Created At --}}
        </colgroup>
        <thead>
            <tr>
                <th class="center">#</th>
                <th>Client Name</th>
                <th>Address</th>
                <th>Zone</th>
                <th class="center">Scheduled Date</th>
                <th class="center">Time</th>
                <th class="center">Est. (min)</th>
                <th>Services</th>
                <th>Equipment</th>
                <th>Recurrence</th>
                <th class="center">Status</th>
                <th class="center">Priority</th>
                <th>Payment Mode</th>
                <th>Payment Status</th>
                <th>Done By</th>
                <th>Assigned Mowers</th>
                <th class="center">Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($jobs as $i => $job)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>
                        {{ $job->client?->name ?? '—' }}
                        @if ($job->client?->customer_unique_id)
                            <br><span style="color:#94a3b8;font-size:6.5px;">#{{ $job->client->customer_unique_id }}</span>
                        @endif
                    </td>
                    <td>{{ $job->client_address ?? '—' }}</td>
                    <td>{{ $job->zone?->name ?? '—' }}</td>
                    <td class="center">{{ $job->scheduled_date?->format('d/m/Y') ?? '—' }}</td>
                    <td class="center">{{ $job->scheduled_time ? \Illuminate\Support\Carbon::parse($job->scheduled_time)->format('h:i A') : '—' }}</td>
                    <td class="center">{{ $job->estimated_duration_minutes ?? '—' }}</td>
                    <td>{{ is_array($job->required_services) ? implode(', ', $job->required_services) : '—' }}</td>
                    <td>{{ $job->equipmentType?->name ?? '—' }}</td>
                    <td>{{ $job->recurrence?->name ?? '—' }}</td>
                    <td class="center"><span class="badge">{{ $job->status ?? '—' }}</span></td>
                    <td class="center"><span class="badge">{{ $job->priority ?? '—' }}</span></td>
                    <td>{{ $job->payment_mode ?? '—' }}</td>
                    <td>{{ $job->payment_status ?? '—' }}</td>
                    <td>{{ $job->doneByUser?->name ?? '—' }}</td>
                    <td>{{ $job->assignedEmployees->pluck('name')->join(', ') ?: '—' }}</td>
                    <td class="center">{{ $job->created_at?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="17" class="no-records">No jobs found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">{{ config('app.name') }} &bull; Jobs Report &bull; {{ now()->format('d M Y') }}</div>
</body>
</html>
