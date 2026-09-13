<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Orders;

use Twila\ParceleMais\Internal\Http\ApiRequestExecutor;
use Twila\ParceleMais\Internal\Http\QueryStringBuilder;
use Twila\ParceleMais\Internal\Mapping\OrderMapper;
use Twila\ParceleMais\Internal\Mapping\PagedMapper;
use Twila\ParceleMais\PagedResult;

final class OrdersClient
{
    /** @var ApiRequestExecutor */
    private $executor;

    /** @var int */
    private $invoiceUploadAttemptTimeoutMs;

    public function __construct(ApiRequestExecutor $executor, int $invoiceUploadAttemptTimeoutMs)
    {
        $this->executor = $executor;
        $this->invoiceUploadAttemptTimeoutMs = $invoiceUploadAttemptTimeoutMs;
    }

    public function create(CreateOrderRequest $request): string
    {
        $wireRequest = OrderMapper::createRequestToWire($request);
        $response = $this->executor->post('v1/order', $wireRequest);
        ApiRequestExecutor::ensureSuccess($response);

        return (string) $response->body['pedidoId'];
    }

    public function get(string $orderId): Order
    {
        $response = $this->executor->get('v1/order/' . $orderId);
        ApiRequestExecutor::ensureSuccess($response);

        return OrderMapper::toPublic($response->body);
    }

    public function list(?ListOrdersRequest $request = null): PagedResult
    {
        $request = $request ?? new ListOrdersRequest();

        $path = (new QueryStringBuilder())
            ->add('status', $request->status)
            ->add('documentoCliente', $request->customerDocument)
            ->add('dataInicio', $request->startDate)
            ->add('dataFim', $request->endDate)
            ->add('numero', $request->number)
            ->add('documentoLoja', $request->establishmentDocument)
            ->add('descricao', $request->description)
            ->add('pagina', $request->page)
            ->add('tamanhoPagina', $request->pageSize)
            ->build('v1/order/paged');

        $response = $this->executor->get($path);
        ApiRequestExecutor::ensureSuccess($response);

        return PagedMapper::fromWire($response->body, [OrderMapper::class, 'toPublic']);
    }

    public function startCdcSale(string $orderId): CheckoutLink
    {
        $response = $this->executor->post('v1/order/start-cdc-sale', ['pedidoId' => $orderId]);
        ApiRequestExecutor::ensureSuccess($response);

        return new CheckoutLink($response->body['linkPagamento'] ?? null);
    }

    public function importInvoice(string $orderId, InvoiceFile $file): void
    {
        $wireRequest = [
            'pedidoId' => $orderId,
            'arquivoBase64' => $file->base64Content,
            'nomeArquivo' => $file->fileName,
        ];

        $response = $this->executor->post('v1/order/invoice', $wireRequest, $this->invoiceUploadAttemptTimeoutMs);
        ApiRequestExecutor::ensureSuccess($response);
    }
}
