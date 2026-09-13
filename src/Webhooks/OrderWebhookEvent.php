<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Webhooks;

final class OrderWebhookEvent
{
    /** @var string */
    public $orderId;

    /** @var int one dos \Twila\ParceleMais\Orders\OrderStatus::* */
    public $status;

    /** @var int */
    public $statusRaw;

    /** @var string */
    public $statusName;

    public function __construct(string $orderId, int $status, int $statusRaw, string $statusName)
    {
        $this->orderId = $orderId;
        $this->status = $status;
        $this->statusRaw = $statusRaw;
        $this->statusName = $statusName;
    }
}
