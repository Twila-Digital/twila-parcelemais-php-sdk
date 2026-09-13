<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Orders;

final class InvoiceFile
{
    /** @var string */
    public $fileName;

    /** @var string */
    public $base64Content;

    public function __construct(string $fileName, string $base64Content)
    {
        $this->fileName = $fileName;
        $this->base64Content = $base64Content;
    }

    public static function fromString(string $content, string $fileName): self
    {
        return new self($fileName, base64_encode($content));
    }
}
