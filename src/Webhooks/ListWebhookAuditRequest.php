<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Webhooks;

final class ListWebhookAuditRequest
{
    /** @var string|null data-hora ISO-8601 */
    public $startDate;

    /** @var string|null data-hora ISO-8601 */
    public $endDate;

    /** @var string|null */
    public $orderId;

    /** @var int|null */
    public $orderNumber;

    /** @var int|null código HTTP (100–599) devolvido pelo endpoint do parceiro */
    public $statusCode;

    /** @var int */
    public $page;

    /** @var int */
    public $pageSize;

    public function __construct(
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $orderId = null,
        ?int $orderNumber = null,
        ?int $statusCode = null,
        int $page = 1,
        int $pageSize = 10
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->orderId = $orderId;
        $this->orderNumber = $orderNumber;
        $this->statusCode = $statusCode;
        $this->page = $page;
        $this->pageSize = $pageSize;
    }
}
