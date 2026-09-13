<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Twila\ParceleMais\Config\ClientOptions;
use Twila\ParceleMais\Config\Environment;
use Twila\ParceleMais\ParceleMaisClient;
use Twila\ParceleMais\Simulations\SimulateInstallmentsRequest;

$clientId = getenv('PARCELEMAIS_CLIENT_ID');
$clientSecret = getenv('PARCELEMAIS_CLIENT_SECRET');

if ($clientId === false || $clientSecret === false) {
    fwrite(STDERR, "Defina PARCELEMAIS_CLIENT_ID e PARCELEMAIS_CLIENT_SECRET no ambiente.\n");
    exit(1);
}

$client = new ParceleMaisClient(new ClientOptions($clientId, $clientSecret, Environment::STAGING));

$parcelas = $client->simulations->simulateInstallments(new SimulateInstallmentsRequest(1500.0));

foreach ($parcelas as $parcela) {
    printf("%dx de %s (total %s)%s", $parcela->term, $parcela->installmentAmount, $parcela->totalAmount, PHP_EOL);
}
