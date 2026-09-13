<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Tests;

use PHPUnit\Framework\TestCase;
use Twila\ParceleMais\Errors\ExceptionFactory;
use Twila\ParceleMais\Errors\ParceleMaisApiException;
use Twila\ParceleMais\Errors\ParceleMaisAuthenticationException;
use Twila\ParceleMais\Errors\ParceleMaisRateLimitException;
use Twila\ParceleMais\Errors\ParceleMaisValidationException;
use Twila\ParceleMais\Internal\Http\ApiResponse;

final class ErrorsTest extends TestCase
{
    public function test401MapsToAuthenticationException(): void
    {
        $error = ExceptionFactory::fromResponse(new ApiResponse(401, ['detalhe' => 'token inválido'], []));

        self::assertInstanceOf(ParceleMaisAuthenticationException::class, $error);
        self::assertSame('token inválido', $error->getMessage());
    }

    public function test400WithFieldErrorsMapsToValidationException(): void
    {
        $error = ExceptionFactory::fromResponse(new ApiResponse(400, [
            'detalhe' => 'campos inválidos',
            'erros' => ['cpf' => ['obrigatório']],
        ], []));

        self::assertInstanceOf(ParceleMaisValidationException::class, $error);
        self::assertSame(400, $error->getStatusCode());
        self::assertSame(['cpf' => ['obrigatório']], $error->getFieldErrors());
    }

    public function test400WithoutFieldErrorsMapsToGenericApiException(): void
    {
        $error = ExceptionFactory::fromResponse(new ApiResponse(400, ['detalhe' => 'bad request'], []));

        self::assertSame(ParceleMaisApiException::class, get_class($error));
        self::assertSame(400, $error->getStatusCode());
    }

    public function test429MapsToRateLimitExceptionWithRetryAfter(): void
    {
        $error = ExceptionFactory::fromResponse(
            new ApiResponse(429, ['detalhe' => 'muitas requisições'], ['retry-after' => '2'])
        );

        self::assertInstanceOf(ParceleMaisRateLimitException::class, $error);
        self::assertSame(2000, $error->getRetryAfterMs());
    }

    public function testUnmappedStatusMapsToGenericApiException(): void
    {
        $error = ExceptionFactory::fromResponse(new ApiResponse(500, ['detalhe' => 'erro interno'], []));

        self::assertSame(ParceleMaisApiException::class, get_class($error));
        self::assertSame(500, $error->getStatusCode());
    }

    public function testErrorCodeReadsProblemDetailsType(): void
    {
        $error = ExceptionFactory::fromResponse(
            new ApiResponse(404, ['tipo' => 'pedido-nao-encontrado', 'detalhe' => 'não encontrado'], [])
        );

        self::assertSame('pedido-nao-encontrado', $error->getErrorCode());
    }

    public function testMessageFallsBackToGenericWhenNoProblemDetails(): void
    {
        $error = ExceptionFactory::fromResponse(new ApiResponse(503, null, []));

        self::assertStringContainsString('503', $error->getMessage());
    }
}
