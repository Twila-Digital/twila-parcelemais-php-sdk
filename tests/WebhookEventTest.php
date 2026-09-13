<?php

declare(strict_types=1);

namespace Twila\ParceleMais\Tests;

use PHPUnit\Framework\TestCase;
use Twila\ParceleMais\Errors\ParceleMaisWebhookSignatureException;
use Twila\ParceleMais\Orders\OrderStatus;
use Twila\ParceleMais\Webhooks\WebhookEvent;

final class WebhookEventTest extends TestCase
{
    private const SIGNING_SECRET = 'whsec_test';

    private function rawEvent(string $orderId = 'order-1', int $status = 9, string $statusName = 'Comprado'): string
    {
        return (string) json_encode(['id_pedido' => $orderId, 'enum_status' => $status, 'status' => $statusName]);
    }

    public function testParseWithoutSignatureMapsFields(): void
    {
        $event = WebhookEvent::parse($this->rawEvent());

        self::assertSame('order-1', $event->orderId);
        self::assertSame(OrderStatus::PURCHASED, $event->status);
        self::assertSame(9, $event->statusRaw);
        self::assertSame('Comprado', $event->statusName);
    }

    public function testParseMapsUnknownStatusToUnknown(): void
    {
        $event = WebhookEvent::parse($this->rawEvent('order-1', 999));

        self::assertSame(OrderStatus::UNKNOWN, $event->status);
        self::assertSame(999, $event->statusRaw);
    }

    public function testParseInvalidJsonRaisesSignatureException(): void
    {
        $this->expectException(ParceleMaisWebhookSignatureException::class);
        WebhookEvent::parse('not json');
    }

    public function testValidSignaturePassesVerification(): void
    {
        $payload = $this->rawEvent();
        $timestamp = time();
        $signature = WebhookEvent::computeSignature(self::SIGNING_SECRET, $timestamp, $payload);
        $header = sprintf('t=%d,v1=%s', $timestamp, $signature);

        $event = WebhookEvent::parse($payload, $header, self::SIGNING_SECRET);

        self::assertSame('order-1', $event->orderId);
    }

    public function testInvalidSignatureRaisesException(): void
    {
        $payload = $this->rawEvent();
        $timestamp = time();
        $header = sprintf('t=%d,v1=%s', $timestamp, str_repeat('0', 64));

        $this->expectException(ParceleMaisWebhookSignatureException::class);
        WebhookEvent::parse($payload, $header, self::SIGNING_SECRET);
    }

    public function testExpiredTimestampRaisesReplayException(): void
    {
        $payload = $this->rawEvent();
        $oldTimestamp = time() - 6 * 60;
        $signature = WebhookEvent::computeSignature(self::SIGNING_SECRET, $oldTimestamp, $payload);
        $header = sprintf('t=%d,v1=%s', $oldTimestamp, $signature);

        $this->expectException(ParceleMaisWebhookSignatureException::class);
        WebhookEvent::parse($payload, $header, self::SIGNING_SECRET);
    }

    public function testMalformedSignatureHeaderRaisesException(): void
    {
        $this->expectException(ParceleMaisWebhookSignatureException::class);
        WebhookEvent::parse($this->rawEvent(), 'garbage-header', self::SIGNING_SECRET);
    }
}
