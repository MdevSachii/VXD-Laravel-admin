<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use GuzzleHttp\Client;

class WPAuthController extends Controller
{
    public function redirect(Request $request)
    {
        $cfg   = Config::get('services.wordpress');
        $state = Str::random(40);
        $request->session()->put('wp_oauth_state', $state);

        $blog = $request->query('blog') ?? ($cfg['site'] ?? null);
        if ($blog) {
            $request->session()->put('wp_oauth_blog', $blog);
        }

        $authorizeBase = rtrim($cfg['api_base_url'], '/') . '/' . ltrim($cfg['oauth']['authorize'], '/');
        if (!preg_match('#^https?://#i', $authorizeBase)) {
            $authorizeBase = 'https://public-api.wordpress.com/oauth2/authorize';
        }

        $query = array_filter([
            'client_id'     => $cfg['client_id'],
            'redirect_uri'  => $cfg['redirect'],
            'response_type' => 'code',
            'scope'         => $cfg['scope'] ?? 'auth',
            'state'         => $state,
            'blog'          => $blog,
        ]);

        $url = $authorizeBase . '?' . http_build_query($query);
        Log::debug('WP OAuth authorize URL', ['url' => $url]);

        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        $cfg = Config::get('services.wordpress');

        if ($request->has('error')) {
            abort(403, 'Authorization was denied by the user.');
        }

        $state = $request->session()->pull('wp_oauth_state');
        if (!$state || $state !== $request->query('state')) {
            abort(403, 'Invalid OAuth state.');
        }

        $code = $request->query('code');
        if (!$code) {
            abort(400, 'Missing authorization code.');
        }

        $http = new Client([
            'http_errors' => false,
            'base_uri'    => rtrim($cfg['api_base_url'], '/') . '/',
            'timeout'     => 15,
        ]);

        $tokenResp = $http->post(ltrim($cfg['oauth']['token'], '/'), [
            'form_params' => [
                'client_id'     => $cfg['client_id'],
                'client_secret' => $cfg['client_secret'],
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => $cfg['redirect'],
            ],
        ]);

        $status    = $tokenResp->getStatusCode();
        $tokenBody = json_decode((string) $tokenResp->getBody(), true) ?: [];
        Log::debug('WP OAuth token response', ['status' => $status, 'body' => $tokenBody]);

        if (!isset($tokenBody['access_token'])) {
            return response()->json([
                'message' => 'Failed to obtain access token',
                'details' => $tokenBody,
            ], 500);
        }

        $accessToken = $tokenBody['access_token'];

        if (!empty($cfg['require_admin'])) {
            $siteIdOrDomain = $request->session()->pull('wp_oauth_blog')
                ?? ($cfg['site_id'] ?? $cfg['site'] ?? null);

            if ($siteIdOrDomain) {
                $siteResp = $http->get(ltrim($cfg['api']['site'], '/') . '/' . rawurlencode($siteIdOrDomain), [
                    'headers' => ['Authorization' => 'Bearer ' . $accessToken],
                ]);
                $site = json_decode((string) $siteResp->getBody(), true) ?: [];

                $canManage = !empty($site['user_can_manage'])
                    || !empty($site['capabilities']['manage'])
                    || !empty($site['capabilities']['manage_options']);

                if (!$canManage) {
                    abort(403, 'Your WordPress.com user does not have admin access to the required site.');
                }
                session(['wp_site' => $site]);
            }
        }

        $meResp = $http->get(ltrim($cfg['api']['me'], '/'), [
            'headers' => ['Authorization' => 'Bearer ' . $accessToken],
        ]);
        $me = json_decode((string) $meResp->getBody(), true) ?: [];

        session([
            'wp_access_token' => $accessToken,
            'wp_user'         => $me,
            'wp_token_raw'    => $tokenBody,
        ]);

        return redirect('/');
    }
}
