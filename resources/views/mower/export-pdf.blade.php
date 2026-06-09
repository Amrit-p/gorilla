<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 8px; color: #1e293b; background: #fff; }

    .title-block { background: #1e293b; color: #fff; padding: 10px 14px; margin-bottom: 8px; }
    .title-block h1 { font-size: 13px; font-weight: bold; letter-spacing: 0.5px; }
    .title-block p  { font-size: 7.5px; margin-top: 3px; opacity: .75; }

    .filter-badge { display: inline-block; background: #065f46; color: #d1fae5; font-size: 7px; font-weight: bold; padding: 2px 7px; border-radius: 20px; margin-left: 6px; letter-spacing: 0.4px; vertical-align: middle; }

    table { width: 100%; border-collapse: collapse; margin-top: 4px; }

    thead tr th {
        font-size: 7.5px;
        font-weight: bold;
        padding: 5px 6px;
        border: 1px solid #cbd5e1;
        background: #f1f5f9;
        color: #334155;
        text-align: left;
        white-space: nowrap;
    }
    thead tr th.center { text-align: center; }

    tbody tr td {
        font-size: 7.5px;
        padding: 5px 6px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
        color: #1e293b;
    }
    tbody tr:nth-child(even) td { background: #f8fafc; }
    tbody tr:nth-child(odd) td  { background: #ffffff; }

    td.center { text-align: center; }
    td.muted   { color: #94a3b8; }

    .week-header td {
        background: #e2e8f0 !important;
        font-size: 7px;
        font-weight: bold;
        color: #475569;
        padding: 4px 6px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        border: 1px solid #cbd5e1;
    }

    .badge { display: inline-block; font-size: 7px; font-weight: bold; padding: 2px 6px; border-radius: 20px; white-space: nowrap; }
    .badge-completed  { background: #d1fae5; color: #065f46; }
    .badge-pending    { background: #fef3c7; color: #92400e; }
    .badge-in-progress { background: #dbeafe; color: #1e40af; }
    .badge-cancelled  { background: #fee2e2; color: #991b1b; }
    .badge-default    { background: #f1f5f9; color: #475569; }

    .pay-paid     { background: #d1fae5; color: #065f46; }
    .pay-pending  { background: #fef3c7; color: #92400e; }
    .pay-partial  { background: #dbeafe; color: #1e40af; }
    .pay-default  { background: #f1f5f9; color: #475569; }

    .footer { margin-top: 10px; font-size: 7px; color: #94a3b8; text-align: right; }
    .no-records { text-align: center; padding: 20px; color: #94a3b8; font-style: italic; }
    .summary { font-size: 7.5px; color: #475569; margin-bottom: 6px; }
</style>
</head>
<body>
@php
    $thisWeekStart = \Carbon\Carbon::today()->startOfWeek(\Carbon\Carbon::MONDAY);
    $grouped = $jobs->groupBy(fn ($job) =>
        \Carbon\Carbon::parse($job->scheduled_date)->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString()
    )->sortKeysDesc();

    $weekLabel = function (string $weekStartDate) use ($thisWeekStart): string {
        $start = \Carbon\Carbon::parse($weekStartDate);
        $end   = $start->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
        if ($start->eq($thisWeekStart)) {
            return 'This Week (' . $start->format('j M') . ' – ' . $end->format('j M, Y') . ')';
        }
        return $start->format('j M, Y') . ' – ' . $end->format('j M, Y');
    };

    $statusClass = function (string $status): string {
        return match (strtolower($status)) {
            'completed', 'done'   => 'badge-completed',
            'pending'             => 'badge-pending',
            'in progress', 'in-progress', 'inprogress' => 'badge-in-progress',
            'cancelled', 'canceled' => 'badge-cancelled',
            default               => 'badge-default',
        };
    };

    $payClass = function (?string $status): string {
        return match (strtolower($status ?? '')) {
            'paid'    => 'pay-paid',
            'pending' => 'pay-pending',
            'partial' => 'pay-partial',
            default   => 'pay-default',
        };
    };
@endphp

<div class="title-block">
    <h1>MY JOBS <span class="filter-badge">{{ strtoupper($scopeLabel) }}</span></h1>
    <p>Date: {{ \Carbon\Carbon::parse($scheduleDate)->format('d M Y') }} &nbsp;&bull;&nbsp; Generated: {{ now()->format('d M Y, H:i') }} &nbsp;&bull;&nbsp; Total: {{ $jobs->count() }} job(s)</p>
</div>

@if ($jobs->isEmpty())
    <table>
        <tr><td class="no-records">No jobs found for the selected filters.</td></tr>
    </table>
@else
    @foreach ($grouped as $weekStart => $weekJobs)
        <table>
            <thead>
                <tr class="week-header">
                    <td colspan="7">{{ $weekLabel($weekStart) }} &mdash; {{ $weekJobs->count() }} job(s)</td>
                </tr>
                <tr>
                    <th style="width:4%">#</th>
                    <th style="width:20%">Client</th>
                    <th style="width:28%">Address</th>
                    <th style="width:11%" class="center">Date</th>
                    <th style="width:9%" class="center">Time</th>
                    <th style="width:13%" class="center">Status</th>
                    <th style="width:15%" class="center">Payment</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($weekJobs as $i => $job)
                    <tr>
                        <td class="center muted">{{ $i + 1 }}</td>
                        <td>
                            @if ($job->client?->customer_unique_id)
                                <span style="color:#64748b">#{{ $job->client->customer_unique_id }}</span>
                            @endif
                            {{ $job->client?->name ?: 'Customer' }}
                        </td>
                        <td>{{ $job->client_address ?: '—' }}</td>
                        <td class="center">
                            {{ optional($job->scheduled_date)->format('d M Y') ?: '—' }}
                        </td>
                        <td class="center">
                            {{ $job->scheduled_time ? \Illuminate\Support\Carbon::parse($job->scheduled_time)->format('g:i A') : '—' }}
                            @if ($job->estimated_duration_minutes)
                                <br><span style="color:#94a3b8">{{ $job->estimated_duration_minutes }}min</span>
                            @endif
                        </td>
                        <td class="center">
                            <span class="badge {{ $statusClass($job->status ?? '') }}">
                                {{ $job->status ?? '—' }}
                            </span>
                        </td>
                        <td class="center">
                            @if ($job->payment_status)
                                <span class="badge {{ $payClass($job->payment_status) }}">
                                    {{ $job->payment_status }}
                                </span>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="height:8px"></div>
    @endforeach
@endif

<div class="footer">{{ config('app.name') }} &bull; My Jobs Export &bull; {{ now()->format('d M Y') }}</div>
</body>
</html>
