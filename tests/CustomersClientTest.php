<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Tests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Twila\ParceleMais\Customers\ListCustomersRequest;

final class CustomersClientTest extends TestCase
{
    private const CUSTOMER_WIRE = [
        'id' => 'customer-1',
        'nome' => 'Maria Souza',
        'documento' => '12345678901',
        'dataDeNascimento' => '1990-05-20T00:00:00-03:00',
        'endereco' => ['rua' => 'Av. Paulista', 'cidade' => 'São Paulo', 'estado' => 'SP'],
        'email' => 'maria@exemplo.com.br',
    ];

    public function testGetMapsCustomer(): void
    {
        $client = TestClientFactory::withMockResponses([
            new Response(200, [], (string) json_encode(self::CUSTOMER_WIRE)),
        ]);

        $customer = $client->customers->get('customer-1');

        self::assertSame('customer-1', $customer->id);
        self::assertSame('Maria Souza', $customer->name);
        self::assertNotNull($customer->address);
        self::assertSame('São Paulo', $customer->address->city);
    }

    public function testListMapsPagedResult(): void
    {
        $client = TestClientFactory::withMockResponses([
            new Response(200, [], (string) json_encode([
                'itens' => [self::CUSTOMER_WIRE],
                'pagina' => [
                    'tem_proximo' => false,
                    'tem_anterior' => false,
                    'numero' => 1,
                    'tamanho' => 10,
                    'total' => 1,
                ],
            ])),
        ]);

        $page = $client->customers->list(new ListCustomersRequest());

        self::assertCount(1, $page->items);
        self::assertSame('customer-1', $page->items[0]->id);
        self::assertFalse($page->hasNext);
    }
}
