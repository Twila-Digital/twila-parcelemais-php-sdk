<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Establishments;

use Twila\ParceleMais\Internal\Http\ApiRequestExecutor;
use Twila\ParceleMais\Internal\Http\QueryStringBuilder;
use Twila\ParceleMais\Internal\Mapping\EstablishmentMapper;

final class EstablishmentsClient
{
    /** @var ApiRequestExecutor */
    private $executor;

    public function __construct(ApiRequestExecutor $executor)
    {
        $this->executor = $executor;
    }

    public function create(CreateEstablishmentRequest $request): CreateEstablishmentResult
    {
        $wireRequest = EstablishmentMapper::createRequestToWire($request);
        $response = $this->executor->post('v1/establishment', $wireRequest);
        ApiRequestExecutor::ensureSuccess($response);

        return new CreateEstablishmentResult($response->body['estabelecimentoId']);
    }

    public function get(string $establishmentId): Establishment
    {
        $response = $this->executor->get('v1/establishment/' . $establishmentId);
        ApiRequestExecutor::ensureSuccess($response);

        return EstablishmentMapper::toPublic($response->body);
    }

    /**
     * @return Establishment[]
     */
    public function list(?ListEstablishmentsRequest $request = null): array
    {
        $request = $request ?? new ListEstablishmentsRequest();

        $path = (new QueryStringBuilder())
            ->add('nomeFantasia', $request->tradeName)
            ->add('ativa', $request->isActive === null ? null : ($request->isActive ? 'true' : 'false'))
            ->build('v1/establishment/list');

        $response = $this->executor->get($path);
        ApiRequestExecutor::ensureSuccess($response);

        return array_map([EstablishmentMapper::class, 'toPublic'], $response->body);
    }

    public function update(string $establishmentId, UpdateEstablishmentRequest $request): void
    {
        $wireRequest = EstablishmentMapper::updateRequestToWire($request);
        $response = $this->executor->put('v1/establishment/' . $establishmentId, $wireRequest);
        ApiRequestExecutor::ensureSuccess($response);
    }

    public function updateBankAccount(string $establishmentId, EstablishmentBankAccount $bankAccount): void
    {
        $wireRequest = EstablishmentMapper::bankAccountToWire($bankAccount);
        $response = $this->executor->put('v1/establishment/' . $establishmentId . '/bank-account', $wireRequest);
        ApiRequestExecutor::ensureSuccess($response);
    }

    public function activate(string $establishmentId): void
    {
        $this->setActive($establishmentId, true);
    }

    public function deactivate(string $establishmentId): void
    {
        $this->setActive($establishmentId, false);
    }

    private function setActive(string $establishmentId, bool $isActive): void
    {
        $response = $this->executor->put('v1/establishment/' . $establishmentId . '/status', ['ativa' => $isActive]);
        ApiRequestExecutor::ensureSuccess($response);
    }
}
