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
        return sprintf('%s/%s', config('services.instagram.graph_host', 'https://graph.instagram.com'), $this->version());
    }

    /**
     * @return string|null Message d’erreur en français si la config OAuth est invalide.
     */
    public function oauthConfigurationError(): ?string
    {
        if (! filled(config('services.instagram.client_id')) || ! filled(config('services.instagram.client_secret'))) {
            return 'Instagram OAuth non configuré : renseignez INSTAGRAM_APP_ID et INSTAGRAM_APP_SECRET (section Business login du dashboard Meta).';
        }

        if (! filled(config('services.instagram.redirect_uri'))) {
            return 'INSTAGRAM_REDIRECT_URI est manquant.';
        }

        $metaAppId = env('META_APP_ID');
        $clientId = (string) config('services.instagram.client_id');

        if (filled($metaAppId) && $clientId === (string) $metaAppId) {
            return 'INSTAGRAM_APP_ID semble être l’App ID Facebook (META_APP_ID). Utilisez l’Instagram App ID affiché dans Business login settings.';
        }

        return null;
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

        return 'https://www.instagram.com/oauth/authorize?'.$query;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    public function exchangeCodeForAccessToken(string $code): array
    {
        $response = Http::retry(3, 300)
            ->asForm()
            ->acceptJson()
            ->post('https://api.instagram.com/oauth/access_token', [
                'client_id' => config('services.instagram.client_id'),
                'client_secret' => config('services.instagram.client_secret'),
                'grant_type' => 'authorization_code',
                'redirect_uri' => config('services.instagram.redirect_uri'),
                'code' => $code,
            ])
            ->throw();

        return $response->json() ?? [];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    public function exchangeForLongLivedToken(string $shortLivedToken): array
    {
        $response = Http::retry(3, 300)
            ->acceptJson()
            ->get(sprintf('%s/access_token', config('services.instagram.graph_host', 'https://graph.instagram.com')), [
                'grant_type' => 'ig_exchange_token',
                'client_secret' => config('services.instagram.client_secret'),
                'access_token' => $shortLivedToken,
            ])
            ->throw();

        return $response->json() ?? [];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    public function getAuthenticatedUser(string $accessToken): array
    {
        return $this->request('get', '/me', [
            'fields' => 'user_id,username,name,biography,website,profile_picture_url,followers_count,follows_count,media_count',
            'access_token' => $accessToken,
        ]);
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
