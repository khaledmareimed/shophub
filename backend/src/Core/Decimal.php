<?php

declare(strict_types=1);

namespace App\Core;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

/**
 * Fixed-scale decimal helpers for money (avoids float; works without ext-bcmath).
 */
final class Decimal
{
    public static function add(string $a, string $b, int $scale = 2): string
    {
        return BigDecimal::of($a)
            ->plus($b)
            ->toScale($scale, RoundingMode::HALF_UP)
            ->__toString();
    }

    public static function sub(string $a, string $b, int $scale = 2): string
    {
        return BigDecimal::of($a)
            ->minus($b)
            ->toScale($scale, RoundingMode::HALF_UP)
            ->__toString();
    }

    public static function mul(string $a, string $b, int $scale = 2): string
    {
        return BigDecimal::of($a)
            ->multipliedBy($b)
            ->toScale($scale, RoundingMode::HALF_UP)
            ->__toString();
    }

    public static function div(string $a, string $b, int $scale): string
    {
        return BigDecimal::of($a)
            ->dividedBy($b, $scale, RoundingMode::HALF_UP)
            ->__toString();
    }

    /** @return int -1, 0, or 1 (same idea as bccomp with fixed scale) */
    public static function comp(string $a, string $b, int $scale = 2): int
    {
        $x = BigDecimal::of($a)->toScale($scale, RoundingMode::HALF_UP);
        $y = BigDecimal::of($b)->toScale($scale, RoundingMode::HALF_UP);
        return $x->compareTo($y);
    }
}
