<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Orders;

final class ListOrdersRequest
{
    /** @var int|null one dos OrderStatus::* */
    public $status;

    /** @var string|null */
    public $customerDocument;

    /** @var string|null */
    public $startDate;

    /** @var string|null */
    public $endDate;

    /** @var int|null */
    public $number;

    /** @var string|null */
    public $establishmentDocument;

    /** @var string|null */
    public $description;

    /** @var int */
    public $page;

    /** @var int */
    public $pageSize;

    public function __construct(
        ?int $status = null,
        ?string $customerDocument = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $number = null,
        ?string $establishmentDocument = null,
        ?string $description = null,
        int $page = 1,
        int $pageSize = 10
    ) {
        $this->status = $status;
        $this->customerDocument = $customerDocument;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->number = $number;
        $this->establishmentDocument = $establishmentDocument;
        $this->description = $description;
        $this->page = $page;
        $this->pageSize = $pageSize;
    }
}
