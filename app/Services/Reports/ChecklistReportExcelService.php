<?php

declare(strict_types=1);

namespace App\Services\Reports;

use Carbon\Carbon;
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
        ['key' => 'name',              'label' => 'Name',             'group' => 'green', 'format' => NumberFormat::FORMAT_TEXT,   'width' => 26, 'align' => 'left'],
        ['key' => 'total_submissions', 'label' => 'Total Submissions', 'group' => 'green', 'format' => NumberFormat::FORMAT_NUMBER, 'width' => 20, 'align' => 'center'],
        ['key' => 'days_submitted',    'label' => 'Days Submitted',   'group' => 'green', 'format' => NumberFormat::FORMAT_NUMBER, 'width' => 18, 'align' => 'center'],
    ];

    private array $columns = [];

    private array $groups = [];

    private array $subColors = [
        'green' => ['bg' => self::GREEN_SUB, 'fg' => self::GREEN_SFG],
        'blue' => ['bg' => self::BLUE_SUB,  'fg' => self::BLUE_SFG],
    ];

    private array $numericSumKeys = [];

    public function export(Collection $reportData): StreamedResponse
    {
        $spreadsheet = $this->buildSpreadsheet($reportData);
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

    private function setupColumns(Collection $reportData): void
    {
        $allDates = [];
        foreach ($reportData as $row) {
            $arr = is_array($row) ? $row : (array) $row;
            foreach (array_keys($arr['submissions_by_date'] ?? []) as $date) {
                $allDates[$date] = true;
            }
        }
        ksort($allDates);

        $this->columns = self::BASE_COLUMNS;

        foreach (array_keys($allDates) as $date) {
            $this->columns[] = [
                'key' => 'date_'.$date,
                'label' => Carbon::parse($date)->format('d M, Y'),
                'group' => 'blue',
                'format' => NumberFormat::FORMAT_NUMBER,
                'width' => 16,
                'align' => 'center',
            ];
        }

        $this->groups = [
            ['label' => 'Employee',  'span' => 3,                   'bg' => self::GREEN_BG, 'fg' => self::GREEN_FG],
            ['label' => 'Checklist Submissions', 'span' => count($allDates), 'bg' => self::BLUE_BG,  'fg' => self::BLUE_FG],
        ];

        // Remove date group if there are no date columns
        if (count($allDates) === 0) {
            array_pop($this->groups);
        }

        $this->numericSumKeys = ['total_submissions', 'days_submitted'];
        foreach (array_keys($allDates) as $date) {
            $this->numericSumKeys[] = 'date_'.$date;
        }
    }

    private function buildSpreadsheet(Collection $reportData): Spreadsheet
    {
        $this->setupColumns($reportData);

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
                    'horizontal' => $col['align'] === 'right' ? Alignment::HORIZONTAL_RIGHT : ($col['align'] === 'center' ? Alignment::HORIZONTAL_CENTER : Alignment::HORIZONTAL_LEFT),
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

        foreach ($reportData as $dto) {
            $rowData = is_array($dto) ? $dto : (array) $dto;
            $isAlt = ($excelRow % 2 === 0);

            foreach ($this->columns as $idx => $col) {
                $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
                $cell = $colLetter.$excelRow;
                $colors = $this->subColors[$col['group']];
                $bgArgb = $isAlt ? ('FF'.$this->lighten($colors['bg'])) : 'FFFFFFFF';

                if (str_starts_with($col['key'], 'date_')) {
                    $dateKey = substr($col['key'], 5);
                    $value = $rowData['submissions_by_date'][$dateKey] ?? '';
                } else {
                    $value = $rowData[$col['key']] ?? '';
                }

                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($col['format']);
                $sheet->getStyle($cell)->applyFromArray([
                    'font' => ['name' => self::FONT_NAME, 'size' => self::DATA_FONT_SIZE],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgArgb]],
                    'alignment' => [
                        'horizontal' => $col['align'] === 'right' ? Alignment::HORIZONTAL_RIGHT : ($col['align'] === 'center' ? Alignment::HORIZONTAL_CENTER : Alignment::HORIZONTAL_LEFT),
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF'.self::BORDER_COLOR]]],
                ]);
            }

            $sheet->getRowDimension($excelRow)->setRowHeight(22);
            $excelRow++;
        }

        return $excelRow - 1;
    }

    private function writeTotalsRow(Worksheet $sheet, int $lastDataRow): void
    {
        $totalsRow = $lastDataRow + 1;
        $dataStart = 4;

        foreach ($this->columns as $idx => $col) {
            $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
            $cell = $colLetter.$totalsRow;

            if ($idx === 0) {
                $sheet->setCellValue($cell, 'TOTALS');
            } elseif (in_array($col['key'], $this->numericSumKeys, true)) {
                $sheet->setCellValue($cell, "=SUM({$colLetter}{$dataStart}:{$colLetter}{$lastDataRow})");
                $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($col['format']);
            }

            $halign = $idx === 0
                ? Alignment::HORIZONTAL_LEFT
                : ($col['align'] === 'center' ? Alignment::HORIZONTAL_CENTER : Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['name' => self::FONT_NAME, 'size' => self::DATA_FONT_SIZE, 'bold' => true, 'color' => ['argb' => 'FF'.self::TOTAL_ROW_FG]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF'.self::TOTAL_ROW_BG]],
                'alignment' => ['horizontal' => $halign, 'vertical' => Alignment::VERTICAL_CENTER],
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
