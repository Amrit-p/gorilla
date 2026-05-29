<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
@include('admin.partials.export-pdf-styles')
</head>
<body>
    <div class="title-block">
        <h1>LEADS REPORT</h1>
        <p>Generated: {{ now()->format('d M Y, H:i') }} &nbsp;&bull;&nbsp; Total Records: {{ $leads->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="center">#</th>
                <th>Client Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Address</th>
                <th>Zone</th>
                <th>Service Types</th>
                <th>Equipment</th>
                <th>Job Type</th>
                <th class="right">Charges</th>
                <th>Payment Mode</th>
                <th>Payment Status</th>
                <th>Status</th>
                <th>Assigned To</th>
                <th class="center">Lead Date</th>
                <th class="center">Converted At</th>
                <th class="center">Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($leads as $i => $lead)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $lead->client_name ?? '—' }}</td>
                    <td>{{ $lead->email ?? '—' }}</td>
                    <td>{{ $lead->mobile_number ?? '—' }}</td>
                    <td>{{ $lead->address ?? '—' }}</td>
                    <td>{{ $lead->zone?->name ?? '—' }}</td>
                    <td>{{ is_array($lead->service_types) ? implode(', ', $lead->service_types) : '—' }}</td>
                    <td>{{ $lead->equipmentType?->name ?? '—' }}</td>
                    <td>{{ $lead->job_type ?? '—' }}</td>
                    <td class="right">{{ $lead->charges !== null ? '$' . number_format((float) $lead->charges, 2) : '—' }}</td>
                    <td>{{ $lead->payment_mode ?? '—' }}</td>
                    <td>{{ $lead->payment_status ?? '—' }}</td>
                    <td class="center">
                        <span class="badge">{{ $lead->status ?? '—' }}</span>
                        @if ($lead->is_locked)
                            <br><span class="badge badge-red" style="margin-top:2px;">Locked</span>
                        @endif
                        @if ($lead->client)
                            <br><span class="badge badge-green" style="margin-top:2px;">Customer</span>
                        @endif
                    </td>
                    <td>{{ $lead->assignedSalesUser?->name ?? 'Unassigned' }}</td>
                    <td class="center">{{ $lead->lead_date?->format('d/m/Y') ?? '—' }}</td>
                    <td class="center">{{ $lead->converted_at?->format('d/m/Y') ?? '—' }}</td>
                    <td class="center">{{ $lead->created_at?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="17" class="no-records">No leads found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">{{ config('app.name') }} &bull; Leads Report &bull; {{ now()->format('d M Y') }}</div>
</body>
</html>
