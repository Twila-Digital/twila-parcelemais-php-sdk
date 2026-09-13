<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Webhooks;

use Twila\ParceleMais\Errors\ParceleMaisWebhookSignatureException;
use Twila\ParceleMais\Orders\OrderStatus;

final class WebhookEvent
{
    private const REPLAY_TOLERANCE_SECONDS = 5 * 60;

    public static function parse(
        string $rawJson,
        ?string $signatureHeader = null,
        ?string $signingSecret = null
    ): OrderWebhookEvent {
        if ($signatureHeader !== null && $signingSecret !== null) {
            self::verifySignature($rawJson, $signatureHeader, $signingSecret);
        }

        $wire = json_decode($rawJson, true);

        if (!is_array($wire)) {
            throw new ParceleMaisWebhookSignatureException('O corpo do webhook está vazio ou não é um JSON válido.');
        }

        return new OrderWebhookEvent(
            $wire['id_pedido'],
            OrderStatus::fromWireValue($wire['enum_status']),
            $wire['enum_status'],
            $wire['status']
        );
    }

    public static function computeSignature(string $signingSecret, int $timestampSeconds, string $payload): string
    {
        $signedContent = $timestampSeconds . '.' . $payload;

        return hash_hmac('sha256', $signedContent, $signingSecret);
    }

    private static function verifySignature(string $rawJson, string $signatureHeader, string $signingSecret): void
    {
        [$timestamp, $signature] = self::parseSignatureHeader($signatureHeader);
        $computed = self::computeSignature($signingSecret, $timestamp, $rawJson);

        if (!hash_equals($computed, $signature)) {
            throw new ParceleMaisWebhookSignatureException('A assinatura do webhook não confere.');
        }

        if (abs(time() - $timestamp) > self::REPLAY_TOLERANCE_SECONDS) {
            throw new ParceleMaisWebhookSignatureException(
                'O timestamp do webhook está fora da janela de tolerância — possível replay.'
            );
        }
    }

    /**
     * @return array{0: int, 1: string}
     */
    private static function parseSignatureHeader(string $signatureHeader): array
    {
        $timestamp = null;
        $signature = null;

        foreach (explode(',', $signatureHeader) as $part) {
            $pieces = explode('=', $part, 2);
            $key = trim($pieces[0]);
            $value = isset($pieces[1]) ? trim($pieces[1]) : '';

            if ($key === 't' && is_numeric($value)) {
                $timestamp = (int) $value;
            } elseif ($key === 'v1') {
                $signature = strtolower($value);
            }
        }

        if ($timestamp === null || $signature === null) {
            throw new ParceleMaisWebhookSignatureException(
                sprintf("Cabeçalho de assinatura malformado: '%s'.", $signatureHeader)
            );
        }

        return [$timestamp, $signature];
    }
}
