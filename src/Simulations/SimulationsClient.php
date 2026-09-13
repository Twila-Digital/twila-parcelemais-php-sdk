<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Simulations;

use Twila\ParceleMais\Internal\Http\ApiRequestExecutor;
use Twila\ParceleMais\Internal\Http\QueryStringBuilder;
use Twila\ParceleMais\Internal\Mapping\SimulationMapper;

final class SimulationsClient
{
    /** @var ApiRequestExecutor */
    private $executor;

    public function __construct(ApiRequestExecutor $executor)
    {
        $this->executor = $executor;
    }

    /**
     * @return array<int, InstallmentSimulation>
     */
    public function simulateInstallments(SimulateInstallmentsRequest $request): array
    {
        $calculationValueType = $request->calculationValueType ?? CalculationValueType::GROSS_AMOUNT;

        $path = (new QueryStringBuilder())
            ->add('valorSolicitado', $request->requestedAmount)
            ->add('tipoValorCalculo', $calculationValueType)
            ->build('v1/order/simulate-installments');

        $response = $this->executor->get($path);
        ApiRequestExecutor::ensureSuccess($response);

        return array_map([SimulationMapper::class, 'installmentToPublic'], $response->body);
    }

    public function simulateValues(SimulateValuesRequest $request): ValuesSimulation
    {
        $calculationValueType = $request->calculationValueType ?? CalculationValueType::GROSS_AMOUNT;

        $path = (new QueryStringBuilder())
            ->add('valor', $request->amount)
            ->add('prazo', $request->term)
            ->add('modeloJuros', 1)
            ->add('tipoValorCalculo', $calculationValueType)
            ->build('v1/order/simulate-values');

        $response = $this->executor->get($path);
        ApiRequestExecutor::ensureSuccess($response);

        return SimulationMapper::valuesToPublic($response->body);
    }
}
