<?php

declare(strict_types=1);

namespace Twila\ParceleMais;

final class PagedResult
{
    /** @var array<int, mixed> */
    public $items;

    /** @var bool */
    public $hasNext;

    /** @var bool */
    public $hasPrevious;

    /** @var int */
    public $pageNumber;

    /** @var int */
    public $pageSize;

    /** @var int */
    public $totalCount;

    /**
     * @param array<int, mixed> $items
     */
    public function __construct(
        array $items,
        bool $hasNext,
        bool $hasPrevious,
        int $pageNumber,
        int $pageSize,
        int $totalCount
    ) {
        $this->items = $items;
        $this->hasNext = $hasNext;
        $this->hasPrevious = $hasPrevious;
        $this->pageNumber = $pageNumber;
        $this->pageSize = $pageSize;
        $this->totalCount = $totalCount;
    }
}
