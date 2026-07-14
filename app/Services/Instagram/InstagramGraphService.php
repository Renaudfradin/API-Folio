<?php

namespace App\Services\Instagram;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class InstagramGraphService
{
    public function version(): string
    {
        return config('services.instagram.graph_version', 'v25.0');
    }

    public function baseUrl(): string
    {
        return sprintf('https://graph.facebook.com/%s', $this->version());
    }

    public function buildOAuthUrl(string $state): string
    {
        $query = http_build_query([
            'client_id' => config('services.instagram.client_id'),
            'redirect_uri' => config('services.instagram.redirect_uri'),
            'response_type' => 'code',
            'scope' => collect(config('services.instagram.scopes', []))->implode(','),
            'state' => $state,
        ]);

        return 'https://www.facebook.com/'.$this->version().'/dialog/oauth?'.$query;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    public function exchangeCodeForAccessToken(string $code): array
    {
        return $this->request('get', '/oauth/access_token', [
            'client_id' => config('services.instagram.client_id'),
            'client_secret' => config('services.instagram.client_secret'),
            'redirect_uri' => config('services.instagram.redirect_uri'),
            'code' => $code,
        ]);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    public function exchangeForLongLivedToken(string $shortLivedToken): array
    {
        return $this->request('get', '/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('services.instagram.client_id'),
            'client_secret' => config('services.instagram.client_secret'),
            'fb_exchange_token' => $shortLivedToken,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws ConnectionException|RequestException
     */
    public function getPages(string $accessToken): array
    {
        $response = $this->request('get', '/me/accounts', [
            'fields' => 'id,name,access_token,instagram_business_account{id,username}',
            'access_token' => $accessToken,
        ]);

        return $response['data'] ?? [];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    public function getInstagramAccount(string $businessAccountId, string $accessToken): array
    {
        return $this->request('get', '/'.$businessAccountId, [
            'fields' => 'id,username,name,biography,website,profile_picture_url,followers_count,follows_count,media_count',
            'access_token' => $accessToken,
        ]);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    public function getAccountInsights(string $businessAccountId, string $accessToken): array
    {
        return $this->request('get', '/'.$businessAccountId.'/insights', [
            'metric' => 'impressions,reach,profile_views,website_clicks,accounts_engaged',
            'period' => 'day',
            'access_token' => $accessToken,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws ConnectionException|RequestException
     */
    public function getMedia(string $businessAccountId, string $accessToken, int $limit = 50): array
    {
        $response = $this->request('get', '/'.$businessAccountId.'/media', [
            'fields' => 'id,caption,media_type,media_product_type,media_url,thumbnail_url,permalink,timestamp,like_count,comments_count',
            'limit' => $limit,
            'access_token' => $accessToken,
        ]);

        return $response['data'] ?? [];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    public function getMediaInsights(string $mediaId, string $accessToken): array
    {
        return $this->request('get', '/'.$mediaId.'/insights', [
            'metric' => 'impressions,reach,engagement,saved,views',
            'access_token' => $accessToken,
        ]);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    private function request(string $method, string $uri, array $query = []): array
    {
        $response = Http::retry(3, 300)
            ->acceptJson()
            ->{$method}($this->baseUrl().$uri, $query)
            ->throw();

        return $response->json() ?? [];
    }
}
