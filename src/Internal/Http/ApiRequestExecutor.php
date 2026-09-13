<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Internal\Http;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Twila\ParceleMais\Config\ResilienceOptions;
use Twila\ParceleMais\Errors\ExceptionFactory;
use Twila\ParceleMais\Internal\Auth\AccessTokenProvider;
use Twila\ParceleMais\Internal\Idempotency\IdempotencyClassifier;
use Twila\ParceleMais\Internal\Resilience\ResiliencePipeline;

final class ApiRequestExecutor
{
    /** @var string */
    private $baseUrl;

    /** @var AccessTokenProvider */
    private $tokenProvider;

    /** @var ResilienceOptions */
    private $resilienceOptions;

    /** @var ClientInterface */
    private $http;

    /** @var ResiliencePipeline */
    private $resilience;

    public function __construct(
        string $baseUrl,
        AccessTokenProvider $tokenProvider,
        ResilienceOptions $resilienceOptions,
        ?ClientInterface $httpClient = null
    ) {
        $this->baseUrl = $baseUrl;
        $this->tokenProvider = $tokenProvider;
        $this->resilienceOptions = $resilienceOptions;
        $this->http = $httpClient ?? new Client(['http_errors' => false]);
        $this->resilience = new ResiliencePipeline($resilienceOptions);
    }

    public function get(string $path): ApiResponse
    {
        return $this->send('GET', $path, null, $this->resilienceOptions->attemptTimeoutMs);
    }

    /**
     * @param mixed $body
     */
    public function post(string $path, $body, ?int $attemptTimeoutMs = null): ApiResponse
    {
        return $this->send('POST', $path, $body, $attemptTimeoutMs ?? $this->resilienceOptions->attemptTimeoutMs);
    }

    /**
     * @param mixed $body
     */
    public function put(string $path, $body): ApiResponse
    {
        return $this->send('PUT', $path, $body, $this->resilienceOptions->attemptTimeoutMs);
    }

    public function delete(string $path): ApiResponse
    {
        return $this->send('DELETE', $path, null, $this->resilienceOptions->attemptTimeoutMs);
    }

    public static function ensureSuccess(ApiResponse $response): void
    {
        if ($response->statusCode < 200 || $response->statusCode >= 300) {
            throw ExceptionFactory::fromResponse($response);
        }
    }

    /**
     * @param mixed $body
     */
    private function send(string $method, string $path, $body, int $attemptTimeoutMs): ApiResponse
    {
        $idempotencyKey = null;
        if (
            !$this->resilienceOptions->disableAutomaticIdempotencyKey
            && IdempotencyClassifier::requiresIdempotencyKey($method, $path)
        ) {
            $idempotencyKey = UuidGenerator::v4();
        }

        $retrySafe = IdempotencyClassifier::isRetrySafe($method, $path, $idempotencyKey !== null);

        return $this->resilience->execute(
            $retrySafe,
            function () use ($method, $path, $body, $idempotencyKey, $attemptTimeoutMs) {
                return $this->sendWithAuth($method, $path, $body, $idempotencyKey, $attemptTimeoutMs);
            }
        );
    }

    /**
     * @param mixed $body
     */
    private function sendWithAuth(
        string $method,
        string $path,
        $body,
        ?string $idempotencyKey,
        int $attemptTimeoutMs
    ): ApiResponse {
        $token = $this->tokenProvider->getToken();
        $response = $this->sendOnce($method, $path, $body, $idempotencyKey, $attemptTimeoutMs, $token);

        if ($response->statusCode !== 401) {
            return $response;
        }

        $this->tokenProvider->invalidate();
        $newToken = $this->tokenProvider->getToken();
        $retried = $this->sendOnce($method, $path, $body, $idempotencyKey, $attemptTimeoutMs, $newToken);

        if ($retried->statusCode !== 401) {
            return $retried;
        }

        throw ExceptionFactory::fromResponse($retried);
    }

    /**
     * @param mixed $body
     */
    private function sendOnce(
        string $method,
        string $path,
        $body,
        ?string $idempotencyKey,
        int $attemptTimeoutMs,
        string $token
    ): ApiResponse {
        // baseUrl sempre termina com "/" (garantido em ClientOptionsResolver) e path nunca começa
        // com "/" — concatenação direta evita a armadilha de resolução de URI relativa (RFC 3986 §5.3)
        // que descartaria o segmento de ambiente da base se algum dia usássemos Uri/base_uri para isso.
        $url = $this->baseUrl . $path;

        $headers = ['Authorization' => 'Bearer ' . $token];
        if ($idempotencyKey !== null) {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }

        $requestOptions = [
            'headers' => $headers,
            'timeout' => $attemptTimeoutMs / 1000,
        ];

        if ($body !== null) {
            $requestOptions['json'] = $body;
        }

        $raw = $this->http->request($method, $url, $requestOptions);

        return ResponseConverter::toApiResponse($raw);
    }
}
