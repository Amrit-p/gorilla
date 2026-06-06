<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Checklist extends Model
{
    protected $fillable = ['name', 'slug'];

    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function points(): HasMany
    {
        return $this->hasMany(ChecklistPoint::class)->orderBy('sort_order');
    }

    public static function safetyChecklist(): ?self
    {
        return static::where('slug', 'safety-checklist')->first();
    }
}
