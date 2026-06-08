<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 9px;
        color: #1e293b;
        background: #fff;
        padding: 0 28px 28px;
    }

    /* ── Document header ── */
    .doc-header {
        background: #0f172a;
        padding: 18px 50px 16px;   /* 28px body padding + 22px inner = 50px */
        margin: 0 -28px 0;
    }
    .doc-header-label {
        font-size: 7px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #475569;
        margin-bottom: 6px;
    }
    .doc-header-title {
        font-size: 17px;
        font-weight: bold;
        color: #f8fafc;
        margin-bottom: 6px;
        word-wrap: break-word;
    }
    .doc-header-meta {
        font-size: 8px;
        color: #64748b;
    }
    .doc-header-count {
        font-size: 11px;
        font-weight: bold;
        color: #fff;
        float: right;
        margin-top: -38px;
        text-align: center;
    }
    .doc-header-count span {
        display: block;
        font-size: 7px;
        font-weight: normal;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-top: 3px;
    }

    /* ── Blue accent bar ── */
    .accent-bar {
        height: 3px;
        background: #1d4ed8;
        margin: 0 -28px 14px;
    }

    /* ── Metadata table ── */
    .meta-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 16px;
        border: 1px solid #e2e8f0;
    }
    .meta-table td {
        padding: 9px 12px;
        vertical-align: top;
        border-right: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .meta-table td:last-child { border-right: none; }
    .meta-cell-label {
        font-size: 7px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: #94a3b8;
        margin-bottom: 4px;
    }
    .meta-cell-value {
        font-size: 9px;
        font-weight: bold;
        color: #1e293b;
    }

    /* ── Category badges ── */
    .badge { display: inline; padding: 1px 7px 2px; border-radius: 10px; font-size: 8px; font-weight: bold; }
    .badge-worker   { background: #dbeafe; color: #1e40af; }
    .badge-budget   { background: #fef3c7; color: #92400e; }
    .badge-expansion{ background: #ede9fe; color: #6d28d9; }
    .badge-crm      { background: #d1fae5; color: #065f46; }
    .badge-general  { background: #f1f5f9; color: #475569; }

    /* ── Section cards ── */
    .section {
        margin-bottom: 12px;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #1e3a5f;
        page-break-inside: avoid;
    }
    .section-head {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 8px 13px;
    }
    .section-num {
        display: inline;
        background: #1e293b;
        color: #fff;
        font-size: 7.5px;
        font-weight: bold;
        padding: 2px 7px;
        border-radius: 3px;
        margin-right: 8px;
    }
    .section-title {
        font-size: 11px;
        font-weight: bold;
        color: #1e293b;
    }
    .section-body {
        padding: 10px 13px;
        font-size: 8.5px;
        line-height: 1.6;
        white-space: pre-wrap;
        word-break: break-word;
        color: #374151;
    }
    .section-empty {
        padding: 9px 13px;
        font-size: 8px;
        color: #94a3b8;
        font-style: italic;
    }

    /* ── Attachments ── */
    .attachments-block {
        border-top: 1px solid #e2e8f0;
        padding: 8px 13px 10px;
        background: #fafafa;
    }
    .attachments-label {
        font-size: 7px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #64748b;
        margin-bottom: 7px;
    }
    .att-img {
        max-width: 155px;
        max-height: 125px;
        border: 1px solid #e2e8f0;
        margin: 0 5px 5px 0;
        display: inline;
    }
    .att-file {
        display: inline;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 8px;
        margin: 0 4px 4px 0;
    }
    .att-pdf  { background: #fee2e2; color: #991b1b; }
    .att-doc  { background: #dbeafe; color: #1e40af; }

    /* ── Footer ── */
    .doc-footer {
        margin-top: 20px;
        padding-top: 8px;
        border-top: 1px solid #e2e8f0;
        font-size: 7px;
        color: #94a3b8;
        text-align: right;
    }

    .clearfix::after { content: ''; display: table; clear: both; }
</style>
</head>
<body>

@php
    $categoryLabel = match($discussion->category) {
        'worker'     => 'Worker',
        'budget'     => 'Budget',
        'expansion'  => 'Expansion',
        'crm_update' => 'CRM Update',
        default      => 'General',
    };
    $badgeClass = match($discussion->category) {
        'worker'     => 'badge-worker',
        'budget'     => 'badge-budget',
        'expansion'  => 'badge-expansion',
        'crm_update' => 'badge-crm',
        default      => 'badge-general',
    };
@endphp

{{-- ── Document header ── --}}
<div class="doc-header">
    <div class="doc-header-count">
        {{ $sections->count() }}
        <span>{{ Str::plural('Section', $sections->count()) }}</span>
    </div>
    <div class="doc-header-label">Discussion Record</div>
    <div class="doc-header-title">{{ $discussion->title }}</div>
    <div class="doc-header-meta">
        {{ $discussion->date->format('d M Y') }}
        &nbsp;&bull;&nbsp; {{ $categoryLabel }}
        @if ($discussion->worker)
            &nbsp;&bull;&nbsp; {{ $discussion->worker->name }}
        @endif
        &nbsp;&bull;&nbsp; Generated {{ now()->format('d M Y, H:i') }}
    </div>
</div>

{{-- ── Blue accent bar ── --}}
<div class="accent-bar"></div>

{{-- ── Metadata row ── --}}
<table class="meta-table">
    <tr>
        <td style="width:22%">
            <div class="meta-cell-label">Date</div>
            <div class="meta-cell-value">{{ $discussion->date->format('d M Y') }}</div>
        </td>
        <td style="width:22%">
            <div class="meta-cell-label">Category</div>
            <div class="meta-cell-value">
                <span class="badge {{ $badgeClass }}">{{ $categoryLabel }}</span>
            </div>
        </td>
        @if ($discussion->worker)
        <td style="width:28%">
            <div class="meta-cell-label">Worker</div>
            <div class="meta-cell-value">{{ $discussion->worker->name }}</div>
        </td>
        @endif
        @if ($discussion->createdBy)
        <td>
            <div class="meta-cell-label">Prepared By</div>
            <div class="meta-cell-value">{{ $discussion->createdBy->name }}</div>
        </td>
        @endif
    </tr>
</table>

{{-- ── Sections ── --}}
@forelse ($sections as $i => $section)
    <div class="section">

        {{-- Section heading --}}
        <div class="section-head">
            <span class="section-num">{{ $i + 1 }}</span>
            <span class="section-title">{{ $section->heading }}</span>
        </div>

        {{-- Body text --}}
        @if ($section->body)
            <div class="section-body">{{ $section->body }}</div>
        @else
            <div class="section-empty">No content for this section.</div>
        @endif

        {{-- Attachments --}}
        @if ($section->attachments->isNotEmpty())
            <div class="attachments-block">
                <div class="attachments-label">Attachments</div>

                <div class="clearfix">
                    {{-- Images as base64 for DomPDF --}}
                    @foreach ($section->attachments as $attachment)
                        @if ($attachment->file_type === 'image')
                            @php
                                $imgPath = Storage::disk('public')->path($attachment->file_path);
                                $imgSrc  = null;
                                if (file_exists($imgPath)) {
                                    $imgSrc = 'data:' . mime_content_type($imgPath) . ';base64,'
                                            . base64_encode(file_get_contents($imgPath));
                                }
                            @endphp
                            @if ($imgSrc)
                                <img src="{{ $imgSrc }}"
                                     class="att-img"
                                     alt="{{ $attachment->original_name }}">
                            @endif
                        @endif
                    @endforeach
                </div>

                {{-- Document / PDF chips --}}
                @foreach ($section->attachments as $attachment)
                    @if ($attachment->file_type !== 'image')
                        <span class="att-file {{ $attachment->file_type === 'pdf' ? 'att-pdf' : 'att-doc' }}">
                            {{ $attachment->original_name }}
                        </span>
                    @endif
                @endforeach
            </div>
        @endif

    </div>
@empty
    <p style="font-size:8.5px;color:#94a3b8;font-style:italic;padding:10px 0 4px;">
        No sections were selected for export.
    </p>
@endforelse

{{-- ── Footer ── --}}
<div class="doc-footer">
    {{ config('app.name') }}
    &nbsp;&bull;&nbsp; Confidential
    &nbsp;&bull;&nbsp; {{ now()->format('d M Y H:i') }}
</div>

</body>
</html>
