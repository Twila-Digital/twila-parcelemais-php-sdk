<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Internal\Idempotency;

final class IdempotencyClassifier
{
    private const MUTABLE_PATHS_REQUIRING_IDEMPOTENCY_KEY = [
        'v1/order/start-cdc-sale',
        'v1/order/invoice',
        'v1/order',
        'v1/webhooks',
    ];

    public static function requiresIdempotencyKey(string $method, string $path): bool
    {
        if (strtoupper($method) !== 'POST') {
            return false;
        }

        foreach (self::MUTABLE_PATHS_REQUIRING_IDEMPOTENCY_KEY as $mutablePath) {
            if (self::endsWith($path, $mutablePath)) {
                return true;
            }
        }

        return false;
    }

    public static function isRetrySafe(string $method, string $path, bool $hasIdempotencyKey): bool
    {
        $upperMethod = strtoupper($method);

        if (in_array($upperMethod, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        if (in_array($upperMethod, ['PUT', 'DELETE'], true)) {
            return strpos($path, 'v1/webhooks/') !== false;
        }

        if ($upperMethod !== 'POST') {
            return false;
        }

        return self::requiresIdempotencyKey($method, $path) && $hasIdempotencyKey;
    }

    private static function endsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);

        return $length === 0 || substr($haystack, -$length) === $needle;
    }
}
