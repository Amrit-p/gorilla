<?php

namespace App\Exports;

use App\Enums\ClientCustomerType;
use App\Enums\ClientPaymentStatus;
use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadWeedSpray;
use App\Exports\Concerns\HasDataValidation;
use App\Models\AccountingLevel;
use App\Models\EquipmentType;
use App\Models\JobLevel;
use App\Models\Recurrence;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClientsExport extends SpreadsheetExport
{
    use HasDataValidation;

    private const DATA_FIRST_ROW = 5;

    private const DATA_LAST_ROW = 5000;

    public const COLUMNS = [
        'A' => ['header' => '#',                   'width' => 6],
        'B' => ['header' => 'Customer ID',          'width' => 14],
        'C' => ['header' => 'Name',                 'width' => 24],
        'D' => ['header' => 'Email',                'width' => 28],
        'E' => ['header' => 'Phone',                'width' => 16],
        'F' => ['header' => 'Address',              'width' => 32],
        'G' => ['header' => 'Zone',                 'width' => 16],
        'H' => ['header' => 'Accounting Level',     'width' => 18],
        'I' => ['header' => 'Job Level',            'width' => 16],
        'J' => ['header' => 'Service Types',        'width' => 26],
        'K' => ['header' => 'Equipment Type',       'width' => 18],
        'L' => ['header' => 'Job Type',             'width' => 14],
        'M' => ['header' => 'Charges ($)',          'width' => 13],
        'N' => ['header' => 'Payment Mode',         'width' => 15],
        'O' => ['header' => 'Payment Status',       'width' => 15],
        'P' => ['header' => 'Customer Type',        'width' => 16],
        'Q' => ['header' => 'Client Type',          'width' => 13],
        'R' => ['header' => 'Weed Spray',           'width' => 12],
        'S' => ['header' => 'Recurrence',           'width' => 18],
        'T' => ['header' => 'Property Details',     'width' => 28],
        'U' => ['header' => 'Special Remarks',      'width' => 28],
        'V' => ['header' => 'Notes',                'width' => 30],
        'W' => ['header' => 'Latitude',             'width' => 14],
        'X' => ['header' => 'Longitude',            'width' => 14],
        'Y' => ['header' => 'Created At',           'width' => 18],
    ];

    public function __construct(private readonly Collection $clients) {}

    protected function getColumns(): array
    {
        return self::COLUMNS;
    }

    protected function getLastColumn(): string
    {
        return 'Y';
    }

    protected function getTitle(): string
    {
        return 'Customers Report';
    }

    protected function getSheetName(): string
    {
        return 'Customers';
    }

    protected function getRecordCount(): int
    {
        return $this->clients->count();
    }

    protected function afterBuild(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->getSheetByName($this->getSheetName());
        if ($sheet !== null) {
            $this->applyValidations($sheet);
        }
    }

    protected function renderData(Worksheet $sheet): void
    {
        $row = self::DATA_FIRST_ROW;

        foreach ($this->clients as $i => $client) {
            $sheet->setCellValue('A'.$row, $i + 1);
            $sheet->setCellValueExplicit('B'.$row, (string) ($client->customer_unique_id ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValue('C'.$row, $client->name ?? '');
            $sheet->setCellValue('D'.$row, $client->email ?? '');
            $sheet->setCellValueExplicit('E'.$row, (string) ($client->phone ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValue('F'.$row, $client->address ?? '');
            $sheet->setCellValue('G'.$row, $client->zone?->name ?? '');
            $sheet->setCellValue('H'.$row, $client->accountingLevel?->name ?? '');
            $sheet->setCellValue('I'.$row, $client->jobLevel?->name ?? '');
            $sheet->setCellValue('J'.$row, is_array($client->service_types) ? implode(', ', $client->service_types) : '');
            $sheet->setCellValue('K'.$row, $client->equipmentType?->name ?? '');
            $sheet->setCellValue('L'.$row, $client->job_type ?? '');
            $sheet->setCellValue('M'.$row, $client->total_charges !== null ? (float) $client->total_charges : '');
            $sheet->setCellValue('N'.$row, $client->payment_mode ?? '');
            $sheet->setCellValue('O'.$row, $client->payment_status ?? '');
            $sheet->setCellValue('P'.$row, $client->customer_type ?? '');
            $sheet->setCellValue('Q'.$row, $client->client_type ?? '');
            $sheet->setCellValue('R'.$row, $client->weed_spray ?? '');
            $sheet->setCellValue('S'.$row, $client->recurrence?->name ?? '');
            $sheet->setCellValue('T'.$row, $client->property_details ?? '');
            $sheet->setCellValue('U'.$row, $client->special_remarks ?? '');
            $sheet->setCellValue('V'.$row, $client->notes ?? '');
            $sheet->setCellValue('W'.$row, $client->latitude !== null ? (float) $client->latitude : '');
            $sheet->setCellValue('X'.$row, $client->longitude !== null ? (float) $client->longitude : '');
            $sheet->setCellValue('Y'.$row, $client->created_at?->format('d/m/Y H:i') ?? '');

            $sheet->getStyle('M'.$row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);

            $this->applyRowStyle($sheet, $row, $i % 2 === 1, ['A', 'B', 'L', 'M', 'Q', 'R', 'S', 'W', 'X', 'Y']);
            $row++;
        }

        if ($this->clients->isNotEmpty()) {
            $this->applyOutlineBorder($sheet, $row - 1);
            $sheet->getStyle('F5:F'.($row - 1))->getAlignment()->setWrapText(true);
        }
    }

    private function applyValidations(Worksheet $sheet): void
    {
        $first = self::DATA_FIRST_ROW;
        $last = self::DATA_LAST_ROW;

        // C: Name — optional, max 255 chars
        $this->addTextValidation($sheet, "C{$first}:C{$last}",
            min: 0, max: 255,
            errorTitle: 'Invalid Name',
            error: 'Name must not exceed 255 characters.',
        );

        // D: Email — optional, basic format check
        $this->addEmailValidation($sheet, "D{$first}:D{$last}");

        // E: Phone — optional, max 30 chars
        $this->addTextValidation($sheet, "E{$first}:E{$last}",
            min: 0, max: 30,
            errorTitle: 'Invalid Phone',
            error: 'Phone number must not exceed 30 characters.',
        );

        // G: Zone
        $zones = Zone::where('is_active', true)->orderBy('sort_order')->pluck('name')->toArray();
        $this->addDropdownValidation($sheet, "G{$first}:G{$last}",
            options: $zones,
            errorTitle: 'Invalid Zone',
            error: 'Please select a valid zone from the dropdown list.',
            prompt: 'Select a zone. Options: '.implode(', ', $zones),
        );

        // H: Accounting Level
        $accountingLevels = AccountingLevel::where('is_active', true)->orderBy('sort_order')->pluck('name')->toArray();
        if (! empty($accountingLevels)) {
            $this->addDropdownValidation($sheet, "H{$first}:H{$last}",
                options: $accountingLevels,
                errorTitle: 'Invalid Accounting Level',
                error: 'Please select a valid accounting level from the dropdown list.',
                prompt: 'Select an accounting level. Options: '.implode(', ', $accountingLevels),
            );
        }

        // I: Job Level
        $jobLevels = JobLevel::where('is_active', true)->orderBy('sort_order')->pluck('name')->toArray();
        if (! empty($jobLevels)) {
            $this->addDropdownValidation($sheet, "I{$first}:I{$last}",
                options: $jobLevels,
                errorTitle: 'Invalid Job Level',
                error: 'Please select a valid job level from the dropdown list.',
                prompt: 'Select a job level. Options: '.implode(', ', $jobLevels),
            );
        }

        // K: Equipment Type — single value
        $equipmentTypes = EquipmentType::all()->pluck('name')->toArray();
        $this->addDropdownValidation($sheet, "K{$first}:K{$last}",
            options: $equipmentTypes,
            errorTitle: 'Invalid Equipment Type',
            error: 'Please select a valid equipment type from the dropdown list.',
            prompt: 'Select an equipment type. Options: '.implode(', ', $equipmentTypes),
        );

        // L: Job Type — required
        $jobTypes = LeadJobType::values();
        $this->addDropdownValidation($sheet, "L{$first}:L{$last}",
            options: $jobTypes,
            errorTitle: 'Invalid Job Type',
            error: 'Please select a valid job type from the dropdown list.',
            prompt: 'Select a job type. Options: '.implode(', ', $jobTypes),
        );

        // M: Charges — decimal >= 0
        $this->addNumericValidation($sheet, "M{$first}:M{$last}",
            type: DataValidation::TYPE_DECIMAL,
            operator: DataValidation::OPERATOR_GREATERTHANOREQUAL,
            formula1: '0',
            errorTitle: 'Invalid Charges',
            error: 'Charges must be a number greater than or equal to 0.',
        );

        // N: Payment Mode — required
        $paymentModes = LeadPaymentMode::values();
        $this->addDropdownValidation($sheet, "N{$first}:N{$last}",
            options: $paymentModes,
            errorTitle: 'Invalid Payment Mode',
            error: 'Please select a valid payment mode from the dropdown list.',
            prompt: 'Select a payment mode. Options: '.implode(', ', $paymentModes),
        );

        // O: Payment Status
        $paymentStatuses = ClientPaymentStatus::values();
        $this->addDropdownValidation($sheet, "O{$first}:O{$last}",
            options: $paymentStatuses,
            errorTitle: 'Invalid Payment Status',
            error: 'Please select a valid payment status from the dropdown list.',
            prompt: 'Select a payment status. Options: '.implode(', ', $paymentStatuses),
        );

        // P: Customer Type — required
        $customerTypes = ClientCustomerType::values();
        $this->addDropdownValidation($sheet, "P{$first}:P{$last}",
            options: $customerTypes,
            errorTitle: 'Invalid Customer Type',
            error: 'Please select a valid customer type from the dropdown list.',
            prompt: 'Select a customer type. Options: '.implode(', ', $customerTypes),
        );

        // R: Weed Spray — required
        $weedSprayOptions = LeadWeedSpray::values();
        $this->addDropdownValidation($sheet, "R{$first}:R{$last}",
            options: $weedSprayOptions,
            errorTitle: 'Invalid Weed Spray',
            error: 'Please select a valid weed spray option from the dropdown list.',
            prompt: 'Select a weed spray option. Options: '.implode(', ', $weedSprayOptions),
        );

        // S: Recurrence
        $recurrences = Recurrence::where('is_active', true)->orderBy('sort_order')->pluck('name')->toArray();
        $this->addDropdownValidation($sheet, "S{$first}:S{$last}",
            options: $recurrences,
            errorTitle: 'Invalid Recurrence',
            error: 'Please select a valid recurrence from the dropdown list.',
            prompt: 'Select a recurrence. Options: '.implode(', ', $recurrences),
        );

        // W: Latitude — decimal between -90 and 90
        $this->addNumericValidation($sheet, "W{$first}:W{$last}",
            type: DataValidation::TYPE_DECIMAL,
            operator: DataValidation::OPERATOR_BETWEEN,
            formula1: '-90',
            formula2: '90',
            errorTitle: 'Invalid Latitude',
            error: 'Latitude must be a decimal number between -90 and 90.',
        );

        // X: Longitude — decimal between -180 and 180
        $this->addNumericValidation($sheet, "X{$first}:X{$last}",
            type: DataValidation::TYPE_DECIMAL,
            operator: DataValidation::OPERATOR_BETWEEN,
            formula1: '-180',
            formula2: '180',
            errorTitle: 'Invalid Longitude',
            error: 'Longitude must be a decimal number between -180 and 180.',
        );
    }
}
