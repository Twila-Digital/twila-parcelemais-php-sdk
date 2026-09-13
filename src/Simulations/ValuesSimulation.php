<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Simulations;

final class ValuesSimulation
{
    /** @var float */
    public $saleAmount;

    /** @var float */
    public $disbursementAmount;

    /** @var float */
    public $installmentAmount;

    public function __construct(float $saleAmount, float $disbursementAmount, float $installmentAmount)
    {
        $this->saleAmount = $saleAmount;
        $this->disbursementAmount = $disbursementAmount;
        $this->installmentAmount = $installmentAmount;
    }
}
