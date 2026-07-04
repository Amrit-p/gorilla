<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'formula',
])]
class SalaryFormula extends Model
{
    private const OPERATOR_SYMBOLS = [
        '+' => '+',
        '-' => '-',
        '*' => '×',
        '/' => '÷',
        '%' => '%',
    ];

    protected function casts(): array
    {
        return [
            'formula' => 'array',
        ];
    }

    /**
     * Render this formula's AST as a human-readable expression, substituting
     * any variable found in $variables with its given display value.
     *
     * @param  array<string, string|float|int>  $variables
     */
    public function describe(array $variables = []): string
    {
        return self::describeExpression($this->formula, $variables);
    }

    /**
     * Render a full "expression = result" description for a salary calculation,
     * given the AST that produced it and the inputs/output involved.
     */
    public static function describeCalculation(
        mixed $formulaAst,
        float $percentage,
        float $totalSales,
        float $totalBonus,
        float $salaryAmount
    ): string {
        $expression = self::describeExpression($formulaAst, [
            'percentage_fraction' => number_format($percentage, 2).'%',
            'total_sales' => '$'.number_format($totalSales, 2),
            'total_bonus' => '$'.number_format($totalBonus, 2),
        ]);

        return $expression.' = $'.number_format($salaryAmount, 2);
    }

    /**
     * Recursively walk a JSON-Logic AST (as used by jwadhams/json-logic-php)
     * and render it as an infix expression, for display purposes only.
     *
     * @param  array<string, string|float|int>  $variables
     */
    public static function describeExpression(mixed $node, array $variables = []): string
    {
        if (! is_array($node)) {
            return self::formatValue($node);
        }

        if (array_key_exists('var', $node)) {
            $name = is_array($node['var']) ? ($node['var'][0] ?? '') : $node['var'];

            return array_key_exists($name, $variables)
                ? self::formatValue($variables[$name])
                : (string) $name;
        }

        $operator = array_key_first($node);
        $operands = $node[$operator];

        if (! is_array($operands) || ! array_is_list($operands)) {
            $operands = [$operands];
        }

        $rendered = array_map(
            static fn (mixed $operand): string => self::describeExpression($operand, $variables),
            $operands
        );

        $symbol = self::OPERATOR_SYMBOLS[$operator] ?? $operator;

        return '('.implode(" {$symbol} ", $rendered).')';
    }

    private static function formatValue(mixed $value): string
    {
        if (is_numeric($value)) {
            return rtrim(rtrim(sprintf('%.4f', (float) $value), '0'), '.');
        }

        return (string) $value;
    }
}
