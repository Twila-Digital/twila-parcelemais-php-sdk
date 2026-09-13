<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Webhooks;

final class Webhook
{
    /** @var int one dos WebHookType::* */
    public $type;

    /** @var string */
    public $url;

    /** @var int one dos WebHookAuthenticationType::* */
    public $authenticationType;

    public function __construct(int $type, string $url, int $authenticationType)
    {
        $this->type = $type;
        $this->url = $url;
        $this->authenticationType = $authenticationType;
    }
}
