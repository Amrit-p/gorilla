<?php

declare(strict_types=1);

namespace App\Services\Reports;

use Illuminate\Support\Collection;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Style\Alignment;
use \PhpOffice\PhpSpreadsheet\Style\Border;
use \PhpOffice\PhpSpreadsheet\Style\Fill;
use \PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MowerReportExcelService
{
    // Tailwind color equivalents used in the blade table
    private const GREEN_BG   = 'D1FAE5'; // green-100
    private const GREEN_FG   = '065F46'; // green-800
    private const GREEN_SUB  = 'ECFDF5'; // green-50
    private const GREEN_SFG  = '047857'; // green-700

    private const YELLOW_BG  = 'FEF3C7'; // yellow-100
    private const YELLOW_FG  = '92400E'; // yellow-800
    private const YELLOW_SUB = 'FFFBEB'; // yellow-50
    private const YELLOW_SFG = 'B45309'; // yellow-700

    private const BLUE_BG    = 'DBEAFE'; // blue-100
    private const BLUE_FG    = '1E40AF'; // blue-800
    private const BLUE_SUB   = 'EFF6FF'; // blue-50
    private const BLUE_SFG   = '1D4ED8'; // blue-700

    private const PINK_BG    = 'FCE7F3'; // pink-100
    private const PINK_FG    = '9D174D'; // pink-800
    private const PINK_SUB   = 'FDF2F8'; // pink-50
    private const PINK_SFG   = 'BE185D'; // pink-700

    private const BORDER_COLOR  = 'CBD5E1'; // slate-300
    private const TOTAL_ROW_BG  = '1E293B'; // slate-800
    private const TOTAL_ROW_FG  = 'FFFFFF';
    private const FONT_NAME     = 'Arial';
    private const DATA_FONT_SIZE = 10;

    /** Base columns — bonus columns are appended dynamically per export. */
    private const BASE_COLUMNS = [
        // Mower (green)
        ['key' => 'name',                 'label' => 'Name',         'group' => 'green',  'format' => NumberFormat::FORMAT_TEXT,   'width' => 26, 'align' => 'left'],
        ['key' => 'total_working_hours',  'label' => 'Total Hours',  'group' => 'green',  'format' => '#,##0.00',                  'width' => 16, 'align' => 'center'],
        ['key' => 'working_days',         'label' => 'Working Days', 'group' => 'green',  'format' => NumberFormat::FORMAT_NUMBER, 'width' => 16, 'align' => 'center'],
        // Jobs (yellow)
        ['key' => 'total_jobs_completed', 'label' => 'Completed',    'group' => 'yellow', 'format' => NumberFormat::FORMAT_NUMBER, 'width' => 14, 'align' => 'center'],
        // Earnings (blue)
        ['key' => 'completed_earnings',   'label' => 'Completed',    'group' => 'blue',   'format' => '"$"#,##0.00',               'width' => 18, 'align' => 'right'],
        // Bonus total (pink) — date columns are appended by setupDynamicColumns()
        ['key' => 'bonus',                'label' => 'Total Bonus',  'group' => 'pink',   'format' => '"$"#,##0.00',               'width' => 18, 'align' => 'right'],
    ];

    private array $columns        = [];
    private array $groups         = [];
    private bool  $hideBonusColumn = false;

    private array $subColors = [
        'green'  => ['bg' => self::GREEN_SUB,  'fg' => self::GREEN_SFG],
        'yellow' => ['bg' => self::YELLOW_SUB, 'fg' => self::YELLOW_SFG],
        'blue'   => ['bg' => self::BLUE_SUB,   'fg' => self::BLUE_SFG],
        'pink'   => ['bg' => self::PINK_SUB,   'fg' => self::PINK_SFG],
    ];

    private array $numericSumKeys = [];

    public function export(Collection $reportData, bool $hideBonusColumn = false): StreamedResponse
    {
        $this->hideBonusColumn = $hideBonusColumn;
        $spreadsheet = $this->buildSpreadsheet($reportData);
        $filename    = $this->filenameForNow() . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
            'Pragma'              => 'public',
        ]);
    }

    private function setupDynamicColumns(Collection $reportData): void
    {
        // Collect all unique bonus dates (sorted ascending)
        $allDates = [];
        foreach ($reportData as $mower) {
            $arr = is_array($mower) ? $mower : (array) $mower;
            foreach (array_keys($arr['individual_bonuses'] ?? []) as $date) {
                $allDates[$date] = true;
            }
        }
        ksort($allDates);
        $uniqueDates = array_keys($allDates);

        $this->columns = array_values(array_filter(
            self::BASE_COLUMNS,
            fn($col) => !($this->hideBonusColumn && $col['key'] === 'bonus')
        ));

        if (!$this->hideBonusColumn) {
            foreach ($uniqueDates as $date) {
                $label           = \Carbon\Carbon::parse($date)->format('d M, Y');
                $this->columns[] = [
                    'key'    => 'bonus_date_' . $date,
                    'label'  => $label,
                    'group'  => 'pink',
                    'format' => '"$"#,##0.00',
                    'width'  => 16,
                    'align'  => 'right',
                ];
            }
        }

        $this->groups = [
            ['label' => 'Mower',    'span' => 3, 'bg' => self::GREEN_BG,  'fg' => self::GREEN_FG],
            ['label' => 'Jobs',     'span' => 1, 'bg' => self::YELLOW_BG, 'fg' => self::YELLOW_FG],
            ['label' => 'Total Sale', 'span' => 1, 'bg' => self::BLUE_BG,   'fg' => self::BLUE_FG],
        ];

        if (!$this->hideBonusColumn) {
            $this->groups[] = ['label' => 'Bonus', 'span' => 1 + count($uniqueDates), 'bg' => self::PINK_BG, 'fg' => self::PINK_FG];
        }

        $this->numericSumKeys = [
            'total_working_hours',
            'working_days',
            'total_jobs_completed',
            'completed_earnings',
        ];

        if (!$this->hideBonusColumn) {
            $this->numericSumKeys[] = 'bonus';
            foreach ($uniqueDates as $date) {
                $this->numericSumKeys[] = 'bonus_date_' . $date;
            }
        }
    }

    private function buildSpreadsheet(Collection $reportData): Spreadsheet
    {
        $this->setupDynamicColumns($reportData);

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Mower Report');
        $spreadsheet->getProperties()->setTitle('Mower Report')->setCreator('Mower Management System');

        $this->writeTitle($sheet);        // row 1
        $this->writeGroupHeaders($sheet); // row 2
        $this->writeSubHeaders($sheet);   // row 3
        $lastDataRow = $this->writeData($sheet, $reportData); // rows 4+

        if ($lastDataRow >= 4) {
            $this->writeTotalsRow($sheet, $lastDataRow);
        }

        $this->applyColumnWidths($sheet);
        $sheet->freezePane('A4');

        return $spreadsheet;
    }

    private function writeTitle(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->columns));
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'Mower Performance Report — ' . date('d M Y'));
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['name' => self::FONT_NAME, 'size' => 14, 'bold' => true, 'color' => ['argb' => 'FF1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
            'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF' . self::BORDER_COLOR]]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);
    }

    private function writeGroupHeaders(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        $col = 1;
        foreach ($this->groups as $group) {
            $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $endCol   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + $group['span'] - 1);
            $range    = "{$startCol}2:{$endCol}2";

            $sheet->mergeCells($range);
            $sheet->setCellValue("{$startCol}2", $group['label']);
            $sheet->getStyle($range)->applyFromArray([
                'font'      => ['name' => self::FONT_NAME, 'size' => 11, 'bold' => true, 'color' => ['argb' => 'FF' . $group['fg']]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $group['bg']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF' . self::BORDER_COLOR]],
                ],
            ]);

            $col += $group['span'];
        }
        $sheet->getRowDimension(2)->setRowHeight(26);
    }

    private function writeSubHeaders(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        foreach ($this->columns as $idx => $col) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
            $cell      = $colLetter . '3';
            $colors    = $this->subColors[$col['group']];

            $sheet->setCellValue($cell, $col['label']);
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['name' => self::FONT_NAME, 'size' => 10, 'bold' => true, 'color' => ['argb' => 'FF' . $colors['fg']]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $colors['bg']]],
                'alignment' => [
                    'horizontal' => $col['align'] === 'right' ? Alignment::HORIZONTAL_RIGHT : ($col['align'] === 'center' ? Alignment::HORIZONTAL_CENTER : Alignment::HORIZONTAL_LEFT),
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true,
                ],
                'borders'   => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF' . self::BORDER_COLOR]],
                ],
            ]);
        }
        $sheet->getRowDimension(3)->setRowHeight(28);
    }

    private function writeData(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        Collection $reportData
    ): int {
        $excelRow = 4;

        foreach ($reportData as $dto) {
            $rowData = $dto instanceof \App\DTOS\Response\Reports\MowerResponseReportDTO
                ? $dto->toArray()
                : (array) $dto;

            $isAlt  = ($excelRow % 2 === 0);

            foreach ($this->columns as $idx => $col) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
                $cell      = $colLetter . $excelRow;
                $colors    = $this->subColors[$col['group']];

                // Alternate rows: use a very light tint of the group color; odd rows white
                $bgArgb = $isAlt ? ('FF' . $this->lighten($colors['bg'])) : 'FFFFFFFF';

                if (isset($col['static'])) {
                    $value = $col['static'];
                } elseif (str_starts_with($col['key'], 'bonus_date_')) {
                    $dateKey = substr($col['key'], 11);
                    $individualBonuses = $rowData['individual_bonuses'] ?? [];
                    $value = isset($individualBonuses[$dateKey]) ? (float) $individualBonuses[$dateKey] : '';
                } else {
                    $value = $rowData[$col['key']] ?? '';
                }
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($col['format']);
                $sheet->getStyle($cell)->applyFromArray([
                    'font'      => ['name' => self::FONT_NAME, 'size' => self::DATA_FONT_SIZE],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgArgb]],
                    'alignment' => [
                        'horizontal' => $col['align'] === 'right' ? Alignment::HORIZONTAL_RIGHT : ($col['align'] === 'center' ? Alignment::HORIZONTAL_CENTER : Alignment::HORIZONTAL_LEFT),
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'borders'   => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF' . self::BORDER_COLOR]],
                    ],
                ]);
            }

            $sheet->getRowDimension($excelRow)->setRowHeight(22);
            $excelRow++;
        }

        return $excelRow - 1;
    }

    private function writeTotalsRow(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        int $lastDataRow
    ): void {
        $totalsRow = $lastDataRow + 1;
        $dataStart = 4;

        foreach ($this->columns as $idx => $col) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
            $cell      = $colLetter . $totalsRow;

            if ($idx === 0) {
                $sheet->setCellValue($cell, 'TOTALS');
            } elseif (isset($col['static'])) {
                $sheet->setCellValue($cell, $col['static']);
            } elseif (in_array($col['key'], $this->numericSumKeys, true)) {
                $sheet->setCellValue($cell, "=SUM({$colLetter}{$dataStart}:{$colLetter}{$lastDataRow})");
                $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($col['format']);
            }

            $halign = $idx === 0
                ? Alignment::HORIZONTAL_LEFT
                : ($col['align'] === 'center' ? Alignment::HORIZONTAL_CENTER : Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['name' => self::FONT_NAME, 'size' => self::DATA_FONT_SIZE, 'bold' => true, 'color' => ['argb' => 'FF' . self::TOTAL_ROW_FG]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . self::TOTAL_ROW_BG]],
                'alignment' => ['horizontal' => $halign, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => [
                    'allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF0F172A']],
                ],
            ]);
        }

        $sheet->getRowDimension($totalsRow)->setRowHeight(24);
    }

    private function applyColumnWidths(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        foreach ($this->columns as $idx => $col) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
            $sheet->getColumnDimension($colLetter)->setWidth($col['width']);
        }
    }

    /** Blend a hex color with white at 50% to produce a subtle alt-row tint. */
    private function lighten(string $hex): string
    {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = (int) round(($r + 255) / 2);
        $g = (int) round(($g + 255) / 2);
        $b = (int) round(($b + 255) / 2);

        return sprintf('%02X%02X%02X', $r, $g, $b);
    }

    private function filenameForNow(): string
    {
        $d    = new \DateTime();
        $hour = (int) $d->format('g');
        $ampm = $d->format('A');

        return sprintf(
            'mower_report_%s_%s_%s_%s-%s_%s',
            $d->format('Y'),
            $d->format('m'),
            $d->format('d'),
            str_pad((string) $hour, 2, '0', STR_PAD_LEFT),
            $d->format('i'),
            $ampm
        );
    }
}
