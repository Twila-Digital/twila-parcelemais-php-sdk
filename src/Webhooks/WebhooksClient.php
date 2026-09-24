<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Webhooks;

use Twila\ParceleMais\Internal\Http\ApiRequestExecutor;
use Twila\ParceleMais\Internal\Http\QueryStringBuilder;
use Twila\ParceleMais\Internal\Mapping\PagedMapper;
use Twila\ParceleMais\Internal\Mapping\WebhookMapper;
use Twila\ParceleMais\PagedResult;

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

    /**
     * Auditoria paginada das entregas de webhook — um registro por tentativa, mais recentes primeiro.
     * Os itens do PagedResult são instâncias de WebhookAudit.
     */
    public function listAudit(?ListWebhookAuditRequest $request = null): PagedResult
    {
        $request = $request ?? new ListWebhookAuditRequest();

        $path = (new QueryStringBuilder())
            ->add('dataInicio', $request->startDate)
            ->add('dataFim', $request->endDate)
            ->add('pedidoId', $request->orderId)
            ->add('numeroPedido', $request->orderNumber)
            ->add('statusCode', $request->statusCode)
            ->add('pagina', $request->page)
            ->add('tamanhoPagina', $request->pageSize)
            ->build('v1/webhooks/auditoria');

        $response = $this->executor->get($path);
        ApiRequestExecutor::ensureSuccess($response);

        return PagedMapper::fromWire($response->body, [WebhookMapper::class, 'auditToPublic']);
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
