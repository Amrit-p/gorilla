<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeadsExport extends SpreadsheetExport
{
    private const COLUMNS = [
        'A' => ['header' => '#',                 'width' => 6],
        'B' => ['header' => 'Client Name',        'width' => 24],
        'C' => ['header' => 'Email',              'width' => 28],
        'D' => ['header' => 'Mobile',             'width' => 16],
        'E' => ['header' => 'Address',            'width' => 32],
        'F' => ['header' => 'Zone',               'width' => 16],
        'G' => ['header' => 'Service Types',      'width' => 26],
        'H' => ['header' => 'Equipment Type',     'width' => 18],
        'I' => ['header' => 'Job Type',           'width' => 14],
        'J' => ['header' => 'Charges ($)',        'width' => 13],
        'K' => ['header' => 'Payment Mode',       'width' => 15],
        'L' => ['header' => 'Payment Status',     'width' => 15],
        'M' => ['header' => 'Status',             'width' => 14],
        'N' => ['header' => 'Assigned To',        'width' => 20],
        'O' => ['header' => 'Lead Date',          'width' => 13],
        'P' => ['header' => 'Lead Time',          'width' => 11],
        'Q' => ['header' => 'Converted At',       'width' => 18],
        'R' => ['header' => 'Weed Spray',         'width' => 12],
        'S' => ['header' => 'Recurrence',          'width' => 18],
        'T' => ['header' => 'Remarks',            'width' => 30],
        'U' => ['header' => 'Property Details',   'width' => 28],
        'V' => ['header' => 'Latitude',           'width' => 14],
        'W' => ['header' => 'Longitude',          'width' => 14],
        'X' => ['header' => 'Created At',         'width' => 18],
    ];

    public function __construct(private readonly Collection $leads) {}

    protected function getColumns(): array    { return self::COLUMNS; }
    protected function getLastColumn(): string { return 'X'; }
    protected function getTitle(): string      { return 'Leads Report'; }
    protected function getSheetName(): string  { return 'Leads'; }
    protected function getRecordCount(): int   { return $this->leads->count(); }

    protected function renderData(Worksheet $sheet): void
    {
        $row = 5;

        foreach ($this->leads as $i => $lead) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $lead->client_name ?? '');
            $sheet->setCellValue('C' . $row, $lead->email ?? '');
            $sheet->setCellValueExplicit('D' . $row, (string) ($lead->mobile_number ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $row, $lead->address ?? '');
            $sheet->setCellValue('F' . $row, $lead->zone?->name ?? '');
            $sheet->setCellValue('G' . $row, is_array($lead->service_types) ? implode(', ', $lead->service_types) : '');
            $sheet->setCellValue('H' . $row, $lead->equipmentType?->name ?? '');
            $sheet->setCellValue('I' . $row, $lead->job_type ?? '');
            $sheet->setCellValue('J' . $row, $lead->charges !== null ? (float) $lead->charges : '');
            $sheet->setCellValue('K' . $row, $lead->payment_mode ?? '');
            $sheet->setCellValue('L' . $row, $lead->payment_status ?? '');
            $sheet->setCellValue('M' . $row, $lead->status ?? '');
            $sheet->setCellValue('N' . $row, $lead->assignedSalesUser?->name ?? 'Unassigned');
            $sheet->setCellValue('O' . $row, $lead->lead_date?->format('d/m/Y') ?? '');
            $sheet->setCellValue('P' . $row, $lead->lead_time ?? '');
            $sheet->setCellValue('Q' . $row, $lead->converted_at?->format('d/m/Y H:i') ?? '');
            $sheet->setCellValue('R' . $row, $lead->weed_spray ?? '');
            $sheet->setCellValue('S' . $row, $lead->recurrence?->name ?? '');
            $sheet->setCellValue('T' . $row, $lead->remarks ?? '');
            $sheet->setCellValue('U' . $row, $lead->property_details ?? '');
            $sheet->setCellValue('V' . $row, $lead->latitude !== null ? (float) $lead->latitude : '');
            $sheet->setCellValue('W' . $row, $lead->longitude !== null ? (float) $lead->longitude : '');
            $sheet->setCellValue('X' . $row, $lead->created_at?->format('d/m/Y H:i') ?? '');

            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);

            $this->applyRowStyle($sheet, $row, $i % 2 === 1, ['A', 'J', 'O', 'P', 'Q', 'R', 'V', 'W', 'X']);
            $row++;
        }

        if ($this->leads->isNotEmpty()) {
            $this->applyOutlineBorder($sheet, $row - 1);
            $sheet->getStyle('E5:E' . ($row - 1))->getAlignment()->setWrapText(true);
        }
    }
}
