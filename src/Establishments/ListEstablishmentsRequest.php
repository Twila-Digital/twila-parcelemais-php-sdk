<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Establishments;

final class ListEstablishmentsRequest
{
    /** @var string|null filtra pelo nome fantasia, busca parcial e sem diferenciar maiúsculas de minúsculas */
    public $tradeName;

    /** @var bool|null quando null, retorna lojas ativas e inativas */
    public $isActive;

    public function __construct(?string $tradeName = null, ?bool $isActive = null)
    {
        $this->tradeName = $tradeName;
        $this->isActive = $isActive;
    }
}
