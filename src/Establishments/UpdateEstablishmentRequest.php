<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Establishments;

final class UpdateEstablishmentRequest
{
    /** @var string */
    public $tradeName;

    /** @var int|null quando null, mantém o modelo atual */
    public $disbursementModel;

    /** @var EstablishmentAddress|null quando null, mantém o endereço atual */
    public $address;

    public function __construct(
        string $tradeName,
        ?int $disbursementModel = null,
        ?EstablishmentAddress $address = null
    ) {
        $this->tradeName = $tradeName;
        $this->disbursementModel = $disbursementModel;
        $this->address = $address;
    }
}
