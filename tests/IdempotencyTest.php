<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Tests;

use PHPUnit\Framework\TestCase;
use Twila\ParceleMais\Internal\Idempotency\IdempotencyClassifier;

final class IdempotencyTest extends TestCase
{
    /**
     * @dataProvider requiresIdempotencyKeyProvider
     */
    public function testRequiresIdempotencyKey(string $method, string $path, bool $expected): void
    {
        self::assertSame($expected, IdempotencyClassifier::requiresIdempotencyKey($method, $path));
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: bool}>
     */
    public static function requiresIdempotencyKeyProvider(): array
    {
        return [
            'create order' => ['POST', 'v1/order', true],
            'start cdc sale' => ['POST', 'v1/order/start-cdc-sale', true],
            'import invoice' => ['POST', 'v1/order/invoice', true],
            'create webhook' => ['POST', 'v1/webhooks', true],
            'simulate installments' => ['POST', 'v1/order/simulate-installments', false],
            'get order' => ['GET', 'v1/order', false],
        ];
    }

    /**
     * @dataProvider retrySafeProvider
     */
    public function testIsRetrySafe(string $method, string $path, bool $hasKey, bool $expected): void
    {
        self::assertSame($expected, IdempotencyClassifier::isRetrySafe($method, $path, $hasKey));
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: bool, 3: bool}>
     */
    public static function retrySafeProvider(): array
    {
        return [
            'get' => ['GET', 'v1/order/123', false, true],
            'head' => ['HEAD', 'v1/order', false, true],
            'put webhook' => ['PUT', 'v1/webhooks/1', false, true],
            'delete webhook' => ['DELETE', 'v1/webhooks/1', false, true],
            'put order (not webhook)' => ['PUT', 'v1/order/123', false, false],
            'post with key' => ['POST', 'v1/order', true, true],
            'post without key' => ['POST', 'v1/order', false, false],
            'post simulate' => ['POST', 'v1/order/simulate-values', false, false],
        ];
    }
}
