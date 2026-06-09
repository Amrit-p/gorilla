<?php

namespace App\Models;

use App\Traits\HasFollowups;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contractor extends Model
{
    use HasFactory;
    use HasFollowups;

    protected $fillable = [
        'name',
        'phone',
        'email',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
