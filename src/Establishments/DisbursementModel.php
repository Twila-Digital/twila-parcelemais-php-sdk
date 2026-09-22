<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Establishments;

final class DisbursementModel
{
    public const ESTABLISHMENT_CHAIN = 1;
    public const ESTABLISHMENT = 2;
    public const EXTERNAL = 3;
    public const UNKNOWN = -1;

    private function __construct()
    {
    }

    public static function fromWireValue(int $value): int
    {
        $known = [self::ESTABLISHMENT_CHAIN, self::ESTABLISHMENT, self::EXTERNAL];

        return in_array($value, $known, true) ? $value : self::UNKNOWN;
    }
}
