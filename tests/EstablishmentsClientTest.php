<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Tests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Twila\ParceleMais\Errors\ParceleMaisApiException;
use Twila\ParceleMais\Establishments\BankAccountType;
use Twila\ParceleMais\Establishments\CreateEstablishmentRequest;
use Twila\ParceleMais\Establishments\DisbursementModel;
use Twila\ParceleMais\Establishments\EstablishmentAddress;
use Twila\ParceleMais\Establishments\EstablishmentBankAccount;
use Twila\ParceleMais\Establishments\EstablishmentOwner;
use Twila\ParceleMais\Establishments\ListEstablishmentsRequest;
use Twila\ParceleMais\Establishments\UpdateEstablishmentRequest;

final class EstablishmentsClientTest extends TestCase
{
    private const ESTABLISHMENT_ID = 'establishment-1';

    private const ESTABLISHMENT_WIRE = [
        'estabelecimentoId' => 'establishment-1',
        'documento' => '12345678000199',
        'razaoSocial' => 'Loja Centro LTDA',
        'nomeFantasia' => 'Loja Centro',
        'ativa' => true,
        'modeloDesembolso' => 1,
        'responsavel' => [
            'nome' => 'Maria Souza',
            'email' => 'maria@loja.com.br',
            'celular' => '+5511999998888',
        ],
        'contaBancaria' => [
            'banco' => '341',
            'agencia' => '1234',
            'digitoAgencia' => '',
            'conta' => '56789',
            'digitoConta' => '0',
            'tipoConta' => 1,
            'nomeTitular' => null,
            'documentoTitular' => null,
        ],
        'endereco' => [
            'rua' => 'Rua Exemplo',
            'numero' => '100',
            'complemento' => null,
            'bairro' => 'Centro',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '01310100',
            'pais' => 'Brasil',
        ],
    ];

