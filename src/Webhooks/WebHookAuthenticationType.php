<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Webhooks;

final class WebHookAuthenticationType
{
    public const NONE = 1;
    public const BASIC = 2;
    public const JWT = 3;
    public const UNKNOWN = -1;

    private function __construct()
    {
    }

    public static function fromWireValue(int $value): int
    {
        return in_array($value, [self::NONE, self::BASIC, self::JWT], true) ? $value : self::UNKNOWN;
    }
}
