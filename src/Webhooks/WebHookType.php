<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Webhooks;

final class WebHookType
{
    public const CUSTOMER = 1;
    public const SIMULATION = 2;
    public const ORDER = 3;
    public const UNKNOWN = -1;

    private function __construct()
    {
    }

    public static function fromWireValue(int $value): int
    {
        return in_array($value, [self::CUSTOMER, self::SIMULATION, self::ORDER], true) ? $value : self::UNKNOWN;
    }
}
