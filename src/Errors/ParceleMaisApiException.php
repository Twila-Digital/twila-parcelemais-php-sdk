<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Errors;

class ParceleMaisApiException extends ParceleMaisException
{
    /** @var int */
    private $statusCode;

    /** @var ProblemDetails */
    private $problemDetails;

    public function __construct(string $message, int $statusCode, ProblemDetails $problemDetails)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->problemDetails = $problemDetails;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getProblemDetails(): ProblemDetails
    {
        return $this->problemDetails;
    }

    public function getErrorCode(): ?string
    {
        return $this->problemDetails->type;
    }

    /**
     * @return array<string, array<int, string>>|null
     */
    public function getFieldErrors(): ?array
    {
        return $this->problemDetails->errors;
    }

    public function getCorrelationId(): ?string
    {
        return $this->problemDetails->correlationId;
    }
}
