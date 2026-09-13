<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Internal\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Twila\ParceleMais\Errors\ExceptionFactory;
use Twila\ParceleMais\Errors\ParceleMaisAuthenticationException;
use Twila\ParceleMais\Internal\Http\ResponseConverter;

final class TokenApiClient
{
    /** @var ClientInterface */
    private $http;

    public function __construct(string $baseUrl, float $timeoutSeconds, ?ClientInterface $httpClient = null)
    {
        $this->http = $httpClient ?? new Client([
            'base_uri' => $baseUrl,
            'timeout' => $timeoutSeconds,
            'http_errors' => false,
        ]);
    }

    /**
     * @return array{token_de_acesso: string, expira_em_segundos: int, tipo_de_token: string}
     */
    public function generate(string $clientId, string $clientSecret): array
    {
        try {
            $raw = $this->http->request('POST', 'v1/authentication/accesstoken', [
                'json' => ['clientId' => $clientId, 'clientSecret' => $clientSecret],
            ]);
        } catch (GuzzleException $e) {
            throw new ParceleMaisAuthenticationException('Falha de rede ao gerar o token de acesso.', $e);
        }

        $response = ResponseConverter::toApiResponse($raw);

        if ($response->statusCode < 200 || $response->statusCode >= 300) {
            throw ExceptionFactory::fromResponse($response);
        }

        $body = $response->body;
        if (!is_array($body) || empty($body['token_de_acesso'])) {
            throw new ParceleMaisAuthenticationException(
                'A API do Parcele+ retornou uma resposta vazia ao gerar o token de acesso.'
            );
        }

        /** @var array{token_de_acesso: string, expira_em_segundos: int, tipo_de_token: string} $body */
        return $body;
    }
}
