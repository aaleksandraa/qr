<?php

namespace App\Providers;

use App\Models\Campaign;
use App\Models\Folder;
use App\Models\QrCode;
use App\Policies\CampaignPolicy;
use App\Policies\FolderPolicy;
use App\Policies\QrCodePolicy;
use App\Services\Analytics\Geo\GeoResolverInterface;
use App\Services\Analytics\Geo\HeaderGeoResolver;
use App\Services\Analytics\Geo\NullGeoResolver;
use App\Services\Qr\Payloads\EmailPayloadBuilder;
use App\Services\Qr\Payloads\LocationPayloadBuilder;
use App\Services\Qr\Payloads\PhonePayloadBuilder;
use App\Services\Qr\Payloads\SmsPayloadBuilder;
use App\Services\Qr\Payloads\TextPayloadBuilder;
use App\Services\Qr\Payloads\UrlPayloadBuilder;
use App\Services\Qr\Payloads\VCardPayloadBuilder;
use App\Services\Qr\Payloads\WifiPayloadBuilder;
use App\Services\Qr\StaticQrPayloadBuilder;
use App\Services\Redirect\QrRuleEngine;
use App\Services\Redirect\Rules\CountryRuleHandler;
use App\Services\Redirect\Rules\DateTimeRuleHandler;
use App\Services\Redirect\Rules\DeviceRuleHandler;
use App\Services\Redirect\Rules\LanguageRuleHandler;
use App\Services\Redirect\Rules\WeightedAbRuleHandler;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StaticQrPayloadBuilder::class, function ($app) {
            return new StaticQrPayloadBuilder([
                $app->make(UrlPayloadBuilder::class),
                $app->make(TextPayloadBuilder::class),
                $app->make(EmailPayloadBuilder::class),
                $app->make(PhonePayloadBuilder::class),
                $app->make(SmsPayloadBuilder::class),
                $app->make(WifiPayloadBuilder::class),
                $app->make(VCardPayloadBuilder::class),
                $app->make(LocationPayloadBuilder::class),
            ]);
        });

        $this->app->singleton(QrRuleEngine::class, function ($app) {
            return new QrRuleEngine([
                $app->make(DeviceRuleHandler::class),
                $app->make(CountryRuleHandler::class),
                $app->make(LanguageRuleHandler::class),
                $app->make(DateTimeRuleHandler::class),
                $app->make(WeightedAbRuleHandler::class),
            ]);
        });

        $this->app->bind(GeoResolverInterface::class, function () {
            return config('qr.geo_resolver') === 'null'
                ? new NullGeoResolver
                : new HeaderGeoResolver;
        });
    }

    public function boot(): void
    {
        Gate::policy(QrCode::class, QrCodePolicy::class);
        Gate::policy(Campaign::class, CampaignPolicy::class);
        Gate::policy(Folder::class, FolderPolicy::class);

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(8)->by($request->ip()));
        RateLimiter::for('register', function (Request $request) {
            $ip = (string) $request->ip();

            return [
                Limit::perMinute((int) config('qr.registration.max_per_minute', 5))->by('reg-min:'.$ip),
                Limit::perHour((int) config('qr.registration.max_per_hour', 8))->by('reg-hour:'.$ip),
                Limit::perDay((int) config('qr.registration.max_per_day', 12))->by('reg-day:'.$ip),
            ];
        });
        RateLimiter::for('qr-write', fn (Request $request) => Limit::perMinute(30)->by(optional($request->user())->id ?: $request->ip()));
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip()));
    }
}
