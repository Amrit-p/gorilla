<?php

declare(strict_types=1);

namespace App\Services\Reports;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChecklistReportExcelService
{
    private const GREEN_BG = 'D1FAE5';

    private const GREEN_FG = '065F46';

    private const GREEN_SUB = 'ECFDF5';

    private const GREEN_SFG = '047857';

    private const BLUE_BG = 'DBEAFE';

    private const BLUE_FG = '1E40AF';

    private const BLUE_SUB = 'EFF6FF';

    private const BLUE_SFG = '1D4ED8';

    private const BORDER_COLOR = 'CBD5E1';

    private const TOTAL_ROW_BG = '1E293B';

    private const TOTAL_ROW_FG = 'FFFFFF';

    private const FONT_NAME = 'Arial';

    private const DATA_FONT_SIZE = 10;

    private const BASE_COLUMNS = [
        ['key' => 'name',           'label' => 'Mower',     'group' => 'green', 'format' => NumberFormat::FORMAT_TEXT, 'width' => 26, 'align' => 'left'],
        ['key' => 'checklist_name', 'label' => 'Checklist', 'group' => 'green', 'format' => NumberFormat::FORMAT_TEXT, 'width' => 30, 'align' => 'left'],
    ];

    private array $columns = [];

    private array $groups = [];

    private Collection $reportDataStore;

    private array $subColors = [
        'green' => ['bg' => self::GREEN_SUB, 'fg' => self::GREEN_SFG],
        'blue' => ['bg' => self::BLUE_SUB,  'fg' => self::BLUE_SFG],
    ];

