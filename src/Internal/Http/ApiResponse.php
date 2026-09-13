<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Internal\Http;

final class ApiResponse
{
    /** @var int */
    public $statusCode;

    /** @var mixed */
    public $body;

    /** @var array<string, string> */
    public $headers;

    /**
     * @param mixed $body
     * @param array<string, string> $headers
     */
    public function __construct(int $statusCode, $body, array $headers)
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->headers = $headers;
    }

    public function header(string $name): ?string
    {
        foreach ($this->headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return $value;
            }
        }

        return null;
    }
}
