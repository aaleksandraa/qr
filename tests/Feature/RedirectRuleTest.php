<?php

namespace Tests\Feature;

use App\Enums\RedirectRuleType;
use App\Models\QrCode;
use App\Models\QrRedirectRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_rule_overrides_destination(): void
    {
        $user = User::factory()->create();
        $qr = QrCode::factory()->dynamic('https://example.com', 'geo1')->create([
            'workspace_id' => $user->currentWorkspace()->id,
        ]);

        QrRedirectRule::create([
            'qr_code_id' => $qr->id,
            'type' => RedirectRuleType::Country,
            'operator' => 'equals',
            'configuration' => ['destinations' => ['BA' => 'https://example.ba']],
            'priority' => 10,
            'is_active' => true,
        ]);

        $this->withHeaders(['CF-IPCountry' => 'BA'])
            ->get('/r/geo1')
            ->assertRedirect('https://example.ba');

        $this->withHeaders(['CF-IPCountry' => 'US'])
            ->get('/r/geo1')
            ->assertRedirect('https://example.com');
    }

    public function test_utm_parameters_are_appended(): void
    {
        $user = User::factory()->create();
        QrCode::factory()->dynamic('https://example.com/page', 'utm1')->create([
            'workspace_id' => $user->currentWorkspace()->id,
            'utm_parameters' => [
                'utm_source' => 'qr',
                'utm_medium' => 'flyer',
            ],
        ]);

        $this->get('/r/utm1')->assertRedirect('https://example.com/page?utm_source=qr&utm_medium=flyer');
    }
}
