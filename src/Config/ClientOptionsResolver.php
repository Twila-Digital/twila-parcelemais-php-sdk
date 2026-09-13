<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Config;

use Twila\ParceleMais\Errors\ParceleMaisConfigurationException;

final class ClientOptionsResolver
{
    public static function resolve(ClientOptions $options): ResolvedClientOptions
    {
        self::validate($options);

        $baseUrl = self::resolveBaseUrl($options);

        return new ResolvedClientOptions($options->clientId, $options->clientSecret, $baseUrl, $options->resilience);
    }

    private static function resolveBaseUrl(ClientOptions $options): string
    {
        $raw = $options->baseUrl ?? Environment::baseUrl($options->environment);

        return substr($raw, -1) === '/' ? $raw : $raw . '/';
    }

    private static function validate(ClientOptions $options): void
    {
        if (self::isBlank($options->clientId)) {
            throw new ParceleMaisConfigurationException('clientId é obrigatório.');
        }

        if (self::isBlank($options->clientSecret)) {
            throw new ParceleMaisConfigurationException('clientSecret é obrigatório.');
        }

        if ($options->baseUrl !== null && filter_var($options->baseUrl, FILTER_VALIDATE_URL) === false) {
            throw new ParceleMaisConfigurationException('baseUrl, quando informada, deve ser uma URL absoluta válida.');
        }

        $resilience = $options->resilience;

        if ($resilience->maxRetryAttempts < 1) {
            throw new ParceleMaisConfigurationException('resilience.maxRetryAttempts deve ser maior ou igual a 1.');
        }

        if ($resilience->totalTimeoutMs <= 0) {
            throw new ParceleMaisConfigurationException('resilience.totalTimeoutMs deve ser maior que zero.');
        }

        if ($resilience->attemptTimeoutMs <= 0) {
            throw new ParceleMaisConfigurationException('resilience.attemptTimeoutMs deve ser maior que zero.');
        }

        if ($resilience->attemptTimeoutMs > $resilience->totalTimeoutMs) {
            throw new ParceleMaisConfigurationException(
                'resilience.attemptTimeoutMs não pode ser maior que resilience.totalTimeoutMs.'
            );
        }

        if ($resilience->circuitBreakerFailureRatio <= 0 || $resilience->circuitBreakerFailureRatio > 1) {
            throw new ParceleMaisConfigurationException(
                'resilience.circuitBreakerFailureRatio deve estar entre 0 (exclusivo) e 1 (inclusivo).'
            );
        }

        if ($resilience->circuitBreakerMinimumThroughput < 2) {
            throw new ParceleMaisConfigurationException(
                'resilience.circuitBreakerMinimumThroughput deve ser maior ou igual a 2.'
            );
        }
    }

    private static function isBlank(?string $value): bool
    {
        return $value === null || trim($value) === '';
    }
}
