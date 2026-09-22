<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Establishments;

final class CreateEstablishmentResult
{
    /** @var string */
    public $establishmentId;

    public function __construct(string $establishmentId)
    {
        $this->establishmentId = $establishmentId;
    }
}
