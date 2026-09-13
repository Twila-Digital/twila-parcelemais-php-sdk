<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Internal\Mapping;

use Twila\ParceleMais\Customers\Address;
use Twila\ParceleMais\Customers\Customer;

final class CustomerMapper
{
    /**
     * @param array<string, mixed> $wire
     */
    public static function toPublic(array $wire): Customer
    {
        $addressWire = $wire['endereco'] ?? null;

        return new Customer(
            $wire['id'],
            $wire['nome'],
            $wire['documento'],
            $wire['dataDeNascimento'],
            $addressWire !== null ? self::addressToPublic($addressWire) : null,
            $wire['email'] ?? null,
            $wire['celular'] ?? null
        );
    }

    /**
     * @param array<string, mixed> $wire
     */
    public static function addressToPublic(array $wire): Address
    {
        return new Address(
            $wire['rua'] ?? null,
            $wire['numero'] ?? null,
            $wire['bairro'] ?? null,
            $wire['cidade'] ?? null,
            $wire['estado'] ?? null,
            $wire['cep'] ?? null,
            $wire['pais'] ?? null,
            $wire['complemento'] ?? null
        );
    }
}
