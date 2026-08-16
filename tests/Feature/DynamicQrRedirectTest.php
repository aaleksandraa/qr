<?php

namespace Tests\Feature;

use App\Enums\QrStatus;
use App\Jobs\TrackQrScan;
use App\Models\QrCode;
use App\Models\QrScan;
use App\Models\User;
use App\Services\Qr\QrImageGenerator;
use App\Services\Redirect\QrRedirectCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class DynamicQrRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_dynamic_qr_redirects_with_302_and_can_change_destination_without_new_qr(): void
    {
        $user = User::factory()->create();
        $workspace = $user->currentWorkspace();

        $qr = QrCode::factory()->dynamic('https://example.com/page-a', 'abc123')->create([
            'workspace_id' => $workspace->id,
            'created_by' => $user->id,
        ]);

        $this->assertSame('http://localhost/r/abc123', $qr->encoded_payload);
        $this->assertSame('http://localhost/r/abc123', $qr->shortUrl());

        $response = $this->get('/r/abc123');
        $response->assertStatus(302)->assertRedirect('https://example.com/page-a');
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));

        $this->actingAs($user)->put(route('qr-codes.update', $qr), [
            'destination_url' => 'https://example.com/page-b',
        ])->assertRedirect();

        $qr->refresh();
        $this->assertSame('abc123', $qr->slug);
        $this->assertSame('http://localhost/r/abc123', $qr->encoded_payload);
        $this->assertSame('https://example.com/page-b', $qr->destination_url);

        $this->get('/r/abc123')
            ->assertStatus(302)
            ->assertRedirect('https://example.com/page-b');
    }

    public function test_reserved_and_duplicate_slugs_are_rejected(): void
    {
        $user = User::factory()->create();
        $workspace = $user->currentWorkspace();

        $this->actingAs($user)->post(route('qr-codes.store'), [
            'qr_type' => 'dynamic',
            'name' => 'Admin slug',
            'destination_url' => 'https://example.com/a',
            'custom_slug' => 'admin',
        ])->assertSessionHasErrors();

        QrCode::factory()->dynamic('https://example.com/a', 'academy')->create([
            'workspace_id' => $workspace->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->post(route('qr-codes.store'), [
            'qr_type' => 'dynamic',
            'name' => 'Collision',
            'destination_url' => 'https://example.com/b',
            'custom_slug' => 'academy',
        ])->assertSessionHasErrors();
    }

    public function test_paused_and_expired_qr_do_not_redirect(): void
    {
        $user = User::factory()->create();
        $workspace = $user->currentWorkspace();

        $paused = QrCode::factory()->dynamic('https://example.com/a', 'paused1')->create([
            'workspace_id' => $workspace->id,
            'status' => QrStatus::Paused,
        ]);

        $this->get('/r/paused1')->assertOk()->assertSee('currently unavailable');

        QrCode::factory()->dynamic('https://example.com/a', 'expired1')->create([
            'workspace_id' => $workspace->id,
            'expires_at' => now()->subMinute(),
        ]);

        $this->get('/r/expired1')->assertOk()->assertSee('expired');

        QrCode::factory()->dynamic('https://example.com/a', 'future1')->create([
            'workspace_id' => $workspace->id,
            'starts_at' => now()->addDay(),
        ]);

        $this->get('/r/future1')->assertOk()->assertSee('not active yet');
        $this->assertNotNull($paused->id);
    }

    public function test_unknown_slug_shows_controlled_page(): void
    {
        $this->get('/r/missing-slug')->assertOk()->assertSee('not found');
    }

    public function test_scan_dispatches_analytics_and_redirect_does_not_depend_on_processing(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        QrCode::factory()->dynamic('https://example.com/a', 'trackme')->create([
            'workspace_id' => $user->currentWorkspace()->id,
        ]);

        $this->get('/r/trackme')->assertRedirect('https://example.com/a');
        Bus::assertDispatched(TrackQrScan::class);
    }

    public function test_human_scan_is_recorded_without_raw_ip_and_bots_are_separated(): void
    {
        $user = User::factory()->create();
        $qr = QrCode::factory()->dynamic('https://example.com/a', 'human1')->create([
            'workspace_id' => $user->currentWorkspace()->id,
        ]);

        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Safari/605'])
            ->get('/r/human1')
            ->assertRedirect();

        $this->withHeaders(['User-Agent' => 'facebookexternalhit/1.1'])
            ->get('/r/human1')
            ->assertRedirect();

        $qr->refresh();
        $this->assertSame(2, $qr->total_scans);
        $this->assertSame(1, $qr->human_scans);
        $this->assertSame(1, $qr->bot_scans);
        $this->assertDatabaseMissing('qr_scans', ['ip_address' => '127.0.0.1']);
        $this->assertTrue(QrScan::query()->where('qr_code_id', $qr->id)->where('is_bot', true)->exists());
        $this->assertTrue(QrScan::query()->where('qr_code_id', $qr->id)->whereNotNull('visitor_hash')->exists());
    }

    public function test_destination_update_invalidates_redirect_cache(): void
    {
        $user = User::factory()->create();
        $qr = QrCode::factory()->dynamic('https://example.com/a', 'cache1')->create([
            'workspace_id' => $user->currentWorkspace()->id,
            'created_by' => $user->id,
        ]);

        $cache = app(QrRedirectCache::class);
        $cache->put($qr);
        $this->assertSame('https://example.com/a', $cache->get('cache1')['destination_url']);

        $this->actingAs($user)->put(route('qr-codes.update', $qr), [
            'destination_url' => 'https://example.com/b',
        ]);

        $this->assertSame('https://example.com/b', $cache->get('cache1')['destination_url']);
    }

    public function test_user_cannot_view_another_workspace_qr(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $qr = QrCode::factory()->dynamic('https://example.com/a', 'secret1')->create([
            'workspace_id' => $owner->currentWorkspace()->id,
        ]);

        $this->actingAs($intruder)->get(route('qr-codes.show', $qr))->assertForbidden();
    }

    public function test_static_qr_image_encodes_destination_not_redirect(): void
    {
        $generator = app(QrImageGenerator::class);
        $svg = $generator->svg('https://example.com/test');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringNotContainsString('localhost/r/', $svg);

        $png = $generator->png('https://example.com/test', [], 256);
        $this->assertNotSame('', $png);
        $this->assertSame('PNG', substr($png, 1, 3));
    }

    public function test_fallback_url_is_used_when_expired_behavior_is_fallback(): void
    {
        $user = User::factory()->create();
        QrCode::factory()->dynamic('https://example.com/a', 'fall1')->create([
            'workspace_id' => $user->currentWorkspace()->id,
            'expires_at' => now()->subMinute(),
            'expired_behavior' => 'fallback',
            'fallback_url' => 'https://example.com/waiting-list',
        ]);

        $this->get('/r/fall1')->assertRedirect('https://example.com/waiting-list');
    }
}
