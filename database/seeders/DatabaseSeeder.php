<?php

namespace Database\Seeders;

use App\Enums\QrCodeType;
use App\Enums\StaticContentType;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\Folder;
use App\Models\QrScan;
use App\Models\User;
use App\Services\Qr\QrCodeService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()
            ->where('email', 'admin@example.com')
            ->update([
                'email' => 'aleksandra@wizionar.com',
                'name' => 'Aleksandra',
                'role' => UserRole::Admin,
            ]);

        $user = User::query()->firstOrCreate(
            ['email' => 'aleksandra@wizionar.com'],
            [
                'name' => 'Aleksandra',
                'password' => 'password',
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ],
        );

        if ($user->role !== UserRole::Admin) {
            $user->forceFill(['role' => UserRole::Admin])->save();
        }

        $milica = User::query()->firstOrCreate(
            ['email' => 'zu.dr.brkic@gmail.com'],
            [
                'name' => 'Milica Delić',
                'password' => 'BrkicDoboj2026!',
                'role' => UserRole::User,
                'email_verified_at' => now(),
            ],
        );

        foreach ([$user, $milica] as $account) {
            if ($account->wasRecentlyCreated || $account->workspaces()->doesntExist()) {
                app(\App\Services\Workspace\WorkspaceService::class)->provisionDefaultWorkspace($account);
            }
        }

        $workspace = $user->fresh()->currentWorkspace();
        if (! $workspace) {
            return;
        }

        $campaign = Campaign::query()->firstOrCreate(
            ['workspace_id' => $workspace->id, 'name' => 'Demo Campaign'],
            [
                'description' => 'Sample campaign for flyers, store, and event QR codes.',
                'status' => 'active',
                'created_by' => $user->id,
            ],
        );

        $folder = Folder::query()->firstOrCreate(
            ['workspace_id' => $workspace->id, 'name' => 'Marketing'],
        );

        $service = app(QrCodeService::class);

        if ($workspace->qrCodes()->exists()) {
            return;
        }

        $service->create($workspace, $user, [
            'qr_type' => QrCodeType::Static->value,
            'content_type' => StaticContentType::Url->value,
            'name' => 'Static Website QR',
            'payload' => ['url' => 'https://example.com'],
            'folder_id' => $folder->id,
        ]);

        $service->create($workspace, $user, [
            'qr_type' => QrCodeType::Static->value,
            'content_type' => StaticContentType::Wifi->value,
            'name' => 'Static Wi-Fi QR',
            'payload' => ['ssid' => 'Office', 'security' => 'WPA', 'password' => 'Test123456'],
        ]);

        $service->create($workspace, $user, [
            'qr_type' => QrCodeType::Static->value,
            'content_type' => StaticContentType::Vcard->value,
            'name' => 'Static vCard QR',
            'payload' => [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'company' => 'Analytical Engines',
                'email' => 'ada@example.com',
                'phone' => '+38765123456',
            ],
        ]);

        $academy = $service->create($workspace, $user, [
            'qr_type' => QrCodeType::Dynamic->value,
            'name' => 'Dynamic Academy QR',
            'destination_url' => 'https://example.com/academy',
            'custom_slug' => 'academy',
            'campaign_id' => $campaign->id,
            'folder_id' => $folder->id,
        ]);

        $service->create($workspace, $user, [
            'qr_type' => QrCodeType::Dynamic->value,
            'name' => 'Dynamic Event QR',
            'destination_url' => 'https://example.com/event',
            'custom_slug' => 'event-2026',
            'campaign_id' => $campaign->id,
        ]);

        $service->create($workspace, $user, [
            'qr_type' => QrCodeType::Dynamic->value,
            'name' => 'Dynamic Product QR',
            'destination_url' => 'https://example.com/product',
            'campaign_id' => $campaign->id,
        ]);

        if (app()->environment('local', 'testing')) {
            QrScan::create([
                'qr_code_id' => $academy->id,
                'scanned_at' => now(),
                'visitor_hash' => hash('sha256', 'demo-visitor'),
                'country_code' => 'BA',
                'device_type' => 'mobile',
                'os' => 'iOS',
                'browser' => 'Safari',
                'is_bot' => false,
            ]);
        }
    }
}
