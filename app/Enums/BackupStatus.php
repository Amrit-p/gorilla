<?php

namespace App\Enums;

enum BackupStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Running => 'Running',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-slate-100 text-slate-600',
            self::Running => 'bg-blue-100 text-blue-700',
            self::Completed => 'bg-emerald-100 text-emerald-700',
            self::Failed => 'bg-red-100 text-red-700',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
