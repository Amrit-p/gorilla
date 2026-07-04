<?php

namespace Tests\Unit;

use App\Models\SalaryFormula;
use App\Services\SalaryCalculatorService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SalaryFormulaTest extends TestCase
{
    #[Test]
    public function it_describes_the_default_formula_expression_with_given_variables(): void
    {
        $expression = SalaryFormula::describeExpression(SalaryCalculatorService::defaultFormulaRule(), [
            'percentage_fraction' => '20.00%',
            'total_sales' => '$1,000.00',
            'total_bonus' => '$50.00',
        ]);

        $this->assertSame('((20.00% × $1,000.00) + $50.00)', $expression);
    }

    #[Test]
    public function it_falls_back_to_the_variable_name_when_no_value_is_supplied(): void
    {
        $expression = SalaryFormula::describeExpression(SalaryCalculatorService::defaultFormulaRule());

        $this->assertSame('((percentage_fraction × total_sales) + total_bonus)', $expression);
    }

    #[Test]
    public function it_maps_operators_to_readable_symbols(): void
    {
        $ast = ['-' => [['var' => 'a'], ['/' => [['var' => 'b'], ['var' => 'c']]]]];

        $expression = SalaryFormula::describeExpression($ast, ['a' => 10, 'b' => 20, 'c' => 4]);

        $this->assertSame('(10 - (20 ÷ 4))', $expression);
    }

    #[Test]
    public function it_appends_the_computed_result_to_the_expression(): void
    {
        $description = SalaryFormula::describeCalculation(
            SalaryCalculatorService::defaultFormulaRule(),
            percentage: 20.0,
            totalSales: 1000.0,
            totalBonus: 50.0,
            salaryAmount: 250.0
        );

        $this->assertSame('((20.00% × $1,000.00) + $50.00) = $250.00', $description);
    }
}
