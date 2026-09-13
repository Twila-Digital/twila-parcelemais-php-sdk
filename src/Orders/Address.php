<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Orders;

final class Address
{
    /** @var string */
    public $street;

    /** @var string */
    public $number;

    /** @var string */
    public $neighborhood;

    /** @var string */
    public $city;

    /** @var string */
    public $state;

    /** @var string */
    public $postalCode;

    /** @var string|null */
    public $complement;

    public function __construct(
        string $street,
        string $number,
        string $neighborhood,
        string $city,
        string $state,
        string $postalCode,
        ?string $complement = null
    ) {
        $this->street = $street;
        $this->number = $number;
        $this->neighborhood = $neighborhood;
        $this->city = $city;
        $this->state = $state;
        $this->postalCode = $postalCode;
        $this->complement = $complement;
    }
}
