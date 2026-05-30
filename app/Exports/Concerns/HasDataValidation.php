<?php

namespace App\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Reusable PhpSpreadsheet data-validation helpers.
 *
 * Mix into any SpreadsheetExport subclass to add structured validation
 * rules without duplicating boilerplate.
 */
trait HasDataValidation
{
    /**
     * Dropdown (LIST) validation.
     *
     * setShowDropDown(false) = show the arrow in Excel (OOXML attribute name is inverted).
     * When $prompt is provided the input message shows the prompt plus the available options.
     *
     * @param string[]                $options
     * @param DataValidation::STYLE_* $errorStyle  Use STYLE_WARNING for multi-value columns.
     */
    protected function addDropdownValidation(
        Worksheet $sheet,
        string $range,
        array $options,
        string $errorTitle,
        string $error,
        string $prompt = '',
        string $errorStyle = DataValidation::STYLE_STOP,
    ): void {
        $dv = $this->getDv($sheet, $range);
        $dv->setType(DataValidation::TYPE_LIST)
            ->setErrorStyle($errorStyle)
            ->setAllowBlank(true)
            ->setShowDropDown(false)
            ->setShowErrorMessage(true)
            ->setErrorTitle($errorTitle)
            ->setError($error)
            ->setFormula1('"' . implode(',', $options) . '"');

        if ($prompt !== '') {
            $dv->setShowInputMessage(true)
                ->setPromptTitle('Hint')
                ->setPrompt($prompt);
        }
    }

    /**
     * Numeric validation (TYPE_DECIMAL or TYPE_WHOLE).
     * Provide formula2 when operator is BETWEEN / NOT_BETWEEN.
     */
    protected function addNumericValidation(
        Worksheet $sheet,
        string $range,
        string $type,
        string $operator,
        string $formula1,
        string $formula2 = '',
        string $errorTitle = 'Invalid Value',
        string $error = 'Please enter a valid number.',
        bool $allowBlank = true,
    ): void {
        $dv = $this->getDv($sheet, $range);
        $dv->setType($type)
            ->setErrorStyle(DataValidation::STYLE_STOP)
            ->setAllowBlank($allowBlank)
            ->setShowErrorMessage(true)
            ->setErrorTitle($errorTitle)
            ->setError($error)
            ->setOperator($operator)
            ->setFormula1($formula1);

        if ($formula2 !== '') {
            $dv->setFormula2($formula2);
        }
    }

    /**
     * Date validation using Excel serial-number boundaries.
     * Convert PHP dates via SpreadsheetDate::PHPToExcel(new \DateTime('YYYY-MM-DD')).
     */
    protected function addDateValidation(
        Worksheet $sheet,
        string $range,
        float $from,
        float $to,
        string $errorTitle,
        string $error,
        string $prompt = '',
    ): void {
        $dv = $this->getDv($sheet, $range);
        $dv->setType(DataValidation::TYPE_DATE)
            ->setErrorStyle(DataValidation::STYLE_STOP)
            ->setAllowBlank(true)
            ->setShowErrorMessage(true)
            ->setErrorTitle($errorTitle)
            ->setError($error)
            ->setOperator(DataValidation::OPERATOR_BETWEEN)
            ->setFormula1((string) $from)
            ->setFormula2((string) $to);

        if ($prompt !== '') {
            $dv->setShowInputMessage(true)
                ->setPromptTitle('Date Format')
                ->setPrompt($prompt);
        }
    }

    /**
     * Text-length validation.
     * min > 0 → BETWEEN min…max  (required field, rejects blank).
     * min = 0 → LESSTHANOREQUAL max (optional field, blank allowed).
     */
    protected function addTextValidation(
        Worksheet $sheet,
        string $range,
        int $min,
        int $max,
        string $errorTitle,
        string $error,
    ): void {
        $dv = $this->getDv($sheet, $range);
        $dv->setType(DataValidation::TYPE_TEXTLENGTH)
            ->setErrorStyle(DataValidation::STYLE_STOP)
            ->setAllowBlank($min === 0)
            ->setShowErrorMessage(true)
            ->setErrorTitle($errorTitle)
            ->setError($error);

        if ($min > 0) {
            $dv->setOperator(DataValidation::OPERATOR_BETWEEN)
                ->setFormula1((string) $min)
                ->setFormula2((string) $max);
        } else {
            $dv->setOperator(DataValidation::OPERATOR_LESSTHANOREQUAL)
                ->setFormula1((string) $max);
        }
    }

    /**
     * Custom-formula e-mail validation.
     * Blank is allowed; non-blank must contain "@" with a "." after it.
     * Excel adjusts the relative cell reference across the full range.
     */
    protected function addEmailValidation(Worksheet $sheet, string $range): void
    {
        [$c] = explode(':', $range); // e.g. "C5"

        $this->getDv($sheet, $range)
            ->setType(DataValidation::TYPE_CUSTOM)
            ->setErrorStyle(DataValidation::STYLE_STOP)
            ->setAllowBlank(true)
            ->setShowErrorMessage(true)
            ->setErrorTitle('Invalid Email')
            ->setError('Please enter a valid email address (e.g. user@example.com).')
            ->setFormula1("=OR({$c}=\"\",AND(LEN({$c})>4,ISNUMBER(FIND(\"@\",{$c})),ISNUMBER(FIND(\".\",MID({$c},FIND(\"@\",{$c}),LEN({$c}))))))")
            ->setShowInputMessage(true)
            ->setPromptTitle('Email')
            ->setPrompt('Enter a valid email address.');
    }

    /**
     * Fetch (or create) the DataValidation for the top-left cell of $range
     * and immediately bind it to the full range via setSqref.
     * One object covers the whole range — no per-row duplication.
     */
    private function getDv(Worksheet $sheet, string $range): DataValidation
    {
        [$startCell] = explode(':', $range);

        return $sheet->getCell($startCell)->getDataValidation()->setSqref($range);
    }
}
