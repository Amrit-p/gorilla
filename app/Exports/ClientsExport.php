<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClientsExport extends SpreadsheetExport
{
    private const COLUMNS = [
        'A' => ['header' => '#',                   'width' => 6],
        'B' => ['header' => 'Customer ID',          'width' => 14],
        'C' => ['header' => 'Name',                 'width' => 24],
        'D' => ['header' => 'Email',                'width' => 28],
        'E' => ['header' => 'Phone',                'width' => 16],
        'F' => ['header' => 'Address',              'width' => 32],
        'G' => ['header' => 'Zone',                 'width' => 16],
        'H' => ['header' => 'Accounting Level',     'width' => 18],
        'I' => ['header' => 'Service Types',        'width' => 26],
        'J' => ['header' => 'Equipment Type',       'width' => 18],
        'K' => ['header' => 'Job Type',             'width' => 14],
        'L' => ['header' => 'Charges ($)',          'width' => 13],
        'M' => ['header' => 'Payment Mode',         'width' => 15],
        'N' => ['header' => 'Payment Status',       'width' => 15],
        'O' => ['header' => 'Customer Type',        'width' => 16],
        'P' => ['header' => 'Client Type',          'width' => 13],
        'Q' => ['header' => 'Weed Spray',           'width' => 12],
        'R' => ['header' => 'Recurrence',           'width' => 18],
        'S' => ['header' => 'Property Details',     'width' => 28],
        'T' => ['header' => 'Special Remarks',      'width' => 28],
        'U' => ['header' => 'Notes',                'width' => 30],
        'V' => ['header' => 'Latitude',             'width' => 14],
        'W' => ['header' => 'Longitude',            'width' => 14],
        'X' => ['header' => 'Created At',           'width' => 18],
    ];

    public function __construct(private readonly Collection $clients) {}

    protected function getColumns(): array    { return self::COLUMNS; }
    protected function getLastColumn(): string { return 'X'; }
    protected function getTitle(): string      { return 'Customers Report'; }
    protected function getSheetName(): string  { return 'Customers'; }
    protected function getRecordCount(): int   { return $this->clients->count(); }

    protected function renderData(Worksheet $sheet): void
    {
        $row = 5;

        foreach ($this->clients as $i => $client) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValueExplicit('B' . $row, (string) ($client->customer_unique_id ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $client->name ?? '');
            $sheet->setCellValue('D' . $row, $client->email ?? '');
            $sheet->setCellValueExplicit('E' . $row, (string) ($client->phone ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValue('F' . $row, $client->address ?? '');
            $sheet->setCellValue('G' . $row, $client->zone?->name ?? '');
            $sheet->setCellValue('H' . $row, $client->accountingLevel?->name ?? '');
            $sheet->setCellValue('I' . $row, is_array($client->service_types) ? implode(', ', $client->service_types) : '');
            $sheet->setCellValue('J' . $row, $client->equipmentType?->name ?? '');
            $sheet->setCellValue('K' . $row, $client->job_type ?? '');
            $sheet->setCellValue('L' . $row, $client->total_charges !== null ? (float) $client->total_charges : '');
            $sheet->setCellValue('M' . $row, $client->payment_mode ?? '');
            $sheet->setCellValue('N' . $row, $client->payment_status ?? '');
            $sheet->setCellValue('O' . $row, $client->customer_type ?? '');
            $sheet->setCellValue('P' . $row, $client->client_type ?? '');
            $sheet->setCellValue('Q' . $row, $client->weed_spray ?? '');
            $sheet->setCellValue('R' . $row, $client->recurrence?->name ?? '');
            $sheet->setCellValue('S' . $row, $client->property_details ?? '');
            $sheet->setCellValue('T' . $row, $client->special_remarks ?? '');
            $sheet->setCellValue('U' . $row, $client->notes ?? '');
            $sheet->setCellValue('V' . $row, $client->latitude !== null ? (float) $client->latitude : '');
            $sheet->setCellValue('W' . $row, $client->longitude !== null ? (float) $client->longitude : '');
            $sheet->setCellValue('X' . $row, $client->created_at?->format('d/m/Y H:i') ?? '');

            $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);

            $this->applyRowStyle($sheet, $row, $i % 2 === 1, ['A', 'B', 'K', 'L', 'Q', 'R', 'V', 'W', 'X']);
            $row++;
        }

        if ($this->clients->isNotEmpty()) {
            $this->applyOutlineBorder($sheet, $row - 1);
            $sheet->getStyle('F5:F' . ($row - 1))->getAlignment()->setWrapText(true);
        }
    }
}
