<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Orders;

final class Order
{
    /** @var string */
    public $id;

    /** @var int */
    public $number;

    /** @var int one dos OrderStatus::* */
    public $status;

    /** @var string */
    public $statusDescription;

    /** @var string */
    public $customerDocument;

    /** @var string */
    public $establishmentLegalName;

    /** @var string */
    public $establishmentDocument;

    /** @var string */
    public $createdAt;

    /** @var float|null */
    public $total;

    /** @var string|null */
    public $customerName;

    /** @var int|null */
    public $term;

    /** @var string|null */
    public $description;

    /** @var float|null */
    public $approvedAmount;

    /** @var bool|null */
    public $disbursed;

    /** @var string|null */
    public $disbursedAt;

    /** @var float|null */
    public $requestedAmount;

    public function __construct(
        string $id,
        int $number,
        int $status,
        string $statusDescription,
        string $customerDocument,
        string $establishmentLegalName,
        string $establishmentDocument,
        string $createdAt,
        ?float $total = null,
        ?string $customerName = null,
        ?int $term = null,
        ?string $description = null,
        ?float $approvedAmount = null,
        ?bool $disbursed = null,
        ?string $disbursedAt = null,
        ?float $requestedAmount = null
    ) {
        $this->id = $id;
        $this->number = $number;
        $this->status = $status;
        $this->statusDescription = $statusDescription;
        $this->customerDocument = $customerDocument;
        $this->establishmentLegalName = $establishmentLegalName;
        $this->establishmentDocument = $establishmentDocument;
        $this->createdAt = $createdAt;
        $this->total = $total;
        $this->customerName = $customerName;
        $this->term = $term;
        $this->description = $description;
        $this->approvedAmount = $approvedAmount;
        $this->disbursed = $disbursed;
        $this->disbursedAt = $disbursedAt;
        $this->requestedAmount = $requestedAmount;
    }
}
