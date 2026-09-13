<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Webhooks;

final class UpdateWebhookRequest
{
    /** @var string */
    public $url;

    /** @var int one dos WebHookAuthenticationType::* */
    public $authenticationType;

    /** @var string|null */
    public $credential;

    public function __construct(string $url, int $authenticationType, ?string $credential = null)
    {
        $this->url = $url;
        $this->authenticationType = $authenticationType;
        $this->credential = $credential;
    }
}
