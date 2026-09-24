<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Establishments;

final class BankAccountType
{
    public const CURRENT = 1;
    public const SAVINGS = 2;
    public const PAYMENT = 3;
    public const UNKNOWN = -1;

    private function __construct()
    {
    }

    public static function fromWireValue(int $value): int
    {
        return in_array($value, [self::CURRENT, self::SAVINGS, self::PAYMENT], true) ? $value : self::UNKNOWN;
    }
}
