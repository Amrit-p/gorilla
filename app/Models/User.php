<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Support\UserUniqueIdGenerator;
use App\Traits\ChecksCrmPermissions;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'user_unique_id',
    'name',
    'email',
    'phone',
    'efficiency',
    'incentive_percentage',
    'status',
    'password',
    'is_active',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use ChecksCrmPermissions, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if ($user->user_unique_id === null) {
                $user->user_unique_id = UserUniqueIdGenerator::next();
            }

            if ($user->status !== null) {
                $user->is_active = $user->status === UserStatus::ACTIVE->value;
            }
        });

        static::saving(function (User $user): void {
            if ($user->isDirty('status')) {
                $user->is_active = $user->status === UserStatus::ACTIVE->value;
            }

            if ($user->isDirty('is_active') && ! $user->isDirty('status')) {
                $user->status = UserStatus::fromActiveFlag((bool) $user->is_active)->value;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'user_unique_id' => 'integer',
            'incentive_percentage' => 'decimal:2',
        ];
    }

    public function isStatusActive(): bool
    {
        return $this->status === UserStatus::ACTIVE->value;
    }

    /** Job-wise payouts made to this mower. */
    public function mowerPayouts(): HasMany
    {
        return $this->hasMany(MowerPayout::class);
    }
}
