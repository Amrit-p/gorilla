<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JobsExport extends SpreadsheetExport
{
    private const COLUMNS = [
        'A' => ['header' => '#',                  'width' => 6],
        'B' => ['header' => 'Customer Name',       'width' => 22],
        'C' => ['header' => 'Phone',               'width' => 14],
        'D' => ['header' => 'Address',             'width' => 30],
        'E' => ['header' => 'Zone',                'width' => 14],
        'F' => ['header' => 'Scheduled Date',      'width' => 14],
        'G' => ['header' => 'Scheduled Time',      'width' => 14],
        'H' => ['header' => 'Est. Duration (min)', 'width' => 18],
        'I' => ['header' => 'Consumed (min)',      'width' => 15],
        'J' => ['header' => 'Services',            'width' => 28],
        'K' => ['header' => 'Equipment Type',      'width' => 16],
        'L' => ['header' => 'Status',              'width' => 14],
        'M' => ['header' => 'Priority',            'width' => 10],
        'N' => ['header' => 'Customer Type',       'width' => 15],
        'O' => ['header' => 'Parking Status',      'width' => 15],
        'P' => ['header' => 'Pet Warning',         'width' => 12],
        'Q' => ['header' => 'Payment Mode',        'width' => 14],
        'R' => ['header' => 'Payment Status',      'width' => 15],
        'S' => ['header' => 'Charges ($)',         'width' => 14],
        'T' => ['header' => 'Done By',             'width' => 18],
        'U' => ['header' => 'Assigned Mowers',     'width' => 24],
        'V' => ['header' => 'Recurring',           'width' => 10],
        'W' => ['header' => 'Recurrence',          'width' => 14],
        'X' => ['header' => 'Site Instructions',   'width' => 28],
        'Y' => ['header' => 'Special Remarks',     'width' => 28],
        'Z' => ['header' => 'Internal Notes',      'width' => 28],
        'AA' => ['header' => 'Created By',          'width' => 16],
        'AB' => ['header' => 'Created At',          'width' => 16],
    ];

    public function __construct(private readonly Collection $jobs) {}

    protected function getColumns(): array
    {
        return self::COLUMNS;
    }

    protected function getLastColumn(): string
    {
        return 'AB';
    }

    protected function getTitle(): string
    {
        return 'Jobs Report';
    }

    protected function getSheetName(): string
    {
        return 'Jobs';
    }

    protected function getRecordCount(): int
    {
        return $this->jobs->count();
    }

    protected function renderData(Worksheet $sheet): void
    {
        $row = 5;

        foreach ($this->jobs as $i => $job) {
            $sheet->setCellValue('A'.$row, $i + 1);
            $sheet->setCellValue('B'.$row, $job->customerDisplayName() === 'N/A' ? '' : $job->customerDisplayName());
            $sheet->setCellValue('C'.$row, $job->phone ?: ($job->client?->customer_unique_id ?? ''));
            $sheet->setCellValue('D'.$row, $job->client_address ?? '');
            $sheet->setCellValue('E'.$row, $job->zone?->name ?? '');
            $sheet->setCellValue('F'.$row, $job->scheduled_date?->format('d/m/Y') ?? '');
            $sheet->setCellValue('G'.$row, $job->scheduled_time ? Carbon::parse($job->scheduled_time)->format('h:i A') : '');
            $sheet->setCellValue('H'.$row, $job->estimated_duration_minutes !== null ? (int) $job->estimated_duration_minutes : '');
            $sheet->setCellValue('I'.$row, $job->consumed_time_minutes !== null ? (int) $job->consumed_time_minutes : '');
            $sheet->setCellValue('J'.$row, is_array($job->required_services) ? implode(', ', $job->required_services) : '');
            $sheet->setCellValue('K'.$row, $job->equipmentType?->name ?? '');
            $sheet->setCellValue('L'.$row, $job->status ?? '');
            $sheet->setCellValue('M'.$row, $job->priority ?? '');
            $sheet->setCellValue('N'.$row, $job->customer_type ?? '');
            $sheet->setCellValue('O'.$row, $job->parking_status ?? '');
            $sheet->setCellValue('P'.$row, $job->pet_warning ?? '');
            $sheet->setCellValue('Q'.$row, $job->payment_mode ?? '');
            $sheet->setCellValue('R'.$row, $job->payment_status ?? '');
            $sheet->setCellValue('S'.$row, $job->charges !== null ? (float) $job->charges : '');
            $sheet->setCellValue('T'.$row, $job->doneByUser?->name ?? '');
            $sheet->setCellValue('U'.$row, $job->assignedEmployees->pluck('name')->join(', '));
            $sheet->setCellValue('V'.$row, $job->is_recurring ? 'Yes' : 'No');
            $sheet->setCellValue('W'.$row, $job->recurrence?->name ?? '');
            $sheet->setCellValue('X'.$row, $job->site_instructions ?? '');
            $sheet->setCellValue('Y'.$row, $job->special_remarks ?? '');
            $sheet->setCellValue('Z'.$row, $job->internal_notes ?? '');
            $sheet->setCellValue('AA'.$row, $job->creator?->name ?? '');
            $sheet->setCellValue('AB'.$row, $job->created_at?->format('d/m/Y H:i') ?? '');

            $this->applyRowStyle($sheet, $row, $i % 2 === 1, ['A', 'F', 'G', 'H', 'I', 'L', 'M', 'P', 'S', 'V', 'AB']);
            $row++;
        }

        if ($this->jobs->isNotEmpty()) {
            $this->applyOutlineBorder($sheet, $row - 1);
            $sheet->getStyle('D5:D'.($row - 1))->getAlignment()->setWrapText(true);
            $sheet->getStyle('X5:Z'.($row - 1))->getAlignment()->setWrapText(true);
        }
    }
}
