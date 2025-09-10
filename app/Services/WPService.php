<?php

namespace App\Services;

use App\Contracts\IWP;
use App\Services\WordPressTokenStore; 
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;

class WPService implements IWP
{
    private Client $http;
    private string $base;
    private string $site;

    public function __construct(private WordPressTokenStore $tokens)
    {
        $cfg       = Config::get('services.wordpress');
        $this->base = rtrim($cfg['api_base_url'], '/') . '/';
        $this->http = new Client(['base_uri' => $this->base, 'http_errors' => false, 'timeout' => 20]);
        $this->site = $cfg['site_id'] ?? $cfg['site'] ?? (session('wp_site.ID') ?? session('wp_site.URL') ?? '');
    }

    private function authHeaders(): array
    {
        $access = $this->tokens->getAccessToken();
        return ['Authorization' => 'Bearer ' . $access];
    }

    public function me(): array
    {
        $res = $this->http->get('rest/v1.4/me', ['headers' => $this->authHeaders()]);
        return json_decode((string)$res->getBody(), true) ?: [];
    }

    public function listPosts(array $params = []): array
    {
        // Typical params: number, status, page
        $query = array_merge([
            'number' => 50,
            'status' => 'publish,draft',
        ], $params);

        $path = 'rest/v1.1/sites/' . rawurlencode($this->site) . '/posts';
        $res  = $this->http->get($path, ['headers' => $this->authHeaders(), 'query' => $query]);
        return json_decode((string)$res->getBody(), true) ?: ['posts'=>[]];
    }

    public function getPost(int|string $postId): array
    {
        $path = 'rest/v1.1/sites/' . rawurlencode($this->site) . '/posts/' . rawurlencode($postId);
        $res  = $this->http->get($path, ['headers' => $this->authHeaders()]);
        return json_decode((string)$res->getBody(), true) ?: [];
    }

    public function createPost(array $data): array
    {
        // WP.com REST: POST sites/{site}/posts/new
        $path = 'rest/v1.1/sites/' . rawurlencode($this->site) . '/posts/new';
        $res  = $this->http->post($path, ['headers' => $this->authHeaders(), 'form_params' => $data]);
        return json_decode((string)$res->getBody(), true) ?: [];
    }

    public function updatePost(int|string $postId, array $data): array
    {
        // WP.com REST: POST sites/{site}/posts/{postId}
        $path = 'rest/v1.1/sites/' . rawurlencode($this->site) . '/posts/' . rawurlencode($postId);
        $res  = $this->http->post($path, ['headers' => $this->authHeaders(), 'form_params' => $data]);
        return json_decode((string)$res->getBody(), true) ?: [];
    }

    public function deletePost(int|string $postId): array
    {
        // WP.com REST: POST sites/{site}/posts/{postId}/delete
        $path = 'rest/v1.1/sites/' . rawurlencode($this->site) . '/posts/' . rawurlencode($postId) . '/delete';
        $res  = $this->http->post($path, ['headers' => $this->authHeaders()]);
        return json_decode((string)$res->getBody(), true) ?: [];
    }
}