<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Internal\Mapping;

use Twila\ParceleMais\Establishments\CreateEstablishmentRequest;
use Twila\ParceleMais\Establishments\Establishment;
use Twila\ParceleMais\Establishments\EstablishmentAddress;
use Twila\ParceleMais\Establishments\EstablishmentBankAccount;
use Twila\ParceleMais\Establishments\EstablishmentOwner;
use Twila\ParceleMais\Establishments\UpdateEstablishmentRequest;

final class EstablishmentMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function createRequestToWire(CreateEstablishmentRequest $request): array
    {
        return [
            'documento' => $request->document,
            'razaoSocial' => $request->legalName,
            'nomeFantasia' => $request->tradeName,
            'modeloDesembolso' => $request->disbursementModel,
            'responsavel' => [
                'nome' => $request->owner->name,
                'email' => $request->owner->email,
                'celular' => $request->owner->phone,
            ],
            'contaBancaria' => self::bankAccountToWire($request->bankAccount),
            'endereco' => $request->address === null ? null : self::addressToWire($request->address),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function updateRequestToWire(UpdateEstablishmentRequest $request): array
    {
        return [
            'nomeFantasia' => $request->tradeName,
            'modeloDesembolso' => $request->disbursementModel,
            'endereco' => $request->address === null ? null : self::addressToWire($request->address),
        ];
    }

    /**
     * @param array<string, mixed> $wire
     */
    public static function toPublic(array $wire): Establishment
    {
        $bankAccountWire = $wire['contaBancaria'] ?? null;
        $addressWire = $wire['endereco'] ?? null;

        return new Establishment(
            $wire['estabelecimentoId'],
            $wire['documento'],
            $wire['razaoSocial'],
            $wire['nomeFantasia'],
            $wire['ativa'],
            new EstablishmentOwner(
                $wire['responsavel']['nome'],
                $wire['responsavel']['email'],
                $wire['responsavel']['celular']
            ),
            $wire['modeloDesembolso'] ?? null,
            $bankAccountWire !== null ? self::bankAccountToPublic($bankAccountWire) : null,
            $addressWire !== null ? self::addressToPublic($addressWire) : null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function bankAccountToWire(EstablishmentBankAccount $bankAccount): array
    {
        return [
            'banco' => $bankAccount->bankNumber,
            'agencia' => $bankAccount->agencyNumber,
            'digitoAgencia' => $bankAccount->agencyDigit ?? '',
            'conta' => $bankAccount->accountNumber,
            'digitoConta' => $bankAccount->accountDigit,
            'tipoConta' => $bankAccount->accountType,
            'nomeTitular' => $bankAccount->holderName,
            'documentoTitular' => $bankAccount->holderDocument,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function addressToWire(EstablishmentAddress $address): array
    {
        return [
            'rua' => $address->street,
            'numero' => $address->number,
            'complemento' => $address->complement,
            'bairro' => $address->district,
            'cidade' => $address->city,
            'estado' => $address->state,
            'cep' => $address->zipCode,
            'pais' => $address->country,
        ];
    }

    /**
     * @param array<string, mixed> $wire
     */
    private static function bankAccountToPublic(array $wire): EstablishmentBankAccount
    {
        return new EstablishmentBankAccount(
            $wire['banco'],
            $wire['agencia'],
            $wire['conta'],
            $wire['digitoConta'],
            $wire['tipoConta'],
            $wire['digitoAgencia'] ?? null,
            $wire['nomeTitular'] ?? null,
            $wire['documentoTitular'] ?? null
        );
    }

    /**
     * @param array<string, mixed> $wire
     */
    private static function addressToPublic(array $wire): EstablishmentAddress
    {
        return new EstablishmentAddress(
            $wire['rua'],
            $wire['numero'],
            $wire['bairro'],
            $wire['cidade'],
            $wire['estado'],
            $wire['cep'],
            $wire['complemento'] ?? null,
            $wire['pais'] ?? null
        );
    }
}
