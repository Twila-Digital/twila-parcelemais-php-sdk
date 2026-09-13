<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Config;

final class Environment
{
    public const STAGING = 'staging';
    public const PRODUCTION = 'production';

    private const BASE_URLS = [
        self::STAGING => 'https://api.staging.parcelemais.com.br/integration/',
        self::PRODUCTION => 'https://api.parcelemais.com.br/integration/',
    ];

    public static function baseUrl(string $environment): string
    {
        return self::BASE_URLS[$environment];
    }
}
