<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Twila\ParceleMais\Config\ClientOptions;
use Twila\ParceleMais\ParceleMaisClient;

final class AuthRefreshTest extends TestCase
{
    public function test401RefreshesTokenAndRetriesOnce(): void
    {
        $mock = new MockHandler([
            new Response(200, [], (string) json_encode([
                'token_de_acesso' => 'stale-token',
                'expira_em_segundos' => 3600,
                'tipo_de_token' => 'Bearer',
            ])),
            new Response(401, [], (string) json_encode(['detalhe' => 'token expirado'])),
            new Response(200, [], (string) json_encode([
                'token_de_acesso' => 'fresh-token',
                'expira_em_segundos' => 3600,
                'tipo_de_token' => 'Bearer',
            ])),
            new Response(200, [], (string) json_encode([
                'id' => 'order-1',
                'numero' => 1,
                'status' => ['valor' => 9, 'descricao' => 'Comprado'],
                'documentoCliente' => '12345678901',
                'razaoSocialEstabelecimento' => 'Loja',
                'documentoEstabelecimento' => '12345678000195',
                'criadoEm' => '2026-01-01T00:00:00-03:00',
            ])),
        ]);

        $seenAuthorizationHeaders = [];
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::mapRequest(function ($request) use (&$seenAuthorizationHeaders) {
            $seenAuthorizationHeaders[] = $request->getHeaderLine('Authorization');
            return $request;
        }));

        $httpClient = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $client = new ParceleMaisClient(
            new ClientOptions('cid', 'csecret', 'production', TestClientFactory::BASE_URL),
            $httpClient
        );

        $order = $client->orders->get('order-1');

        self::assertSame('order-1', $order->id);
        self::assertSame(
            ['', 'Bearer stale-token', '', 'Bearer fresh-token'],
            $seenAuthorizationHeaders
        );
    }
}
