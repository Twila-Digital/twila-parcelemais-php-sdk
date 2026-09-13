<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Customers;

use Twila\ParceleMais\Internal\Http\ApiRequestExecutor;
use Twila\ParceleMais\Internal\Http\QueryStringBuilder;
use Twila\ParceleMais\Internal\Mapping\CustomerMapper;
use Twila\ParceleMais\Internal\Mapping\PagedMapper;
use Twila\ParceleMais\PagedResult;

final class CustomersClient
{
    /** @var ApiRequestExecutor */
    private $executor;

    public function __construct(ApiRequestExecutor $executor)
    {
        $this->executor = $executor;
    }

    public function get(string $customerId): Customer
    {
        $response = $this->executor->get('v1/customer/' . $customerId);
        ApiRequestExecutor::ensureSuccess($response);

        return CustomerMapper::toPublic($response->body);
    }

    public function list(?ListCustomersRequest $request = null): PagedResult
    {
        $request = $request ?? new ListCustomersRequest();

        $path = (new QueryStringBuilder())
            ->add('nome', $request->name)
            ->add('documento', $request->document)
            ->add('pagina', $request->page)
            ->add('tamanhoPagina', $request->pageSize)
            ->build('v1/customer/paged');

        $response = $this->executor->get($path);
        ApiRequestExecutor::ensureSuccess($response);

        return PagedMapper::fromWire($response->body, [CustomerMapper::class, 'toPublic']);
    }
}
