<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Internal\Resilience;

use GuzzleHttp\Exception\GuzzleException;
use Twila\ParceleMais\Config\ResilienceOptions;
use Twila\ParceleMais\Internal\Http\ApiResponse;

final class TransientFailureClassifier
{
    private const TRANSIENT_STATUS_CODES = [408, 429, 502, 503, 504];

    public static function isTransientResponse(ApiResponse $response, ResilienceOptions $options): bool
    {
        if (in_array($response->statusCode, self::TRANSIENT_STATUS_CODES, true)) {
            return true;
        }

        return $response->statusCode === 500 && $options->retryOn500;
    }

    public static function isNetworkError(\Throwable $error): bool
    {
        return $error instanceof GuzzleException;
    }
}
