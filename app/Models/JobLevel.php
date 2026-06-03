<?php

namespace App\Models;

use App\Models\Concerns\IsMasterCatalogRecord;
use Illuminate\Database\Eloquent\Model;

class JobLevel extends Model
{
    use IsMasterCatalogRecord;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
