<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Internal\Resilience;

use Twila\ParceleMais\Config\ResilienceOptions;
use Twila\ParceleMais\Errors\ParceleMaisTimeoutException;
use Twila\ParceleMais\Internal\Http\ApiResponse;

final class ResiliencePipeline
{
    /** @var ResilienceOptions */
    private $options;

    /** @var CircuitBreaker */
    private $circuitBreaker;

    public function __construct(ResilienceOptions $options)
    {
        $this->options = $options;
        $this->circuitBreaker = new CircuitBreaker($options);
    }

    /**
     * @param callable():ApiResponse $attempt
     */
    public function execute(bool $retrySafe, callable $attempt): ApiResponse
    {
        $deadline = microtime(true) + ($this->options->totalTimeoutMs / 1000);
        $maxAttempts = max(1, $this->options->maxRetryAttempts);
        $lastError = null;

        for ($attemptNumber = 1; $attemptNumber <= $maxAttempts; $attemptNumber++) {
            if (microtime(true) >= $deadline) {
                throw new ParceleMaisTimeoutException('A requisição excedeu o tempo limite configurado.', $lastError);
            }

            try {
                $this->circuitBreaker->beforeCall();
            } catch (BrokenCircuitException $broken) {
                throw new ParceleMaisTimeoutException(
                    'O circuit breaker está aberto — chamadas recentes falharam de forma consistente.',
                    $broken
                );
            }

            try {
                $response = $attempt();
            } catch (\Throwable $error) {
                if (!TransientFailureClassifier::isNetworkError($error)) {
                    throw $error;
                }

                $this->circuitBreaker->onFailure();
                $lastError = $error;

                if ($retrySafe && $attemptNumber < $maxAttempts && microtime(true) < $deadline) {
                    $this->sleepSeconds($this->backoffSeconds($attemptNumber));
                    continue;
                }

                throw $error;
            }

            $transient = TransientFailureClassifier::isTransientResponse($response, $this->options);
            if ($transient) {
                $this->circuitBreaker->onFailure();
            } else {
                $this->circuitBreaker->onSuccess();
            }

            if (!$transient || !$retrySafe) {
                return $response;
            }

            if ($attemptNumber >= $maxAttempts) {
                return $response;
            }

            $remainingSeconds = $deadline - microtime(true);
            if ($remainingSeconds <= 0) {
                return $response;
            }

            $delay = $this->delaySeconds($attemptNumber, $response);
            $this->sleepSeconds(min($delay, max(0.0, $remainingSeconds)));
        }

        throw new \LogicException('unreachable: resilience pipeline loop did not return or raise');
    }

    private function delaySeconds(int $attemptNumber, ApiResponse $response): float
    {
        $retryAfter = self::retryAfterSeconds($response);
        if ($retryAfter !== null) {
            return $retryAfter;
        }

        return $this->backoffSeconds($attemptNumber);
    }

    private function backoffSeconds(int $attemptNumber): float
    {
        $baseDelayMs = $this->options->retryBaseDelayMs;
        $exponential = $baseDelayMs * (2 ** max(0, $attemptNumber - 1));
        $jitterFactor = 0.5 + (mt_rand() / mt_getrandmax());

        return ($exponential * $jitterFactor) / 1000;
    }

    private function sleepSeconds(float $seconds): void
    {
        if ($seconds <= 0) {
            return;
        }

        usleep((int) round($seconds * 1_000_000));
    }

    private static function retryAfterSeconds(ApiResponse $response): ?float
    {
        $retryAfter = $response->header('retry-after');
        if ($retryAfter === null || $retryAfter === '') {
            return null;
        }

        return is_numeric($retryAfter) ? (float) $retryAfter : null;
    }
}
