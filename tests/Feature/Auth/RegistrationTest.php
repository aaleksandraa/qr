<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_is_disabled_when_public_registration_is_false(): void
    {
        config(['qr.public_registration' => false]);

        $this->get('/register')->assertNotFound();
    }

    public function test_registration_screen_can_be_rendered_when_enabled(): void
    {
        config(['qr.public_registration' => true]);

        $this->get('/register')->assertStatus(200);
    }

    public function test_landing_page_is_public(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_new_users_can_register_when_enabled(): void
    {
        config(['qr.public_registration' => true]);

        $response = $this->post('/register', $this->validPayload());

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('workspaces', ['owner_id' => auth()->id()]);
    }

    public function test_honeypot_blocks_automated_signups(): void
    {
        config(['qr.public_registration' => true]);

        $this->post('/register', $this->validPayload([
            'website' => 'https://spam.example',
        ]))->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_too_fast_submissions_are_rejected(): void
    {
        config([
            'qr.public_registration' => true,
            'qr.registration.min_seconds' => 4,
        ]);

        $this->post('/register', $this->validPayload([
            'form_started_at' => time(),
        ]))->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_disposable_email_is_rejected(): void
    {
        config(['qr.public_registration' => true]);

        $this->post('/register', $this->validPayload([
            'email' => 'bot@mailinator.com',
        ]))->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'bot@mailinator.com']);
    }

    public function test_turnstile_is_required_when_configured(): void
    {
        config([
            'qr.public_registration' => true,
            'qr.registration.turnstile.secret_key' => 'test-secret',
        ]);

        $this->post('/register', $this->validPayload())
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_turnstile_success_allows_registration(): void
    {
        config([
            'qr.public_registration' => true,
            'qr.registration.turnstile.secret_key' => 'test-secret',
        ]);

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true]),
        ]);

        $this->post('/register', $this->validPayload([
            'cf-turnstile-response' => 'ok-token',
        ]))->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_register_endpoint_is_rate_limited(): void
    {
        config([
            'qr.public_registration' => true,
            'qr.registration.max_per_minute' => 1,
        ]);

        $this->post('/register', ['email' => 'one@example.com'])->assertSessionHasErrors();
        $this->post('/register', ['email' => 'two@example.com'])->assertStatus(429);

        $this->assertSame(0, User::query()->count());
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'website' => '',
            'form_started_at' => time() - 5,
        ], $overrides);
    }
}
