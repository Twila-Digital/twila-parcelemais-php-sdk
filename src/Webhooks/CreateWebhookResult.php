<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Webhooks;

final class CreateWebhookResult
{
    /** @var string */
    public $signingSecret;

    public function __construct(string $signingSecret)
    {
        $this->signingSecret = $signingSecret;
    }
}
