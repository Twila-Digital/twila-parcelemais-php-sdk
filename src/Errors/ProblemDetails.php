<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Errors;

final class ProblemDetails
{
    /** @var string|null */
    public $type;

    /** @var string|null */
    public $title;

    /** @var int|null */
    public $status;

    /** @var string|null */
    public $detail;

    /** @var string|null */
    public $instance;

    /** @var array<string, array<int, string>>|null */
    public $errors;

    /** @var string|null */
    public $correlationId;

    /**
     * @param array<string, array<int, string>>|null $errors
     */
    public function __construct(
        ?string $type = null,
        ?string $title = null,
        ?int $status = null,
        ?string $detail = null,
        ?string $instance = null,
        ?array $errors = null,
        ?string $correlationId = null
    ) {
        $this->type = $type;
        $this->title = $title;
        $this->status = $status;
        $this->detail = $detail;
        $this->instance = $instance;
        $this->errors = $errors;
        $this->correlationId = $correlationId;
    }

    /**
     * @param mixed $body
     */
    public static function parse($body): self
    {
        if (!is_array($body)) {
            return new self();
        }

        $errors = $body['erros'] ?? null;

        return new self(
            isset($body['tipo']) && is_string($body['tipo']) ? $body['tipo'] : null,
            isset($body['titulo']) && is_string($body['titulo']) ? $body['titulo'] : null,
            isset($body['status']) && is_int($body['status']) ? $body['status'] : null,
            isset($body['detalhe']) && is_string($body['detalhe']) ? $body['detalhe'] : null,
            isset($body['instancia']) && is_string($body['instancia']) ? $body['instancia'] : null,
            is_array($errors) ? $errors : null,
            isset($body['correlationId']) && is_string($body['correlationId']) ? $body['correlationId'] : null
        );
    }
}
