<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Simulations;

final class SimulateInstallmentsRequest
{
    /** @var float */
    public $requestedAmount;

    /** @var int|null one dos CalculationValueType::* */
    public $calculationValueType;

    public function __construct(float $requestedAmount, ?int $calculationValueType = null)
    {
        $this->requestedAmount = $requestedAmount;
        $this->calculationValueType = $calculationValueType;
    }
}
