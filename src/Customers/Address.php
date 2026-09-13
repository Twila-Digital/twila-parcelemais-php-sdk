<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Customers;

final class Address
{
    /** @var string|null */
    public $street;

    /** @var string|null */
    public $number;

    /** @var string|null */
    public $neighborhood;

    /** @var string|null */
    public $city;

    /** @var string|null */
    public $state;

    /** @var string|null */
    public $postalCode;

    /** @var string|null */
    public $country;

    /** @var string|null */
    public $complement;

    public function __construct(
        ?string $street = null,
        ?string $number = null,
        ?string $neighborhood = null,
        ?string $city = null,
        ?string $state = null,
        ?string $postalCode = null,
        ?string $country = null,
        ?string $complement = null
    ) {
        $this->street = $street;
        $this->number = $number;
        $this->neighborhood = $neighborhood;
        $this->city = $city;
        $this->state = $state;
        $this->postalCode = $postalCode;
        $this->country = $country;
        $this->complement = $complement;
    }
}
