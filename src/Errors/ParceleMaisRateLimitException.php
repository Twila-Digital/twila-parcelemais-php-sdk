<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Errors;

final class ParceleMaisRateLimitException extends ParceleMaisApiException
{
    /** @var int|null */
    private $retryAfterMs;

    public function __construct(string $message, ProblemDetails $problemDetails, ?int $retryAfterMs = null)
    {
        parent::__construct($message, 429, $problemDetails);
        $this->retryAfterMs = $retryAfterMs;
    }

    public function getRetryAfterMs(): ?int
    {
        return $this->retryAfterMs;
    }
}
