<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Internal\Mapping;

use Twila\ParceleMais\Webhooks\CreateWebhookRequest;
use Twila\ParceleMais\Webhooks\UpdateWebhookRequest;
use Twila\ParceleMais\Webhooks\WebHookAuthenticationType;
use Twila\ParceleMais\Webhooks\Webhook;
use Twila\ParceleMais\Webhooks\WebhookAudit;
use Twila\ParceleMais\Webhooks\WebHookType;

final class WebhookMapper
{
    /**
     * @param array<string, mixed> $wire
     */
    public static function auditToPublic(array $wire): WebhookAudit
    {
        return new WebhookAudit(
            $wire['id'],
            WebHookType::fromWireValue($wire['tipo']),
            $wire['requisicao'],
            $wire['resposta'],
            $wire['statusCode'],
            $wire['dataCriacao']
        );
    }

    /**
     * @param array<string, mixed> $wire
     */
    public static function toPublic(array $wire): Webhook
    {
        return new Webhook(
            WebHookType::fromWireValue($wire['tipo']),
            $wire['url'],
            WebHookAuthenticationType::fromWireValue($wire['tipoAutenticacao'])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function createRequestToWire(CreateWebhookRequest $request): array
    {
        return [
            'tipo' => $request->type,
            'url' => $request->url,
            'tipoAutenticacao' => $request->authenticationType,
            'credencial' => $request->credential,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function updateRequestToWire(UpdateWebhookRequest $request): array
    {
        return [
            'url' => $request->url,
            'tipoAutenticacao' => $request->authenticationType,
            'credencial' => $request->credential,
        ];
    }
}
