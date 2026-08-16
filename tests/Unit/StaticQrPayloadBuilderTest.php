<?php

namespace Tests\Unit;

use App\Enums\StaticContentType;
use App\Services\Qr\StaticQrPayloadBuilder;
use Tests\TestCase;

class StaticQrPayloadBuilderTest extends TestCase
{
    private function builder(): StaticQrPayloadBuilder
    {
        return app(StaticQrPayloadBuilder::class);
    }

    public function test_url_payload_is_the_destination_not_a_redirect(): void
    {
        $payload = $this->builder()->build(StaticContentType::Url, [
            'url' => 'https://example.com/test',
        ]);

        $this->assertSame('https://example.com/test', $payload);
        $this->assertStringNotContainsString('/r/', $payload);
    }

    public function test_url_rejects_javascript_scheme(): void
    {
        $this->expectException(\App\Exceptions\InvalidQrDestination::class);

        $this->builder()->build(StaticContentType::Url, [
            'url' => 'javascript:alert(1)',
        ]);
    }

    public function test_text_payload(): void
    {
        $this->assertSame('Hello world', $this->builder()->build(StaticContentType::Text, [
            'text' => 'Hello world',
        ]));
    }

    public function test_email_payload_is_encoded(): void
    {
        $payload = $this->builder()->build(StaticContentType::Email, [
            'email' => 'info@example.com',
            'subject' => 'Hello there',
        ]);

        $this->assertStringStartsWith('mailto:info@example.com?', $payload);
        $this->assertStringContainsString('subject=Hello', $payload);
    }

    public function test_phone_payload(): void
    {
        $this->assertSame('tel:+38765123456', $this->builder()->build(StaticContentType::Phone, [
            'phone' => '+387 65 123 456',
        ]));
    }

    public function test_sms_payload(): void
    {
        $this->assertSame('SMSTO:+38765123456:Hello', $this->builder()->build(StaticContentType::Sms, [
            'phone' => '+38765123456',
            'message' => 'Hello',
        ]));
    }

    public function test_wifi_payload_escapes_special_characters(): void
    {
        $payload = $this->builder()->build(StaticContentType::Wifi, [
            'ssid' => 'Office;Net',
            'security' => 'WPA',
            'password' => 'Test123456',
        ]);

        $this->assertSame('WIFI:T:WPA;S:Office\;Net;P:Test123456;;', $payload);
    }

    public function test_vcard_payload(): void
    {
        $payload = $this->builder()->build(StaticContentType::Vcard, [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.com',
        ]);

        $this->assertStringContainsString('BEGIN:VCARD', $payload);
        $this->assertStringContainsString('VERSION:3.0', $payload);
        $this->assertStringContainsString('END:VCARD', $payload);
        $this->assertStringContainsString('ada@example.com', $payload);
    }

    public function test_location_payload(): void
    {
        $this->assertSame('geo:43.8563,18.4131', $this->builder()->build(StaticContentType::Location, [
            'latitude' => 43.8563,
            'longitude' => 18.4131,
        ]));
    }
}
