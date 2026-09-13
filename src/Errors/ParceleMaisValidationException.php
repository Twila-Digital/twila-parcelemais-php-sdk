<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Errors;

final class ParceleMaisValidationException extends ParceleMaisApiException
{
    public function __construct(string $message, ProblemDetails $problemDetails)
    {
        parent::__construct($message, 400, $problemDetails);
    }
}
