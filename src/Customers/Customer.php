<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Customers;

final class Customer
{
    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $document;

    /** @var string */
    public $dateOfBirth;

    /** @var Address|null */
    public $address;

    /** @var string|null */
    public $email;

    /** @var string|null */
    public $phoneNumber;

    public function __construct(
        string $id,
        string $name,
        string $document,
        string $dateOfBirth,
        ?Address $address = null,
        ?string $email = null,
        ?string $phoneNumber = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->document = $document;
        $this->dateOfBirth = $dateOfBirth;
        $this->address = $address;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
    }
}
