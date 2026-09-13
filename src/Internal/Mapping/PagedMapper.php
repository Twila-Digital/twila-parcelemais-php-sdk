<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Internal\Mapping;

use Twila\ParceleMais\PagedResult;

final class PagedMapper
{
    /**
     * @param array<string, mixed> $wire
     * @param callable(array<string, mixed>): mixed $itemMapper
     */
    public static function fromWire(array $wire, callable $itemMapper): PagedResult
    {
        $pagina = $wire['pagina'];

        return new PagedResult(
            array_map($itemMapper, $wire['itens']),
            $pagina['tem_proximo'],
            $pagina['tem_anterior'],
            $pagina['numero'],
            $pagina['tamanho'],
            $pagina['total']
        );
    }
}
