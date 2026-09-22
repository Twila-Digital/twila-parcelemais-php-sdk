<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Establishments;

final class EstablishmentAddress
{
    /** @var string */
    public $street;

    /** @var string */
    public $number;

    /** @var string */
    public $district;

    /** @var string */
    public $city;

    /** @var string */
    public $state;

    /** @var string */
    public $zipCode;

    /** @var string|null */
    public $complement;

    /** @var string|null */
    public $country;

    public function __construct(
        string $street,
        string $number,
        string $district,
        string $city,
        string $state,
        string $zipCode,
        ?string $complement = null,
        ?string $country = null
    ) {
        $this->street = $street;
        $this->number = $number;
        $this->district = $district;
        $this->city = $city;
        $this->state = $state;
        $this->zipCode = $zipCode;
        $this->complement = $complement;
        $this->country = $country;
    }
}
