<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use Twila\ParceleMais\Config\ClientOptions;
use Twila\ParceleMais\Config\Environment;
use Twila\ParceleMais\Errors\ParceleMaisApiException;
use Twila\ParceleMais\ParceleMaisClient;
use Twila\ParceleMais\Simulations\SimulateInstallmentsRequest;

$app = AppFactory::create();

// ParceleMaisClient como singleton na aplicação — uma instância por processo/worker,
// nunca uma por requisição (ele mantém cache de token e estado do circuit breaker).
$parceleMaisClient = new ParceleMaisClient(new ClientOptions(
    getenv('PARCELEMAIS_CLIENT_ID') ?: '',
    getenv('PARCELEMAIS_CLIENT_SECRET') ?: '',
    Environment::STAGING
));

$app->get('/simulacoes', function (ServerRequestInterface $request, ResponseInterface $response) use ($parceleMaisClient) {
    try {
        $parcelas = $parceleMaisClient->simulations->simulateInstallments(new SimulateInstallmentsRequest(1500.0));
    } catch (ParceleMaisApiException $e) {
        $response->getBody()->write((string) json_encode(['erro' => $e->getMessage()]));
        return $response->withStatus($e->getStatusCode())->withHeader('Content-Type', 'application/json');
    }

    $payload = array_map(static function ($parcela) {
        return ['prazo' => $parcela->term, 'valorParcela' => $parcela->installmentAmount, 'valorTotal' => $parcela->totalAmount];
    }, $parcelas);

    $response->getBody()->write((string) json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
