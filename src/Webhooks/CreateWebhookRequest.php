<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Webhooks;

final class CreateWebhookRequest
{
    /** @var int one dos WebHookType::* */
    public $type;

    /** @var string */
    public $url;

    /** @var int one dos WebHookAuthenticationType::* */
    public $authenticationType;

    /** @var string|null */
    public $credential;

    public function __construct(int $type, string $url, int $authenticationType, ?string $credential = null)
    {
        $this->type = $type;
        $this->url = $url;
        $this->authenticationType = $authenticationType;
        $this->credential = $credential;
    }
}