    private static function newCreateRequest(?EstablishmentAddress $address = null): CreateEstablishmentRequest
    {
        return new CreateEstablishmentRequest(
            '12345678000199',
            'Loja Centro LTDA',
            'Loja Centro',
            DisbursementModel::ESTABLISHMENT_CHAIN,
            new EstablishmentOwner('Maria Souza', 'maria@loja.com.br', '+5511999998888'),
            new EstablishmentBankAccount('341', '1234', '56789', '0', BankAccountType::CURRENT),
            $address
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function decodeLastBody(\GuzzleHttp\Handler\MockHandler $mock): array
    {
        $request = $mock->getLastRequest();
        self::assertNotNull($request);

        /** @var array<string, mixed> $body */
        $body = json_decode((string) $request->getBody(), true);

        return $body;
    }

    public function testCreateSendsWireBodyAndReturnsId(): void
    {
        [$client, $mock] = TestClientFactory::withMockHandler([
            new Response(200, [], (string) json_encode(['estabelecimentoId' => self::ESTABLISHMENT_ID])),
        ]);

        $result = $client->establishments->create(self::newCreateRequest(
            new EstablishmentAddress('Rua Exemplo', '100', 'Centro', 'São Paulo', 'SP', '01310100')
        ));

        self::assertSame(self::ESTABLISHMENT_ID, $result->establishmentId);

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('POST', $request->getMethod());
        self::assertStringEndsWith('v1/establishment', $request->getUri()->getPath());

        $body = self::decodeLastBody($mock);
        self::assertSame('12345678000199', $body['documento']);
        self::assertSame('Loja Centro LTDA', $body['razaoSocial']);
        self::assertSame(1, $body['modeloDesembolso']);
        self::assertIsArray($body['responsavel']);
        self::assertSame('+5511999998888', $body['responsavel']['celular']);
        self::assertIsArray($body['contaBancaria']);
        self::assertSame(1, $body['contaBancaria']['tipoConta']);
        self::assertIsArray($body['endereco']);
        self::assertSame('01310100', $body['endereco']['cep']);
    }

    public function testCreateWithoutAddressSendsNull(): void
    {
        [$client, $mock] = TestClientFactory::withMockHandler([
            new Response(200, [], (string) json_encode(['estabelecimentoId' => self::ESTABLISHMENT_ID])),
        ]);

        $client->establishments->create(self::newCreateRequest());

        $body = self::decodeLastBody($mock);
        self::assertNull($body['endereco']);
    }

    public function testGetMapsEstablishment(): void
    {
        $client = TestClientFactory::withMockResponses([
            new Response(200, [], (string) json_encode(self::ESTABLISHMENT_WIRE)),
        ]);

        $establishment = $client->establishments->get(self::ESTABLISHMENT_ID);

        self::assertSame(self::ESTABLISHMENT_ID, $establishment->establishmentId);
        self::assertSame('Loja Centro', $establishment->tradeName);
        self::assertTrue($establishment->isActive);
        self::assertSame(DisbursementModel::ESTABLISHMENT_CHAIN, $establishment->disbursementModel);
        self::assertSame('+5511999998888', $establishment->owner->phone);
        self::assertNotNull($establishment->bankAccount);
        self::assertSame(BankAccountType::CURRENT, $establishment->bankAccount->accountType);
        self::assertNotNull($establishment->address);
        self::assertSame('São Paulo', $establishment->address->city);
    }

    public function testGetWithoutBankAccountAndAddress(): void
    {
        $wire = self::ESTABLISHMENT_WIRE;
        $wire['ativa'] = false;
        $wire['modeloDesembolso'] = null;
        $wire['contaBancaria'] = null;
        $wire['endereco'] = null;

        $client = TestClientFactory::withMockResponses([
            new Response(200, [], (string) json_encode($wire)),
        ]);

        $establishment = $client->establishments->get(self::ESTABLISHMENT_ID);

        self::assertFalse($establishment->isActive);
        self::assertNull($establishment->disbursementModel);
        self::assertNull($establishment->bankAccount);
        self::assertNull($establishment->address);
    }

    public function testListBuildsQueryString(): void
    {
        [$client, $mock] = TestClientFactory::withMockHandler([
            new Response(200, [], (string) json_encode([self::ESTABLISHMENT_WIRE])),
        ]);

        $establishments = $client->establishments->list(new ListEstablishmentsRequest('Centro', true));

        self::assertCount(1, $establishments);
        self::assertSame('Loja Centro', $establishments[0]->tradeName);

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertStringEndsWith('v1/establishment/list', $request->getUri()->getPath());
        self::assertStringContainsString('nomeFantasia=Centro', $request->getUri()->getQuery());
        self::assertStringContainsString('ativa=true', $request->getUri()->getQuery());
    }

    public function testListWithoutFiltersSendsNoQuery(): void
    {
        [$client, $mock] = TestClientFactory::withMockHandler([
            new Response(200, [], (string) json_encode([])),
        ]);

        self::assertSame([], $client->establishments->list());

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('', $request->getUri()->getQuery());
    }

    public function testListInactiveSendsFalse(): void
    {
        [$client, $mock] = TestClientFactory::withMockHandler([
            new Response(200, [], (string) json_encode([])),
        ]);

        $client->establishments->list(new ListEstablishmentsRequest(null, false));

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertStringContainsString('ativa=false', $request->getUri()->getQuery());
    }

    public function testUpdateSendsOnlyEditableFields(): void
    {
        [$client, $mock] = TestClientFactory::withMockHandler([new Response(200)]);

        $client->establishments->update(self::ESTABLISHMENT_ID, new UpdateEstablishmentRequest('Loja Centro Matriz'));

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('PUT', $request->getMethod());
        self::assertStringEndsWith('v1/establishment/' . self::ESTABLISHMENT_ID, $request->getUri()->getPath());

        $body = self::decodeLastBody($mock);
        self::assertSame('Loja Centro Matriz', $body['nomeFantasia']);
        self::assertNull($body['modeloDesembolso']);
        self::assertNull($body['endereco']);
        self::assertArrayNotHasKey('contaBancaria', $body);
    }

    public function testUpdateBankAccountUsesOwnEndpoint(): void
    {
        [$client, $mock] = TestClientFactory::withMockHandler([new Response(200)]);

        $client->establishments->updateBankAccount(
            self::ESTABLISHMENT_ID,
            new EstablishmentBankAccount('237', '4321', '98765', '1', BankAccountType::SAVINGS)
        );

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertStringEndsWith(
            'v1/establishment/' . self::ESTABLISHMENT_ID . '/bank-account',
            $request->getUri()->getPath()
        );

        $body = self::decodeLastBody($mock);
        self::assertSame('237', $body['banco']);
        self::assertSame(2, $body['tipoConta']);
    }

    public function testActivateSendsTrue(): void
    {
        [$client, $mock] = TestClientFactory::withMockHandler([new Response(200)]);

        $client->establishments->activate(self::ESTABLISHMENT_ID);

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertStringEndsWith(
            'v1/establishment/' . self::ESTABLISHMENT_ID . '/status',
            $request->getUri()->getPath()
        );
        self::assertSame(['ativa' => true], self::decodeLastBody($mock));
    }

    public function testDeactivateSendsFalse(): void
    {
        [$client, $mock] = TestClientFactory::withMockHandler([new Response(200)]);

        $client->establishments->deactivate(self::ESTABLISHMENT_ID);

        self::assertSame(['ativa' => false], self::decodeLastBody($mock));
    }

    public function testCreateConflictThrowsApiException(): void
    {
        $client = TestClientFactory::withMockResponses([
            new Response(409, [], (string) json_encode([
                'tipo' => 'Establishment.DocumentAlreadyAdded',
                'detalhe' => 'Documento já cadastrado.',
            ])),
        ]);

        $this->expectException(ParceleMaisApiException::class);

        $client->establishments->create(self::newCreateRequest());
    }

    public function testGetNotFoundThrowsApiException(): void
    {
        $client = TestClientFactory::withMockResponses([
            new Response(404, [], (string) json_encode([
                'tipo' => 'Establishment.EstablishmentNotFound',
                'detalhe' => 'Estabelecimento não encontrado.',
            ])),
        ]);

        try {
            $client->establishments->get(self::ESTABLISHMENT_ID);
            self::fail('Esperava ParceleMaisApiException.');
        } catch (ParceleMaisApiException $e) {
            self::assertSame(404, $e->getStatusCode());
        }
    }
}
