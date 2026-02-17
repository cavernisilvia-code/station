<?php

declare(strict_types=1);

namespace App\Util;

final class Random
{
    /**
     * Inclusive min/max.
     */
    public static function int(int $min, int $max): int
    {
        if ($min > $max) {
            throw new \InvalidArgumentException("Invalid range: $min > $max");
        }

        return random_int($min, $max);
    }
}
