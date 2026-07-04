<?php

namespace App\Models;

use App\Enums\SalaryPeriodType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'mower_id',
    'period_type',
    'period_start',
    'period_end',
    'total_sales',
    'total_bonus',
    'percentage_used',
    'formula_snapshot',
    'salary_amount',
    'generated_by',
    'notified_at',
])]
class SalaryReceipt extends Model
{
    protected function casts(): array
    {
        return [
            'period_type' => SalaryPeriodType::class,
            'period_start' => 'date',
            'period_end' => 'date',
            'total_sales' => 'decimal:2',
            'total_bonus' => 'decimal:2',
            'percentage_used' => 'decimal:2',
            'formula_snapshot' => 'array',
            'salary_amount' => 'decimal:2',
            'notified_at' => 'datetime',
        ];
    }

    public function mower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mower_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function formulaDescription(): string
    {
        return SalaryFormula::describeCalculation(
            $this->formula_snapshot,
            (float) $this->percentage_used,
            (float) $this->total_sales,
            (float) $this->total_bonus,
            (float) $this->salary_amount
        );
    }
}
