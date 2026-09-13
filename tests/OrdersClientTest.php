<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Tests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Twila\ParceleMais\Orders\Address;
use Twila\ParceleMais\Orders\CreateOrderRequest;
use Twila\ParceleMais\Orders\ListOrdersRequest;
use Twila\ParceleMais\Orders\OrderStatus;

final class OrdersClientTest extends TestCase
{
    private const ORDER_WIRE = [
        'id' => 'order-1',
        'numero' => 42,
        'status' => ['valor' => 9, 'descricao' => 'Comprado'],
        'documentoCliente' => '12345678901',
        'razaoSocialEstabelecimento' => 'Loja Exemplo',
        'documentoEstabelecimento' => '12345678000195',
        'criadoEm' => '2026-01-01T00:00:00-03:00',
    ];

    private function address(): Address
    {
        return new Address('Av. Paulista', '1578', 'Bela Vista', 'São Paulo', 'SP', '01311000');
    }

    public function testCreateReturnsOrderId(): void
    {
        $client = TestClientFactory::withMockResponses([
            new Response(200, [], (string) json_encode(['pedidoId' => 'order-1'])),
        ]);

        $orderId = $client->orders->create(new CreateOrderRequest(
            '12345678901',
            '+5511999998888',
            '12345678000195',
            1500.0,
            'Maria Souza',
            'maria@exemplo.com.br',
            '1990-05-20T00:00:00-03:00',
            $this->address()
        ));

        self::assertSame('order-1', $orderId);
    }

    public function testGetMapsStatusAndFields(): void
    {
        $client = TestClientFactory::withMockResponses([
            new Response(200, [], (string) json_encode(self::ORDER_WIRE)),
        ]);

        $order = $client->orders->get('order-1');

        self::assertSame('order-1', $order->id);
        self::assertSame(42, $order->number);
        self::assertSame(OrderStatus::PURCHASED, $order->status);
        self::assertSame('Comprado', $order->statusDescription);
        self::assertSame('12345678901', $order->customerDocument);
    }

    public function testListMapsPagedResult(): void
    {
        $client = TestClientFactory::withMockResponses([
            new Response(200, [], (string) json_encode([
                'itens' => [self::ORDER_WIRE],
                'pagina' => [
                    'tem_proximo' => true,
                    'tem_anterior' => false,
                    'numero' => 1,
                    'tamanho' => 10,
                    'total' => 25,
                ],
            ])),
        ]);

        $page = $client->orders->list(new ListOrdersRequest(null, null, null, null, null, null, null, 1, 10));

        self::assertCount(1, $page->items);
        self::assertSame('order-1', $page->items[0]->id);
        self::assertTrue($page->hasNext);
        self::assertSame(25, $page->totalCount);
    }

    public function testStartCdcSaleReturnsCheckoutLink(): void
    {
        $client = TestClientFactory::withMockResponses([
            new Response(200, [], (string) json_encode(['linkPagamento' => 'https://pay.example.com/abc'])),
        ]);

        $link = $client->orders->startCdcSale('order-1');

        self::assertSame('https://pay.example.com/abc', $link->url);
    }
}
