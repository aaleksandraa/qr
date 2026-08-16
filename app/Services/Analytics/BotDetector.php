<?php

namespace App\Services\Analytics;

class BotDetector
{
    /**
     * @var list<string>
     */
    private array $signatures = [
        'facebookexternalhit', 'facebot', 'whatsapp', 'slackbot', 'googlebot',
        'bingbot', 'twitterbot', 'discordbot', 'telegrambot', 'linkedinbot',
        'pinterest', 'applebot', 'yandexbot', 'baiduspider', 'duckduckbot',
        'semrushbot', 'ahrefsbot', 'petalbot', 'bytespider', 'gptbot',
        'chatgpt', 'claudebot', 'preview', 'crawler', 'spider', 'bot/',
        'httpie', 'curl/', 'wget/', 'python-requests', 'go-http-client',
        'postman', 'headlesschrome',
    ];

    public function isBot(?string $userAgent): bool
    {
        if (! $userAgent) {
            return true;
        }

        $ua = strtolower($userAgent);

        foreach ($this->signatures as $signature) {
            if (str_contains($ua, $signature)) {
                return true;
            }
        }

        return false;
    }
}
