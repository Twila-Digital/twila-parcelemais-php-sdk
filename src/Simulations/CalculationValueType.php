<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Simulations;

final class CalculationValueType
{
    public const GROSS_AMOUNT = 1;
    public const LIQUID_AMOUNT = 2;
    public const UNKNOWN = -1;

    private function __construct()
    {
    }

    public static function fromWireValue(int $value): int
    {
        return in_array($value, [self::GROSS_AMOUNT, self::LIQUID_AMOUNT], true) ? $value : self::UNKNOWN;
    }
}
