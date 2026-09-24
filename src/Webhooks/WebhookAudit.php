<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Webhooks;

/**
 * Registro de uma tentativa de entrega de webhook (uma linha por tentativa).
 */
final class WebhookAudit
{
    /** @var string */
    public $id;

    /** @var int um dos WebHookType::* */
    public $type;

    /** @var string corpo enviado ao endpoint do parceiro */
    public $request;

    /** @var string corpo devolvido pelo endpoint do parceiro */
    public $response;

    /** @var int */
    public $statusCode;

    /** @var string */
    public $createdAt;

    public function __construct(
        string $id,
        int $type,
        string $request,
        string $response,
        int $statusCode,
        string $createdAt
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->request = $request;
        $this->response = $response;
        $this->statusCode = $statusCode;
        $this->createdAt = $createdAt;
    }
}
