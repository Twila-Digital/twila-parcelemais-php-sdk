<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Orders;

final class CreateOrderRequest
{
    /** @var string */
    public $cpf;

    /** @var string */
    public $phoneNumber;

    /** @var string */
    public $establishmentDocument;

    /** @var float */
    public $requestedAmount;

    /** @var string */
    public $name;

    /** @var string */
    public $email;

    /** @var string */
    public $dateOfBirth;

    /** @var Address */
    public $address;

    public function __construct(
        string $cpf,
        string $phoneNumber,
        string $establishmentDocument,
        float $requestedAmount,
        string $name,
        string $email,
        string $dateOfBirth,
        Address $address
    ) {
        $this->cpf = $cpf;
        $this->phoneNumber = $phoneNumber;
        $this->establishmentDocument = $establishmentDocument;
        $this->requestedAmount = $requestedAmount;
        $this->name = $name;
        $this->email = $email;
        $this->dateOfBirth = $dateOfBirth;
        $this->address = $address;
    }
}
