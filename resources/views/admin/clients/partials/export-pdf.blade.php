<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
@include('admin.partials.export-pdf-styles')
</head>
<body>
    <div class="title-block">
        <h1>CUSTOMERS REPORT</h1>
        <p>Generated: {{ now()->format('d M Y, H:i') }} &nbsp;&bull;&nbsp; Total Records: {{ $clients->count() }}</p>
    </div>

    <table style="table-layout:fixed;">
        <colgroup>
            <col style="width:2%">  {{-- # --}}
            <col style="width:3%">  {{-- ID --}}
            <col style="width:7%">  {{-- Name --}}
            <col style="width:7%">  {{-- Email --}}
            <col style="width:5%">  {{-- Phone --}}
            <col style="width:8%">  {{-- Address --}}
            <col style="width:5%">  {{-- Zone --}}
            <col style="width:8%">  {{-- Service Types --}}
            <col style="width:5%">  {{-- Equipment --}}
            <col style="width:4%">  {{-- Job Type --}}
            <col style="width:4%">  {{-- Charges --}}
            <col style="width:5%">  {{-- Payment Mode --}}
            <col style="width:5%">  {{-- Payment Status --}}
            <col style="width:5%">  {{-- Customer Type --}}
            <col style="width:4%">  {{-- Client Type --}}
            <col style="width:4%">  {{-- Weed Spray --}}
            <col style="width:4%">  {{-- Re-comp. Days --}}
            <col style="width:5%">  {{-- Property Details --}}
            <col style="width:6%">  {{-- Special Remarks --}}
            <col style="width:4%">  {{-- Created At --}}
        </colgroup>
        <thead>
            <tr>
                <th class="center">#</th>
                <th class="center">ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Zone</th>
                <th>Service Types</th>
                <th>Equipment</th>
                <th>Job Type</th>
                <th class="right">Charges</th>
                <th>Payment Mode</th>
                <th>Payment Status</th>
                <th>Customer Type</th>
                <th>Client Type</th>
                <th>Weed Spray</th>
                <th class="center">Re-comp. Days</th>
                <th>Property Details</th>
                <th>Special Remarks</th>
                <th class="center">Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($clients as $i => $client)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td class="center">{{ $client->customer_unique_id ?? '—' }}</td>
                    <td>{{ $client->name ?? '—' }}</td>
                    <td>{{ $client->email ?? '—' }}</td>
                    <td>{{ $client->phone ?? '—' }}</td>
                    <td>{{ $client->address ?? '—' }}</td>
                    <td>{{ $client->zone?->name ?? '—' }}</td>
                    <td>{{ is_array($client->service_types) ? implode(', ', $client->service_types) : '—' }}</td>
                    <td>{{ $client->equipmentType?->name ?? '—' }}</td>
                    <td>{{ $client->job_type ?? '—' }}</td>
                    <td class="right">{{ $client->charges !== null ? '$' . number_format((float) $client->charges, 2) : '—' }}</td>
                    <td>{{ $client->payment_mode ?? '—' }}</td>
                    <td>{{ $client->payment_status ?? '—' }}</td>
                    <td>{{ $client->customer_type ?? '—' }}</td>
                    <td>{{ $client->client_type ?? '—' }}</td>
                    <td>{{ $client->weed_spray ?? '—' }}</td>
                    <td class="center">{{ $client->re_completion_days ?? '—' }}</td>
                    <td>{{ $client->property_details ?? '—' }}</td>
                    <td>{{ $client->special_remarks ?? '—' }}</td>
                    <td class="center">{{ $client->created_at?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="20" class="no-records">No customers found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">{{ config('app.name') }} &bull; Customers Report &bull; {{ now()->format('d M Y') }}</div>
</body>
</html>
