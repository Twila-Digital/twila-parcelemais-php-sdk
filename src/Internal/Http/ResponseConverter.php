<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Internal\Http;

use Psr\Http\Message\ResponseInterface;

final class ResponseConverter
{
    public static function toApiResponse(ResponseInterface $raw): ApiResponse
    {
        $bodyRaw = (string) $raw->getBody();
        $decoded = $bodyRaw === '' ? null : json_decode($bodyRaw, true);

        $headers = [];
        foreach ($raw->getHeaders() as $name => $values) {
            $headers[$name] = implode(', ', $values);
        }

        return new ApiResponse($raw->getStatusCode(), $decoded, $headers);
    }
}
