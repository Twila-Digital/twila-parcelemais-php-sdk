<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Tests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Twila\ParceleMais\Simulations\SimulateInstallmentsRequest;
use Twila\ParceleMais\Simulations\SimulateValuesRequest;

final class SimulationsClientTest extends TestCase
{
    public function testSimulateInstallmentsMapsResponse(): void
    {
        $client = TestClientFactory::withMockResponses([
            new Response(200, [], (string) json_encode([
                ['valorTotalDebito' => 1600.0, 'prazo' => 3, 'valorParcela' => 533.33],
            ])),
        ]);

        $parcelas = $client->simulations->simulateInstallments(new SimulateInstallmentsRequest(1500.0));

        self::assertCount(1, $parcelas);
        self::assertSame(3, $parcelas[0]->term);
        self::assertSame(533.33, $parcelas[0]->installmentAmount);
    }

    public function testSimulateValuesMapsResponse(): void
    {
        $client = TestClientFactory::withMockResponses([
            new Response(200, [], (string) json_encode([
                'valoresEstabelecimento' => ['valorVenda' => 1500.0, 'valorDesembolso' => 1450.0],
                'valoresCliente' => ['valorParcela' => 533.33],
            ])),
        ]);

        $result = $client->simulations->simulateValues(new SimulateValuesRequest(1500.0, 3));

        self::assertSame(1500.0, $result->saleAmount);
        self::assertSame(1450.0, $result->disbursementAmount);
        self::assertSame(533.33, $result->installmentAmount);
    }
}
