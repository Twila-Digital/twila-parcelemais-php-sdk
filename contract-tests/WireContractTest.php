<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * Testes de contrato contra o OpenAPI real de staging. Não roda a cada PR — só agendado
 * e no pipeline de release (ver contract-tests.yml).
 */
final class WireContractTest extends TestCase
{
    private const SWAGGER_URL = 'https://api.staging.parcelemais.com.br/integration/swagger/v1/swagger.json';

    private const EXPECTED_PATHS = [
        '/v1/authentication/accesstoken',
        '/v1/order',
        '/v1/order/{id}',
        '/v1/order/paged',
        '/v1/order/start-cdc-sale',
        '/v1/order/invoice',
        '/v1/order/simulate-installments',
        '/v1/order/simulate-values',
        '/v1/customer/{id}',
        '/v1/customer/paged',
        '/v1/webhooks',
        '/v1/webhooks/{type}',
        '/v1/webhooks/auditoria',
    ];

    /** @var array<string, mixed>|null */
    private static $schema;

    /**
     * @return array<string, mixed>
     */
    private static function fetchStagingSchema(): array
    {
        if (self::$schema !== null) {
            return self::$schema;
        }

        $client = new Client(['timeout' => 30]);
        $response = $client->get(self::SWAGGER_URL);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(sprintf(
                'Falha ao buscar o swagger.json de staging: HTTP %d',
                $response->getStatusCode()
            ));
        }

        self::$schema = json_decode((string) $response->getBody(), true);

        return self::$schema;
    }

    private static function normalize(string $templatePath): string
    {
        return (string) preg_replace('/\{[^}]+}/', '', $templatePath);
    }

    private static function simpleTypeName(string $schemaKey): string
    {
        $lastDot = strrpos($schemaKey, '.');

        return $lastDot === false ? $schemaKey : substr($schemaKey, $lastDot + 1);
    }

    /**
     * @dataProvider expectedPathsProvider
     */
    public function testEndpointExistsInStagingSchema(string $expectedPath): void
    {
        $schema = self::fetchStagingSchema();
        $paths = array_keys($schema['paths'] ?? []);

        $matches = false;
        foreach ($paths as $realPath) {
            if (self::endsWith(self::normalize($realPath), self::normalize($expectedPath))) {
                $matches = true;
                break;
            }
        }

        self::assertTrue(
            $matches,
            sprintf("Endpoint '%s' não encontrado no swagger.json de staging — o SDK e o backend divergiram.", $expectedPath)
        );
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function expectedPathsProvider(): array
    {
        $cases = [];
        foreach (self::EXPECTED_PATHS as $path) {
            $cases[$path] = [$path];
        }

        return $cases;
    }

    public function testOrderResponseSchemaHasFieldsOrderMapperExpects(): void
    {
        $schema = self::fetchStagingSchema();
        $schemas = $schema['components']['schemas'] ?? [];

        $orderSchema = null;
        foreach ($schemas as $key => $value) {
            if (self::simpleTypeName((string) $key) === 'OrderIntegrationResponse') {
                $orderSchema = $value;
                break;
            }
        }

        self::assertNotNull($orderSchema, 'Não encontrei o schema OrderIntegrationResponse no swagger.json de staging.');

        $properties = $orderSchema['properties'] ?? [];

        foreach (['id', 'numero', 'status', 'documentoCliente', 'criadoEm'] as $expectedField) {
            self::assertArrayHasKey(
                $expectedField,
                $properties,
                sprintf("Campo '%s' esperado pelo mapper não existe (mais) no schema de staging.", $expectedField)
            );
        }
    }

    private static function endsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);

        return $length === 0 || substr($haystack, -$length) === $needle;
    }
}
