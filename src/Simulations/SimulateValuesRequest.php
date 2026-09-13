<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Simulations;

final class SimulateValuesRequest
{
    /** @var float */
    public $amount;

    /** @var int */
    public $term;

    /** @var int|null one dos CalculationValueType::* */
    public $calculationValueType;

    public function __construct(float $amount, int $term, ?int $calculationValueType = null)
    {
        $this->amount = $amount;
        $this->term = $term;
        $this->calculationValueType = $calculationValueType;
    }
}
