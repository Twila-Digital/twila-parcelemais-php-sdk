<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Orders;

/**
 * Pseudo-enum (classe com constantes) em vez de `enum` nativo do PHP —
 * o SDK suporta PHP 7.4+, e `enum` só existe a partir do PHP 8.1.
 */
final class OrderStatus
{
    public const UNDEFINED = 0;
    public const ANALYSING = 1;
    public const APPROVED = 2;
    public const UNAVAILABLE_BALANCE = 3;
    public const ANALYSIS_EXPIRED = 4;
    public const PENDING_PAYMENT = 5;
    public const BIOMETRY_REFUSED = 6;
    public const BIOMETRY_APPROVED = 7;
    public const PAYMENT_REFUSED = 8;
    public const PURCHASED = 9;
    public const UNAUTHORIZED = 10;
    public const PENDING_AUTHORIZATION = 11;
    public const AWAITING_REGISTRATION = 12;
    public const SALE_NOT_STARTED = 13;
    public const CANCELED = 14;
    public const BILLING = 15;
    public const COMPLETED = 16;
    public const FROZEN = 17;
    public const PENDING_PAYMENT_CONFIRMATION = 18;
    public const DISBURSED = 19;
    public const UNKNOWN = -1;

    private function __construct()
    {
    }

    public static function fromWireValue(int $value): int
    {
        return in_array($value, self::values(), true) ? $value : self::UNKNOWN;
    }

    /**
     * @return array<int, int>
     */
    public static function values(): array
    {
        return [
            self::UNDEFINED,
            self::ANALYSING,
            self::APPROVED,
            self::UNAVAILABLE_BALANCE,
            self::ANALYSIS_EXPIRED,
            self::PENDING_PAYMENT,
            self::BIOMETRY_REFUSED,
            self::BIOMETRY_APPROVED,
            self::PAYMENT_REFUSED,
            self::PURCHASED,
            self::UNAUTHORIZED,
            self::PENDING_AUTHORIZATION,
            self::AWAITING_REGISTRATION,
            self::SALE_NOT_STARTED,
            self::CANCELED,
            self::BILLING,
            self::COMPLETED,
            self::FROZEN,
            self::PENDING_PAYMENT_CONFIRMATION,
            self::DISBURSED,
        ];
    }
}
