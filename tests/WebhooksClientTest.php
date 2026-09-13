<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Tests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Twila\ParceleMais\Webhooks\CreateWebhookRequest;
use Twila\ParceleMais\Webhooks\UpdateWebhookRequest;
use Twila\ParceleMais\Webhooks\WebHookAuthenticationType;
use Twila\ParceleMais\Webhooks\WebHookType;

final class WebhooksClientTest extends TestCase
{
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
