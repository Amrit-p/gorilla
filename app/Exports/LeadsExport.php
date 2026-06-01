<?php

declare(strict_types=1);

namespace App\Exports;

use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadPaymentStatus;
use App\Enums\LeadStatus;
use App\Enums\LeadWeedSpray;
use App\Models\EquipmentType;
use App\Models\Recurrence;
use App\Models\Zone;
use App\Exports\Concerns\HasDataValidation;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeadsExport extends SpreadsheetExport
{
    use HasDataValidation;

    private const DATA_FIRST_ROW = 5;
    private const DATA_LAST_ROW  = 5000;

    public const COLUMNS = [
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

    // -------------------------------------------------------------------------
    // Post-build hook: metadata + validations
    // -------------------------------------------------------------------------

    protected function afterBuild(Spreadsheet $spreadsheet): void
    {

        $leadsSheet = $spreadsheet->getSheetByName($this->getSheetName());
        if ($leadsSheet !== null) {
            $this->applyValidations($leadsSheet);
        }
    }

    // -------------------------------------------------------------------------
    // Row rendering
    // -------------------------------------------------------------------------

    protected function renderData(Worksheet $sheet): void
    {
        $row = self::DATA_FIRST_ROW;

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
            if ($lead->lead_date !== null) {
                $sheet->setCellValue('O' . $row, SpreadsheetDate::PHPToExcel($lead->lead_date));
                $sheet->getStyle('O' . $row)->getNumberFormat()->setFormatCode('DD/MM/YYYY');
            } else {
                $sheet->setCellValue('O' . $row, '');
            }
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

    // -------------------------------------------------------------------------
    // Validation orchestration — all values sourced from their enums
    // -------------------------------------------------------------------------

    private function applyValidations(Worksheet $sheet): void
    {
        $first = self::DATA_FIRST_ROW;
        $last  = self::DATA_LAST_ROW;

        // B: Client Name — required, 1–255 chars
        $this->addTextValidation($sheet, "B{$first}:B{$last}",
            min: 1, max: 255,
            errorTitle: 'Invalid Client Name',
            error: 'Client name is required and must not exceed 255 characters.',
        );

        // C: Email — optional, basic format check
        $this->addEmailValidation($sheet, "C{$first}:C{$last}");

        // D: Mobile — optional, max 20 chars
        $this->addTextValidation($sheet, "D{$first}:D{$last}",
            min: 0, max: 20,
            errorTitle: 'Invalid Mobile',
            error: 'Mobile number must not exceed 20 characters.',
        );

        // F: Zone
        $zones = Zone::where('is_active', true)->orderBy('sort_order')->pluck('name')->toArray();
        $this->addDropdownValidation($sheet, "F{$first}:F{$last}",
            options: $zones,
            errorTitle: 'Invalid Zone',
            error: 'Please select a valid zone from the dropdown list.',
            prompt: 'Select a zone. Options: ' . implode(', ', $zones),
        );

        // H: Equipment Type — single value
        $equipmentTypes = EquipmentType::all()->pluck('name')->toArray();
        $this->addDropdownValidation($sheet, "H{$first}:H{$last}",
            options: $equipmentTypes,
            errorTitle: 'Invalid Equipment Type',
            error: 'Please select a valid equipment type from the dropdown list.',
            prompt: 'Select an equipment type. Options: ' . implode(', ', $equipmentTypes),
        );

        // I: Job Type — single value
        $jobTypes = LeadJobType::values();
        $this->addDropdownValidation($sheet, "I{$first}:I{$last}",
            options: $jobTypes,
            errorTitle: 'Invalid Job Type',
            error: 'Please select a valid job type from the dropdown list.',
            prompt: 'Select a job type. Options: ' . implode(', ', $jobTypes),
        );

        // J: Charges — decimal >= 0
        $this->addNumericValidation($sheet, "J{$first}:J{$last}",
            type: DataValidation::TYPE_DECIMAL,
            operator: DataValidation::OPERATOR_GREATERTHANOREQUAL,
            formula1: '0',
            errorTitle: 'Invalid Charges',
            error: 'Charges must be a number greater than or equal to 0.',
        );

        // K: Payment Mode
        $paymentModes = LeadPaymentMode::values();
        $this->addDropdownValidation($sheet, "K{$first}:K{$last}",
            options: $paymentModes,
            errorTitle: 'Invalid Payment Mode',
            error: 'Please select a valid payment mode from the dropdown list.',
            prompt: 'Select a payment mode. Options: ' . implode(', ', $paymentModes),
        );

        // L: Payment Status
        $paymentStatuses = LeadPaymentStatus::values();
        $this->addDropdownValidation($sheet, "L{$first}:L{$last}",
            options: $paymentStatuses,
            errorTitle: 'Invalid Payment Status',
            error: 'Please select a valid payment status from the dropdown list.',
            prompt: 'Select a payment status. Options: ' . implode(', ', $paymentStatuses),
        );

        // M: Status
        $leadStatuses = LeadStatus::values();
        $this->addDropdownValidation($sheet, "M{$first}:M{$last}",
            options: $leadStatuses,
            errorTitle: 'Invalid Status',
            error: 'Please select a value from the dropdown list.',
            prompt: 'Select a lead status. Options: ' . implode(', ', $leadStatuses),
        );

        // O: Lead Date — apply DD/MM/YYYY format to the entire range
        $sheet->getStyle("O{$first}:O{$last}")->getNumberFormat()->setFormatCode('DD/MM/YYYY');

        // O: Lead Date — between 01/01/2020 and 31/12/2050
        $this->addDateValidation($sheet, "O{$first}:O{$last}",
            from: SpreadsheetDate::PHPToExcel(new \DateTime('2020-01-01')),
            to:   SpreadsheetDate::PHPToExcel(new \DateTime('2050-12-31')),
            errorTitle: 'Invalid Lead Date',
            error: 'Lead Date must be between 01/01/2020 and 31/12/2050.',
            prompt: 'Enter a date in DD/MM/YYYY format.',
        );

        // S: Recurrence
        $recurrences = Recurrence::where('is_active', true)->orderBy('sort_order')->pluck('name')->toArray();
        $this->addDropdownValidation($sheet, "S{$first}:S{$last}",
            options: $recurrences,
            errorTitle: 'Invalid Recurrence',
            error: 'Please select a valid recurrence from the dropdown list.',
            prompt: 'Select a recurrence. Options: ' . implode(', ', $recurrences),
        );

        // R: Weed Spray
        $weedSprayOptions = LeadWeedSpray::values();
        $this->addDropdownValidation($sheet, "R{$first}:R{$last}",
            options: $weedSprayOptions,
            errorTitle: 'Invalid Weed Spray',
            error: 'Please select a valid weed spray option from the dropdown list.',
            prompt: 'Select a weed spray option. Options: ' . implode(', ', $weedSprayOptions),
        );

        // V: Latitude — decimal between -90 and 90
        $this->addNumericValidation($sheet, "V{$first}:V{$last}",
            type: DataValidation::TYPE_DECIMAL,
            operator: DataValidation::OPERATOR_BETWEEN,
            formula1: '-90',
            formula2: '90',
            errorTitle: 'Invalid Latitude',
            error: 'Latitude must be a decimal number between -90 and 90.',
        );

        // W: Longitude — decimal between -180 and 180
        $this->addNumericValidation($sheet, "W{$first}:W{$last}",
            type: DataValidation::TYPE_DECIMAL,
            operator: DataValidation::OPERATOR_BETWEEN,
            formula1: '-180',
            formula2: '180',
            errorTitle: 'Invalid Longitude',
            error: 'Longitude must be a decimal number between -180 and 180.',
        );
    }
}
