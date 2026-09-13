<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Tests;

use PHPUnit\Framework\TestCase;
use Twila\ParceleMais\Config\ClientOptions;
use Twila\ParceleMais\Config\ClientOptionsResolver;
use Twila\ParceleMais\Config\Environment;
use Twila\ParceleMais\Config\ResilienceOptions;
use Twila\ParceleMais\Errors\ParceleMaisConfigurationException;

final class ConfigTest extends TestCase
{
    public function testResolveDefaultsToProductionBaseUrl(): void
    {
        $resolved = ClientOptionsResolver::resolve(new ClientOptions('id', 'secret'));

        self::assertSame(Environment::baseUrl(Environment::PRODUCTION), $resolved->baseUrl);
    }

    public function testResolveNormalizesBaseUrlWithoutTrailingSlash(): void
    {
        $resolved = ClientOptionsResolver::resolve(
            new ClientOptions('id', 'secret', Environment::PRODUCTION, 'https://example.com/integration')
        );

        self::assertSame('https://example.com/integration/', $resolved->baseUrl);
    }

    /**
     * @dataProvider blankCredentialsProvider
     */
    public function testBlankCredentialsRaiseConfigurationException(string $clientId, string $clientSecret): void
    {
        $this->expectException(ParceleMaisConfigurationException::class);
        ClientOptionsResolver::resolve(new ClientOptions($clientId, $clientSecret));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function blankCredentialsProvider(): array
    {
        return [
            'blank client id' => ['', 'secret'],
            'blank client secret' => ['id', ''],
            'whitespace client id' => ['   ', 'secret'],
        ];
    }

    public function testInvalidBaseUrlRaisesConfigurationException(): void
    {
        $this->expectException(ParceleMaisConfigurationException::class);
        ClientOptionsResolver::resolve(new ClientOptions('id', 'secret', Environment::PRODUCTION, 'not-a-url'));
    }

    /**
     * @dataProvider invalidResilienceOptionsProvider
     */
    public function testInvalidResilienceOptionsRaiseConfigurationException(ResilienceOptions $resilience): void
    {
        $this->expectException(ParceleMaisConfigurationException::class);
        ClientOptionsResolver::resolve(new ClientOptions('id', 'secret', Environment::PRODUCTION, null, $resilience));
    }

    /**
     * @return array<string, array{0: ResilienceOptions}>
     */
    public static function invalidResilienceOptionsProvider(): array
    {
        return [
            'zero retries' => [new ResilienceOptions(30000, 10000, 60000, 0)],
            'zero total timeout' => [new ResilienceOptions(0)],
            'zero attempt timeout' => [new ResilienceOptions(30000, 0)],
            'attempt timeout above total' => [new ResilienceOptions(10000, 20000)],
            'failure ratio zero' => [new ResilienceOptions(30000, 10000, 60000, 3, 500, 0.0)],
            'failure ratio above 1' => [new ResilienceOptions(30000, 10000, 60000, 3, 500, 1.5)],
            'minimum throughput below 2' => [new ResilienceOptions(30000, 10000, 60000, 3, 500, 0.5, 30000, 1)],
        ];
    }
}
