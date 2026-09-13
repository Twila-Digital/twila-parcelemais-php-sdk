<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Webhooks;

use Twila\ParceleMais\Internal\Http\ApiRequestExecutor;
use Twila\ParceleMais\Internal\Mapping\WebhookMapper;

final class WebhooksClient
{
    /** @var ApiRequestExecutor */
    private $executor;

    public function __construct(ApiRequestExecutor $executor)
    {
        $this->executor = $executor;
    }

    public function create(CreateWebhookRequest $request): CreateWebhookResult
    {
        $wireRequest = WebhookMapper::createRequestToWire($request);
        $response = $this->executor->post('v1/webhooks', $wireRequest);
        ApiRequestExecutor::ensureSuccess($response);

        return new CreateWebhookResult($response->body['chaveAssinatura']);
    }

    /**
     * @return array<int, Webhook>
     */
    public function list(): array
    {
        $response = $this->executor->get('v1/webhooks');
        ApiRequestExecutor::ensureSuccess($response);

        return array_map([WebhookMapper::class, 'toPublic'], $response->body);
    }

    public function update(int $type, UpdateWebhookRequest $request): void
    {
        $wireRequest = WebhookMapper::updateRequestToWire($request);
        $response = $this->executor->put('v1/webhooks/' . $type, $wireRequest);
        ApiRequestExecutor::ensureSuccess($response);
    }

    public function delete(int $type): void
    {
        $response = $this->executor->delete('v1/webhooks/' . $type);
        ApiRequestExecutor::ensureSuccess($response);
    }
}
