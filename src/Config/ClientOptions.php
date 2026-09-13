<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Config;

final class ClientOptions
{
    /** @var string */
    public $clientId;

    /** @var string */
    public $clientSecret;

    /** @var string */
    public $environment;

    /** @var string|null */
    public $baseUrl;

    /** @var ResilienceOptions */
    public $resilience;

    public function __construct(
        string $clientId,
        string $clientSecret,
        string $environment = Environment::PRODUCTION,
        ?string $baseUrl = null,
        ?ResilienceOptions $resilience = null
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->environment = $environment;
        $this->baseUrl = $baseUrl;
        $this->resilience = $resilience ?? new ResilienceOptions();
    }
}
