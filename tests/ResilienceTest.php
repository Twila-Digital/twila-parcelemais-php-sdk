<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Tests;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Twila\ParceleMais\Config\ResilienceOptions;
use Twila\ParceleMais\Errors\ParceleMaisTimeoutException;
use Twila\ParceleMais\Internal\Http\ApiResponse;
use Twila\ParceleMais\Internal\Resilience\ResiliencePipeline;

final class ResilienceTest extends TestCase
{
    private function fastOptions(): ResilienceOptions
    {
        return new ResilienceOptions(5000, 1000, 60000, 3, 1);
    }

    private function response(int $statusCode): ApiResponse
    {
        return new ApiResponse($statusCode, [], []);
    }

    public function testSuccessfulResponseDoesNotRetry(): void
    {
        $pipeline = new ResiliencePipeline($this->fastOptions());
        $calls = 0;

        $result = $pipeline->execute(true, function () use (&$calls) {
            $calls++;
            return $this->response(200);
        });

        self::assertSame(200, $result->statusCode);
        self::assertSame(1, $calls);
    }

    public function testTransientResponseRetriesWhenRetrySafe(): void
    {
        $pipeline = new ResiliencePipeline($this->fastOptions());
        $calls = 0;

        $result = $pipeline->execute(true, function () use (&$calls) {
            $calls++;
            return $this->response($calls < 3 ? 503 : 200);
        });

        self::assertSame(200, $result->statusCode);
        self::assertSame(3, $calls);
    }

    public function testTransientResponseDoesNotRetryWhenNotRetrySafe(): void
    {
        $pipeline = new ResiliencePipeline($this->fastOptions());
        $calls = 0;

        $result = $pipeline->execute(false, function () use (&$calls) {
            $calls++;
            return $this->response(503);
        });

        self::assertSame(503, $result->statusCode);
        self::assertSame(1, $calls);
    }

    public function testNetworkErrorRetriesAndEventuallyThrows(): void
    {
        $pipeline = new ResiliencePipeline($this->fastOptions());
        $calls = 0;

        $this->expectException(ConnectException::class);

        try {
            $pipeline->execute(true, function () use (&$calls) {
                $calls++;
                throw new ConnectException('boom', new Request('GET', 'https://example.com'));
            });
        } finally {
            self::assertSame(3, $calls);
        }
    }

    public function testTotalTimeoutRaisesTimeoutException(): void
    {
        $options = new ResilienceOptions(1, 1000, 60000, 5, 50);
        $pipeline = new ResiliencePipeline($options);

        $this->expectException(ParceleMaisTimeoutException::class);
        $pipeline->execute(true, function () {
            return $this->response(503);
        });
    }

    public function testCircuitBreakerOpensAfterFailureThresholdAndBlocksNextCall(): void
    {
        $options = new ResilienceOptions(5000, 1000, 60000, 1, 500, 0.5, 30000, 2, 60000);
        $pipeline = new ResiliencePipeline($options);

        for ($i = 0; $i < 2; $i++) {
            $pipeline->execute(false, function () {
                return $this->response(503);
            });
        }

        $this->expectException(ParceleMaisTimeoutException::class);
        $pipeline->execute(false, function () {
            return $this->response(200);
        });
    }
}
