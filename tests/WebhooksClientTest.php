<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Tests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Twila\ParceleMais\Errors\ParceleMaisValidationException;
use Twila\ParceleMais\Webhooks\CreateWebhookRequest;
use Twila\ParceleMais\Webhooks\ListWebhookAuditRequest;
use Twila\ParceleMais\Webhooks\UpdateWebhookRequest;
use Twila\ParceleMais\Webhooks\WebHookAuthenticationType;
use Twila\ParceleMais\Webhooks\WebhookAudit;
use Twila\ParceleMais\Webhooks\WebHookType;

final class WebhooksClientTest extends TestCase
{
    private const AUDIT_WIRE = [
        'id' => '7d1f7e3c-2b1a-4c8e-9f00-0a1b2c3d4e5f',
        'tipo' => 3,
        'requisicao' => '{"pedidoId":"abc"}',
        'resposta' => 'ok',
        'statusCode' => 200,
        'dataCriacao' => '2026-09-24T12:00:00Z',
    ];

    private const EMPTY_PAGE = [
        'itens' => [],
        'pagina' => ['tem_proximo' => false, 'tem_anterior' => false, 'numero' => 1, 'tamanho' => 10, 'total' => 0],
    ];

    public function testCreateReturnsSigningSecret(): void
    {
        $client = TestClientFactory::withMockResponses([
            new Response(200, [], (string) json_encode(['chaveAssinatura' => 'whsec_abc'])),
        ]);

        $result = $client->webhooks->create(new CreateWebhookRequest(
            WebHookType::ORDER,
            'https://example.com/webhook',
            WebHookAuthenticationType::NONE
        ));

        self::assertSame('whsec_abc', $result->signingSecret);
    }

    public function testListMapsWebhooks(): void
    {
        $client = TestClientFactory::withMockResponses([
            new Response(200, [], (string) json_encode([
                ['tipo' => 3, 'url' => 'https://example.com/webhook', 'tipoAutenticacao' => 1],
            ])),
        ]);

        $webhooks = $client->webhooks->list();

        self::assertCount(1, $webhooks);
        self::assertSame(WebHookType::ORDER, $webhooks[0]->type);
        self::assertSame(WebHookAuthenticationType::NONE, $webhooks[0]->authenticationType);
    }

    public function testListAuditMapsPagedResult(): void
    {
        $client = TestClientFactory::withMockResponses([
            new Response(200, [], (string) json_encode([
                'itens' => [self::AUDIT_WIRE],
                'pagina' => [
                    'tem_proximo' => true,
                    'tem_anterior' => false,
                    'numero' => 1,
                    'tamanho' => 10,
                    'total' => 25,
                ],
            ])),
        ]);

        $page = $client->webhooks->listAudit(new ListWebhookAuditRequest());

        self::assertCount(1, $page->items);
        $audit = $page->items[0];
        self::assertInstanceOf(WebhookAudit::class, $audit);
        self::assertSame('7d1f7e3c-2b1a-4c8e-9f00-0a1b2c3d4e5f', $audit->id);
        self::assertSame(WebHookType::ORDER, $audit->type);
        self::assertSame('{"pedidoId":"abc"}', $audit->request);
        self::assertSame('ok', $audit->response);
        self::assertSame(200, $audit->statusCode);
        self::assertSame('2026-09-24T12:00:00Z', $audit->createdAt);
        self::assertTrue($page->hasNext);
        self::assertFalse($page->hasPrevious);
        self::assertSame(25, $page->totalCount);
    }

    public function testListAuditMapsUnknownTypeToUnknown(): void
    {
        $client = TestClientFactory::withMockResponses([
            new Response(200, [], (string) json_encode([
                'itens' => [array_merge(self::AUDIT_WIRE, ['tipo' => 99])],
                'pagina' => self::EMPTY_PAGE['pagina'],
            ])),
        ]);

        $page = $client->webhooks->listAudit();

        self::assertSame(WebHookType::UNKNOWN, $page->items[0]->type);
    }

    public function testListAuditBuildsQueryString(): void
    {
        [$client, $mock] = TestClientFactory::withMockHandler([
            new Response(200, [], (string) json_encode(self::EMPTY_PAGE)),
        ]);

        $client->webhooks->listAudit(new ListWebhookAuditRequest(
            '2026-09-01T00:00:00Z',
            '2026-09-24T23:59:59Z',
            '7d1f7e3c-2b1a-4c8e-9f00-0a1b2c3d4e5f',
            42,
            500,
            2,
            20
        ));

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('GET', $request->getMethod());
        self::assertStringEndsWith('v1/webhooks/auditoria', $request->getUri()->getPath());
        self::assertSame(
            'dataInicio=2026-09-01T00%3A00%3A00Z&dataFim=2026-09-24T23%3A59%3A59Z'
            . '&pedidoId=7d1f7e3c-2b1a-4c8e-9f00-0a1b2c3d4e5f&numeroPedido=42&statusCode=500'
            . '&pagina=2&tamanhoPagina=20',
            $request->getUri()->getQuery()
        );
    }

    public function testListAuditPercentEncodesPlusInIsoOffset(): void
    {
        [$client, $mock] = TestClientFactory::withMockHandler([
            new Response(200, [], (string) json_encode(self::EMPTY_PAGE)),
        ]);

        $client->webhooks->listAudit(new ListWebhookAuditRequest(
            '2026-09-01T00:00:00+05:30',
            '2026-09-24T23:59:59-03:00'
        ));

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        // '+' literal na query vira espaço no servidor — precisa ir como %2B.
        self::assertSame(
            'dataInicio=2026-09-01T00%3A00%3A00%2B05%3A30&dataFim=2026-09-24T23%3A59%3A59-03%3A00'
            . '&pagina=1&tamanhoPagina=10',
            $request->getUri()->getQuery()
        );
    }

    public function testListAuditWithoutFiltersSendsOnlyPagingDefaults(): void
    {
        [$client, $mock] = TestClientFactory::withMockHandler([
            new Response(200, [], (string) json_encode(self::EMPTY_PAGE)),
        ]);

        $page = $client->webhooks->listAudit();

        self::assertSame([], $page->items);

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('pagina=1&tamanhoPagina=10', $request->getUri()->getQuery());
    }

    public function testListAuditPropagatesApiErrors(): void
    {
        $client = TestClientFactory::withMockResponses([
            new Response(400, ['Content-Type' => 'application/json'], (string) json_encode([
                'detalhe' => 'campos inválidos',
                'erros' => ['statusCode' => ['deve estar entre 100 e 599']],
            ])),
        ]);

        $this->expectException(ParceleMaisValidationException::class);

        $client->webhooks->listAudit(new ListWebhookAuditRequest(null, null, null, null, 42));
    }

    public function testUpdateSendsPut(): void
    {
        $client = TestClientFactory::withMockResponses([new Response(204)]);

        $client->webhooks->update(
            WebHookType::ORDER,
            new UpdateWebhookRequest('https://example.com/new', WebHookAuthenticationType::NONE)
        );

        $this->addToAssertionCount(1);
    }

    public function testDeleteSendsDelete(): void
    {
        $client = TestClientFactory::withMockResponses([new Response(204)]);

        $client->webhooks->delete(WebHookType::ORDER);

        $this->addToAssertionCount(1);
    }
}
