<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Simulations;

final class InstallmentSimulation
{
    /** @var float */
    public $totalAmount;

    /** @var int */
    public $term;

    /** @var float */
    public $installmentAmount;

    public function __construct(float $totalAmount, int $term, float $installmentAmount)
    {
        $this->totalAmount = $totalAmount;
        $this->term = $term;
        $this->installmentAmount = $installmentAmount;
    }
}
