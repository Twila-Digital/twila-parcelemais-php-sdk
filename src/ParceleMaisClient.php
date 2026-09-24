<?php

declare(strict_types=1);

namespace Twila\ParceleMais;

use GuzzleHttp\ClientInterface;
use Twila\ParceleMais\Config\ClientOptions;
use Twila\ParceleMais\Config\ClientOptionsResolver;
use Twila\ParceleMais\Customers\CustomersClient;
use Twila\ParceleMais\Internal\Auth\AccessTokenProvider;
use Twila\ParceleMais\Internal\Auth\TokenApiClient;
use Twila\ParceleMais\Internal\Http\ApiRequestExecutor;
use Twila\ParceleMais\Orders\OrdersClient;
use Twila\ParceleMais\Simulations\SimulationsClient;
use Twila\ParceleMais\Establishments\EstablishmentsClient;
use Twila\ParceleMais\Webhooks\WebhooksClient;

/**
 * Cliente principal do SDK. Reaproveite como singleton na aplicação (não crie um por requisição) —
 * ele mantém o cache do token de acesso e o estado do circuit breaker.
 */
final class ParceleMaisClient
{
    /** @var OrdersClient */
    public $orders;

    /** @var SimulationsClient */
    public $simulations;

    /** @var CustomersClient */
    public $customers;

    /** @var EstablishmentsClient */
    public $establishments;

    /** @var WebhooksClient */
    public $webhooks;

    /**
     * @param ClientInterface|null $httpClient Cliente Guzzle customizado — uso interno/testes.
     *   Consumidores normais não precisam informar isso.
     */
    public function __construct(ClientOptions $options, ?ClientInterface $httpClient = null)
    {
        $resolved = ClientOptionsResolver::resolve($options);

        $tokenApiClient = new TokenApiClient(
            $resolved->baseUrl,
            $resolved->resilience->attemptTimeoutMs / 1000,
            $httpClient
        );
        $tokenProvider = new AccessTokenProvider($tokenApiClient, $resolved->clientId, $resolved->clientSecret);

        $executor = new ApiRequestExecutor($resolved->baseUrl, $tokenProvider, $resolved->resilience, $httpClient);

        $this->orders = new OrdersClient($executor, $resolved->resilience->invoiceUploadAttemptTimeoutMs);
        $this->simulations = new SimulationsClient($executor);
        $this->customers = new CustomersClient($executor);
        $this->establishments = new EstablishmentsClient($executor);
        $this->webhooks = new WebhooksClient($executor);
    }
}
