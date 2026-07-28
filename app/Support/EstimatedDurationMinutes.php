<?php

namespace App\Support;

final class EstimatedDurationMinutes
{
    /**
     * Convert client estimated_time strings (e.g. "45 mins", "1.5 hours") or numeric values to minutes.
     */
    public static function resolve(mixed $value, int $default = 60): int
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_numeric($value)) {
            return max(15, (int) $value);
        }

        $text = strtolower(trim((string) $value));

        if (preg_match('/(\d+(?:\.\d+)?)\s*h/', $text, $matches) === 1) {
            return max(15, (int) round((float) $matches[1] * 60));
        }

        if (preg_match('/(\d+)\s*m/', $text, $matches) === 1) {
            return max(15, (int) $matches[1]);
        }

        return $default;
    }
}
