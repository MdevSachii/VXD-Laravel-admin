<?php

namespace App\Services;

use App\Models\SystemSetting;
use Carbon\CarbonImmutable;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;

class WordPressTokenStore
{
    private const KEY = 'wordpress.oauth';
    private const SKEW_SECONDS = 120;

    public function saveFromTokenResponse(array $tokenBody): void
    {
        $expiresAt = CarbonImmutable::now()->addSeconds((int)($tokenBody['expires_in'] ?? 0));

        $data = [
            'access_token'  => $tokenBody['access_token'] ?? null,
            'refresh_token' => $tokenBody['refresh_token'] ?? null,
            'token_type'    => $tokenBody['token_type'] ?? 'bearer',
            'scope'         => $tokenBody['scope'] ?? null,
            'expires_at'    => $expiresAt->toIso8601String(),
            'obtained_at'   => CarbonImmutable::now()->toIso8601String(),
            'raw'           => $tokenBody,
        ];

        SystemSetting::updateOrCreate(
            ['name'  => self::KEY],
            ['value' => Crypt::encryptString(json_encode($data))]
        );
    }

    public function getTokens(): ?array
    {
        $row = SystemSetting::where('name', self::KEY)->first();
        if (!$row) return null;

        try {
            return json_decode(Crypt::decryptString($row->value), true);
        } catch (\Throwable) {
            return null;
        }
    }

    public function getAccessToken(): ?string
    {
        $tokens = $this->getTokens();
        if (!$tokens) return null;

        $expiresAt = isset($tokens['expires_at']) ? CarbonImmutable::parse($tokens['expires_at']) : null;
        $now = CarbonImmutable::now();

        if ($expiresAt && $now->addSeconds(self::SKEW_SECONDS)->lt($expiresAt)) {
            return $tokens['access_token'] ?? null;
        }

        return $this->refreshAccessToken();
    }

    public function refreshAccessToken(): ?string
    {
        $lock = Cache::lock('wp_token_refresh', 10);

        try {
            if (!$lock->get()) {
                usleep(300_000);
                $t = $this->getTokens();
                return $t['access_token'] ?? null;
            }

            $tokens = $this->getTokens();
            if (!$tokens || empty($tokens['refresh_token'])) {
                return $tokens['access_token'] ?? null;
            }

            $cfg = Config::get('services.wordpress');
            $http = new Client([
                'http_errors' => false,
                'base_uri'    => rtrim($cfg['api_base_url'], '/') . '/',
                'timeout'     => 15,
            ]);

            $resp = $http->post(ltrim($cfg['oauth']['token'], '/'), [
                'form_params' => [
                    'client_id'     => $cfg['client_id'],
                    'client_secret' => $cfg['client_secret'],
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $tokens['refresh_token'],
                ],
            ]);

            $body = json_decode((string)$resp->getBody(), true) ?: [];
            if (empty($body['access_token'])) {
                return $tokens['access_token'] ?? null;
            }

            $this->saveFromTokenResponse($body);
            $new = $this->getTokens();
            return $new['access_token'] ?? null;
        } finally {
            optional($lock)->release();
        }
    }
}
