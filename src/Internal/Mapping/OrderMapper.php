<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Internal\Mapping;

use Twila\ParceleMais\Orders\Address;
use Twila\ParceleMais\Orders\CreateOrderRequest;
use Twila\ParceleMais\Orders\Order;
use Twila\ParceleMais\Orders\OrderStatus;

final class OrderMapper
{
    /**
     * @param array<string, mixed> $wire
     */
    public static function toPublic(array $wire): Order
    {
        $status = $wire['status'];

        return new Order(
            $wire['id'],
            $wire['numero'],
            OrderStatus::fromWireValue($status['valor']),
            $status['descricao'],
            $wire['documentoCliente'],
            $wire['razaoSocialEstabelecimento'],
            $wire['documentoEstabelecimento'],
            $wire['criadoEm'],
            $wire['total'] ?? null,
            $wire['nomeCliente'] ?? null,
            $wire['prazo'] ?? null,
            $wire['descricao'] ?? null,
            $wire['valorAprovado'] ?? null,
            $wire['desembolsado'] ?? null,
            $wire['desembolsadoEm'] ?? null,
            $wire['valorSolicitado'] ?? null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function addressToWire(Address $address): array
    {
        return [
            'logradouro' => $address->street,
            'numero' => $address->number,
            'bairro' => $address->neighborhood,
            'cidade' => $address->city,
            'estado' => $address->state,
            'cep' => $address->postalCode,
            'complemento' => $address->complement,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function createRequestToWire(CreateOrderRequest $request): array
    {
        return [
            'cpf' => $request->cpf,
            'celular' => $request->phoneNumber,
            'documentoEstabelecimento' => $request->establishmentDocument,
            'valorSolicitado' => $request->requestedAmount,
            'nome' => $request->name,
            'email' => $request->email,
            'dataDeNascimento' => $request->dateOfBirth,
            'endereco' => self::addressToWire($request->address),
        ];
    }
}
