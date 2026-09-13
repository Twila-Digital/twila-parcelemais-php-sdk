<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Errors;

final class ParceleMaisAuthenticationException extends ParceleMaisException
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
