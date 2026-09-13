<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Internal\Auth;

final class AccessTokenProvider
{
    private const CLOCK_SKEW_SECONDS = 60;

    /** @var TokenApiClient */
    private $tokenApiClient;

    /** @var string */
    private $clientId;

    /** @var string */
    private $clientSecret;

    /** @var string|null */
    private $cachedValue;

    /** @var int|null */
    private $cachedExpiresAt;

    public function __construct(TokenApiClient $tokenApiClient, string $clientId, string $clientSecret)
    {
        $this->tokenApiClient = $tokenApiClient;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function getToken(): string
    {
        if ($this->cachedValue !== null && !$this->isCloseToExpiry()) {
            return $this->cachedValue;
        }

        $response = $this->tokenApiClient->generate($this->clientId, $this->clientSecret);

        $this->cachedValue = $response['token_de_acesso'];
        $this->cachedExpiresAt = time() + $response['expira_em_segundos'];

        return $this->cachedValue;
    }

    public function invalidate(): void
    {
        $this->cachedValue = null;
        $this->cachedExpiresAt = null;
    }

    private function isCloseToExpiry(): bool
    {
        return $this->cachedExpiresAt === null || (time() + self::CLOCK_SKEW_SECONDS) >= $this->cachedExpiresAt;
    }
}
