<?php

namespace App\Enums;

enum JobImageKind: string
{
    case BEFORE = 'before';
    case AFTER = 'after';

    public function jobAttribute(): string
    {
        return match ($this) {
            self::BEFORE => 'before_images',
            self::AFTER => 'after_images',
        };
    }

    public function directorySegment(): string
    {
        return $this->value;
    }
}
