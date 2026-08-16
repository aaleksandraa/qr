<?php

namespace Tests\Feature;

use App\Models\QrCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaticQrHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_static_url_qr_with_direct_payload(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('qr-codes.store'), [
            'qr_type' => 'static',
            'content_type' => 'url',
            'name' => 'Website',
            'payload' => ['url' => 'https://example.com/test'],
        ])->assertRedirect();

        $qr = QrCode::query()->first();
        $this->assertNotNull($qr);
        $this->assertSame('https://example.com/test', $qr->encoded_payload);
        $this->assertNull($qr->slug);
        $this->assertStringNotContainsString('/r/', $qr->encoded_payload);
    }

    public function test_registration_is_disabled_by_default(): void
    {
        $this->get('/register')->assertNotFound();
    }
}
