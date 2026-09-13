<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Orders;

final class CheckoutLink
{
    /** @var string|null */
    public $url;

    public function __construct(?string $url = null)
    {
        $this->url = $url;
    }
}