    public function export(Collection $reportData, ?Carbon $startDate = null, ?Carbon $endDate = null): StreamedResponse
    {
        $spreadsheet = $this->buildSpreadsheet($reportData, $startDate, $endDate);
        $filename = 'checklist_report_'.date('Y_m_d_H_i').'.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
            'Pragma' => 'public',
        ]);
    }

    private function setupColumns(Collection $reportData, ?Carbon $startDate = null, ?Carbon $endDate = null): void
    {
        $allDates = [];

        if ($startDate && $endDate) {
            foreach (CarbonPeriod::create($startDate, $endDate) as $day) {
                $allDates[$day->toDateString()] = true;
            }
        } else {
            foreach ($reportData as $row) {
                $arr = is_array($row) ? $row : (array) $row;
                foreach (array_keys($arr['submissions_by_date'] ?? []) as $date) {
                    $allDates[$date] = true;
                }
            }
            ksort($allDates);
        }

        $this->columns = self::BASE_COLUMNS;

        foreach (array_keys($allDates) as $date) {
            $this->columns[] = [
                'key' => 'date_'.$date,
                'label' => Carbon::parse($date)->format('d M'),
                'group' => 'blue',
                'format' => NumberFormat::FORMAT_TEXT,
                'width' => 12,
                'align' => 'center',
            ];
        }

        $this->groups = [
            ['label' => 'Employee', 'span' => 2, 'bg' => self::GREEN_BG, 'fg' => self::GREEN_FG],
        ];

        if (count($allDates) > 0) {
            $this->groups[] = ['label' => 'Submission Dates', 'span' => count($allDates), 'bg' => self::BLUE_BG, 'fg' => self::BLUE_FG];
        }
    }

    private function buildSpreadsheet(Collection $reportData, ?Carbon $startDate = null, ?Carbon $endDate = null): Spreadsheet
    {
        $this->setupColumns($reportData, $startDate, $endDate);
        $this->reportDataStore = $reportData;

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Checklist Report');
        $spreadsheet->getProperties()->setTitle('Checklist Submission Report')->setCreator('CRM');

        $this->writeTitle($sheet);
        $this->writeGroupHeaders($sheet);
        $this->writeSubHeaders($sheet);
        $lastDataRow = $this->writeData($sheet, $reportData);

        if ($lastDataRow >= 4) {
            $this->writeTotalsRow($sheet, $lastDataRow);
        }

        $this->applyColumnWidths($sheet);
        $sheet->freezePane('A4');

        return $spreadsheet;
    }

    private function writeTitle(Worksheet $sheet): void
    {
        $lastCol = Coordinate::stringFromColumnIndex(count($this->columns));
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'Checklist Submission Report — '.date('d M Y'));
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['name' => self::FONT_NAME, 'size' => 14, 'bold' => true, 'color' => ['argb' => 'FF1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF'.self::BORDER_COLOR]]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);
    }

    private function writeGroupHeaders(Worksheet $sheet): void
    {
        $col = 1;
        foreach ($this->groups as $group) {
            $startCol = Coordinate::stringFromColumnIndex($col);
            $endCol = Coordinate::stringFromColumnIndex($col + $group['span'] - 1);
            $range = "{$startCol}2:{$endCol}2";

            $sheet->mergeCells($range);
            $sheet->setCellValue("{$startCol}2", $group['label']);
            $sheet->getStyle($range)->applyFromArray([
                'font' => ['name' => self::FONT_NAME, 'size' => 11, 'bold' => true, 'color' => ['argb' => 'FF'.$group['fg']]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF'.$group['bg']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF'.self::BORDER_COLOR]]],
            ]);

            $col += $group['span'];
        }
        $sheet->getRowDimension(2)->setRowHeight(26);
    }

    private function writeSubHeaders(Worksheet $sheet): void
    {
        foreach ($this->columns as $idx => $col) {
            $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
            $cell = $colLetter.'3';
            $colors = $this->subColors[$col['group']];

            $sheet->setCellValue($cell, $col['label']);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['name' => self::FONT_NAME, 'size' => 10, 'bold' => true, 'color' => ['argb' => 'FF'.$colors['fg']]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF'.$colors['bg']]],
                'alignment' => [
                    'horizontal' => $col['align'] === 'center' ? Alignment::HORIZONTAL_CENTER : Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF'.self::BORDER_COLOR]]],
            ]);
        }
        $sheet->getRowDimension(3)->setRowHeight(28);
    }

    private function writeData(Worksheet $sheet, Collection $reportData): int
    {
        $excelRow = 4;
        $grouped = $reportData->groupBy(fn ($r) => is_array($r) ? $r['user_id'] : $r->user_id);
        $colorIndex = 0;
        $mergeRanges = [];

        foreach ($grouped as $userRows) {
            $rowCount = $userRows->count();
            $isAlt = ($colorIndex % 2 !== 0);
            $startRow = $excelRow;
            $colorIndex++;

            foreach ($userRows->values() as $subIndex => $dto) {
                $rowData = is_array($dto) ? $dto : (array) $dto;

                foreach ($this->columns as $idx => $col) {
                    $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
                    $cell = $colLetter.$excelRow;
                    $colors = $this->subColors[$col['group']];
                    $bgArgb = $isAlt ? ('FF'.$this->lighten($colors['bg'])) : 'FFFFFFFF';

                    if ($idx === 0) {
                        $value = $subIndex === 0 ? ($rowData['name'] ?? '') : '';
                    } elseif ($col['key'] === 'checklist_name') {
                        $value = $rowData['checklist_name'] ?? '';
                    } elseif (str_starts_with($col['key'], 'date_')) {
                        $dateKey = substr($col['key'], 5);
                        $value = ($rowData['submissions_by_date'][$dateKey] ?? false) ? '✓' : '';
                    } else {
                        $value = $rowData[$col['key']] ?? '';
                    }

                    $sheet->setCellValue($cell, $value);
                    $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($col['format']);
                    $sheet->getStyle($cell)->applyFromArray([
                        'font' => ['name' => self::FONT_NAME, 'size' => self::DATA_FONT_SIZE],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgArgb]],
                        'alignment' => [
                            'horizontal' => $col['align'] === 'center' ? Alignment::HORIZONTAL_CENTER : Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF'.self::BORDER_COLOR]]],
                    ]);
                }

                $sheet->getRowDimension($excelRow)->setRowHeight(22);
                $excelRow++;
            }

            if ($rowCount > 1) {
                $mergeRanges[] = 'A'.$startRow.':A'.($excelRow - 1);
            }
        }

        foreach ($mergeRanges as $range) {
            $sheet->mergeCells($range);
            $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }

        return $excelRow - 1;
    }

    private function writeTotalsRow(Worksheet $sheet, int $lastDataRow): void
    {
        $totalsRow = $lastDataRow + 1;

        foreach ($this->columns as $idx => $col) {
            $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
            $cell = $colLetter.$totalsRow;

            if ($idx === 0) {
                $sheet->setCellValue($cell, 'TOTALS');
            } elseif (str_starts_with($col['key'], 'date_')) {
                $dateKey = substr($col['key'], 5);
                $count = $this->reportDataStore->filter(
                    fn ($r) => (is_array($r) ? $r : (array) $r)['submissions_by_date'][$dateKey] ?? false
                )->count();

                if ($count > 0) {
                    $sheet->setCellValue($cell, $count);
                    $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
                }
            }

            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['name' => self::FONT_NAME, 'size' => self::DATA_FONT_SIZE, 'bold' => true, 'color' => ['argb' => 'FF'.self::TOTAL_ROW_FG]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF'.self::TOTAL_ROW_BG]],
                'alignment' => [
                    'horizontal' => $idx === 0 ? Alignment::HORIZONTAL_LEFT : Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF0F172A']]],
            ]);
        }

        $sheet->getRowDimension($totalsRow)->setRowHeight(24);
    }

    private function applyColumnWidths(Worksheet $sheet): void
    {
        foreach ($this->columns as $idx => $col) {
            $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
            $sheet->getColumnDimension($colLetter)->setWidth($col['width']);
        }
    }

    private function lighten(string $hex): string
    {
        $r = (int) round((hexdec(substr($hex, 0, 2)) + 255) / 2);
        $g = (int) round((hexdec(substr($hex, 2, 2)) + 255) / 2);
        $b = (int) round((hexdec(substr($hex, 4, 2)) + 255) / 2);

        return sprintf('%02X%02X%02X', $r, $g, $b);
    }
}
