<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Customers;

final class ListCustomersRequest
{
    /** @var string|null */
    public $name;

    /** @var string|null */
    public $document;

    /** @var int */
    public $page;

    /** @var int */
    public $pageSize;

    public function __construct(?string $name = null, ?string $document = null, int $page = 1, int $pageSize = 10)
    {
        $this->name = $name;
        $this->document = $document;
        $this->page = $page;
        $this->pageSize = $pageSize;
    }
}
