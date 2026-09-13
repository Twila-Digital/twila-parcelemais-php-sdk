<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Errors;

use Twila\ParceleMais\Internal\Http\ApiResponse;

final class ExceptionFactory
{
    public static function fromResponse(ApiResponse $response): ParceleMaisException
    {
        $problemDetails = ProblemDetails::parse($response->body);
        $message = $problemDetails->detail
            ?? $problemDetails->title
            ?? sprintf('A API do Parcele+ retornou %d.', $response->statusCode);

        if ($response->statusCode === 401) {
            return new ParceleMaisAuthenticationException($message);
        }

        if ($response->statusCode === 400 && !empty($problemDetails->errors)) {
            return new ParceleMaisValidationException($message, $problemDetails);
        }

        if ($response->statusCode === 429) {
            return new ParceleMaisRateLimitException($message, $problemDetails, self::retryAfterMs($response));
        }

        return new ParceleMaisApiException($message, $response->statusCode, $problemDetails);
    }

    private static function retryAfterMs(ApiResponse $response): ?int
    {
        $retryAfter = $response->header('retry-after');
        if ($retryAfter === null || $retryAfter === '') {
            return null;
        }

        if (is_numeric($retryAfter)) {
            return (int) (((float) $retryAfter) * 1000);
        }

        $timestamp = strtotime($retryAfter);
        if ($timestamp === false) {
            return null;
        }

        return max(0, ($timestamp - time()) * 1000);
    }
}
