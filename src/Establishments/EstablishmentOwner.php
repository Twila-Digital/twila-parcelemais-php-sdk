<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Establishments;

final class EstablishmentOwner
{
    /** @var string */
    public $name;

    /** @var string */
    public $email;

    /** @var string celular no formato E.164 (código do país + DDD + número). Ex.: +5511999998888 */
    public $phone;

    public function __construct(string $name, string $email, string $phone)
    {
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
    }
}
