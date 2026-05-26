<?php

namespace App\Models;

use App\Models\Concerns\IsMasterCatalogRecord;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use IsMasterCatalogRecord;

    protected $fillable = [
        'name',
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
