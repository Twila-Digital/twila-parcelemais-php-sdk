<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Internal\Mapping;

use Twila\ParceleMais\Simulations\InstallmentSimulation;
use Twila\ParceleMais\Simulations\ValuesSimulation;

final class SimulationMapper
{
    /**
     * @param array<string, mixed> $wire
     */
    public static function installmentToPublic(array $wire): InstallmentSimulation
    {
        return new InstallmentSimulation($wire['valorTotalDebito'], $wire['prazo'], $wire['valorParcela']);
    }

    /**
     * @param array<string, mixed> $wire
     */
    public static function valuesToPublic(array $wire): ValuesSimulation
    {
        $establishment = $wire['valoresEstabelecimento'];
        $customer = $wire['valoresCliente'];

        return new ValuesSimulation(
            $establishment['valorVenda'],
            $establishment['valorDesembolso'],
            $customer['valorParcela']
        );
    }
}
