<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Twila\ParceleMais\Config\ClientOptions;
use Twila\ParceleMais\Config\ResilienceOptions;
use Twila\ParceleMais\ParceleMaisClient;

final class TestClientFactory
{
    public const BASE_URL = 'https://sdk-test.local/integration/';

    public static function defaultTokenResponse(): Response
    {
        return new Response(200, [], (string) json_encode([
            'token_de_acesso' => 'test-token',
            'expira_em_segundos' => 3600,
            'tipo_de_token' => 'Bearer',
        ]));
    }

    /**
     * Cria um ParceleMaisClient cujas chamadas HTTP internas (token + recurso) compartilham a
     * mesma fila do MockHandler — a ordem das respostas enfileiradas precisa bater com a ordem
     * real de chamadas (token primeiro, sempre).
     *
     * @param array<int, Response> $responses
     */
    public static function withMockResponses(array $responses, ?ResilienceOptions $resilience = null): ParceleMaisClient
    {
        $mock = new MockHandler(array_merge([self::defaultTokenResponse()], $responses));
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack, 'http_errors' => false]);

        $options = new ClientOptions(
            'test-client-id',
            'test-client-secret',
            'production',
            self::BASE_URL,
            $resilience
        );

        return new ParceleMaisClient($options, $httpClient);
    }
}
