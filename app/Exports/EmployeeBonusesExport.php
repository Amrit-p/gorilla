<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeBonusesExport extends SpreadsheetExport
{
    private const COLUMNS_FULL = [
        'A' => ['header' => '#',           'width' => 6],
        'B' => ['header' => 'Employee',    'width' => 24],
        'C' => ['header' => 'Employee ID', 'width' => 13],
        'D' => ['header' => 'Amount ($)',  'width' => 14],
        'E' => ['header' => 'Bonus Date',  'width' => 14],
        'F' => ['header' => 'Description', 'width' => 40],
        'G' => ['header' => 'Added By',    'width' => 20],
        'H' => ['header' => 'Created At',  'width' => 16],
    ];

    private const COLUMNS_NO_ADDED_BY = [
        'A' => ['header' => '#',           'width' => 6],
        'B' => ['header' => 'Employee',    'width' => 24],
        'C' => ['header' => 'Employee ID', 'width' => 13],
        'D' => ['header' => 'Amount ($)',  'width' => 14],
        'E' => ['header' => 'Bonus Date',  'width' => 14],
        'F' => ['header' => 'Description', 'width' => 40],
        'G' => ['header' => 'Created At',  'width' => 16],
    ];

    public function __construct(
        private readonly Collection $bonuses,
        private readonly bool $hideAddedBy = false,
    ) {}

    protected function getColumns(): array     { return $this->hideAddedBy ? self::COLUMNS_NO_ADDED_BY : self::COLUMNS_FULL; }
    protected function getLastColumn(): string  { return $this->hideAddedBy ? 'G' : 'H'; }
    protected function getTitle(): string       { return 'Employee Bonuses Report'; }
    protected function getSheetName(): string   { return 'Employee Bonuses'; }
    protected function getRecordCount(): int    { return $this->bonuses->count(); }

    protected function renderData(Worksheet $sheet): void
    {
        $row = 5;

        foreach ($this->bonuses as $i => $bonus) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $bonus->employee?->name ?? '');
            $sheet->setCellValue('C' . $row, $bonus->employee?->user_unique_id ?? '');
            $sheet->setCellValue('D' . $row, (float) $bonus->amount);
            $sheet->setCellValue('E' . $row, $bonus->bonus_date?->format('d/m/Y') ?? '');
            $sheet->setCellValue('F' . $row, $bonus->description ?? '');

            if ($this->hideAddedBy) {
                $sheet->setCellValue('G' . $row, $bonus->created_at?->format('d/m/Y H:i') ?? '');
                $this->applyRowStyle($sheet, $row, $i % 2 === 1, ['A', 'C', 'D', 'E', 'G']);
            } else {
                $sheet->setCellValue('G' . $row, $bonus->creator?->name ?? '');
                $sheet->setCellValue('H' . $row, $bonus->created_at?->format('d/m/Y H:i') ?? '');
                $this->applyRowStyle($sheet, $row, $i % 2 === 1, ['A', 'C', 'D', 'E', 'H']);
            }

            $row++;
        }

        if ($this->bonuses->isNotEmpty()) {
            $this->applyOutlineBorder($sheet, $row - 1);
            $sheet->getStyle('F5:F' . ($row - 1))->getAlignment()->setWrapText(true);
        }
    }
}
