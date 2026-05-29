<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 8px; color: #1e293b; background: #fff; }

    .title-block { background: #10b981; color: #fff; padding: 12px 16px; margin-bottom: 2px; }
    .title-block h1 { font-size: 14px; font-weight: bold; letter-spacing: 0.5px; }
    .title-block p  { font-size: 8px; margin-top: 3px; opacity: .85; }

    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    thead tr th {
        background: #1e3a5f;
        color: #fff;
        font-size: 7.5px;
        font-weight: bold;
        text-align: center;
        padding: 5px 4px;
        border: 1px solid #1e3a5f;
        white-space: nowrap;
    }
    tbody tr td {
        font-size: 7.5px;
        padding: 4px 4px;
        border: 1px solid #cbd5e1;
        vertical-align: top;
        word-break: break-word;
    }
    tbody tr:nth-child(even) td { background: #f0f4f8; }
    tbody tr:nth-child(odd)  td { background: #ffffff; }

    .center { text-align: center; }
    .right  { text-align: right; }
    .badge  { display: inline-block; padding: 1px 5px; border-radius: 4px; font-size: 7px; background: #e2e8f0; color: #475569; }
    .badge-green { background: #d1fae5; color: #065f46; }
    .badge-red   { background: #fee2e2; color: #991b1b; }

    .footer { margin-top: 12px; font-size: 7px; color: #94a3b8; text-align: right; }
    .no-records { text-align: center; padding: 20px; color: #94a3b8; font-style: italic; }
</style>
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
