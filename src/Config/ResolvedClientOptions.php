<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Config;

final class ResolvedClientOptions
{
    /** @var string */
    public $clientId;

    /** @var string */
    public $clientSecret;

    /** @var string */
    public $baseUrl;

    /** @var ResilienceOptions */
    public $resilience;

    public function __construct(string $clientId, string $clientSecret, string $baseUrl, ResilienceOptions $resilience)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->baseUrl = $baseUrl;
        $this->resilience = $resilience;
    }
}
