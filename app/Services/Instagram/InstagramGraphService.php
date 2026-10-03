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
     * @return array<int, array<string, mixed>>
     *
     * @throws ConnectionException|RequestException
     */
    public function getStories(string $businessAccountId, string $accessToken): array
    {
        $query = [
            'fields' => 'id,caption,media_type,media_product_type,media_url,thumbnail_url,permalink,timestamp,like_count,comments_count',
            'access_token' => $accessToken,
        ];

        $stories = $this->fetchStoriesPage($businessAccountId, $query);

        if ($stories === []) {
            return [];
        }

        if (isset($stories[0]['media_url']) || isset($stories[0]['timestamp'])) {
            return collect($stories)
                ->map(function (array $story): array {
                    $story['media_product_type'] = $story['media_product_type'] ?? 'STORY';

                    return $story;
                })
                ->all();
        }

        $enriched = [];

        foreach ($stories as $story) {
            $storyId = $story['id'] ?? null;

            if (! filled($storyId)) {
                continue;
            }

            try {
                $node = $this->getMediaNode((string) $storyId, $accessToken);
                $node['media_product_type'] = $node['media_product_type'] ?? 'STORY';
                $enriched[] = $node;
            } catch (RequestException|ConnectionException) {
                $story['media_product_type'] = $story['media_product_type'] ?? 'STORY';
                $enriched[] = $story;
            }
        }

        return $enriched;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<int, array<string, mixed>>
     */
    protected function fetchStoriesPage(string $businessAccountId, array $query): array
    {
        foreach ([$this->baseUrl(), $this->facebookBaseUrl()] as $host) {
            try {
                $response = Http::retry(2, 300)
                    ->acceptJson()
                    ->get(rtrim($host, '/').'/'.$businessAccountId.'/stories', $query)
                    ->throw();

                $stories = $response->json('data') ?? [];

                if ($stories !== []) {
                    return $stories;
                }
            } catch (RequestException|ConnectionException) {
                continue;
            }
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    public function getMediaNode(string $mediaId, string $accessToken): array
    {
        return $this->requestGraph('get', '/'.$mediaId, [
            'fields' => 'id,caption,media_type,media_product_type,media_url,thumbnail_url,permalink,timestamp,like_count,comments_count',
            'access_token' => $accessToken,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getStoryInsights(string $mediaId, string $accessToken): array
    {
        $dataByName = [];

        foreach (['views,reach,replies,likes', 'views,reach,replies', 'views,reach', 'views'] as $metrics) {
            try {
                $response = $this->requestGraph('get', '/'.$mediaId.'/insights', [
                    'metric' => $metrics,
                    'access_token' => $accessToken,
                ]);

                foreach ($response['data'] ?? [] as $item) {
                    $name = $item['name'] ?? null;

                    if (filled($name) && ! isset($dataByName[$name])) {
                        $dataByName[$name] = $item;
                    }
                }
            } catch (RequestException|ConnectionException) {
                continue;
            }
        }

        if ($dataByName === []) {
            return [];
        }

        return ['data' => array_values($dataByName)];
    }

    public function facebookBaseUrl(): string
    {
        return sprintf('https://graph.facebook.com/%s', $this->version());
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    public function getMediaInsights(string $mediaId, string $accessToken): array
    {
        $dataByName = [];

        foreach (['views,reach,shares,saved,follows', 'views,shares,saved,follows', 'views', 'reach', 'shares', 'saved', 'follows'] as $metrics) {
            try {
                $response = $this->request('get', '/'.$mediaId.'/insights', [
                    'metric' => $metrics,
                    'access_token' => $accessToken,
                ]);

                foreach ($response['data'] ?? [] as $item) {
                    $name = $item['name'] ?? null;

                    if (filled($name) && ! isset($dataByName[$name])) {
                        $dataByName[$name] = $item;
                    }
                }
            } catch (RequestException|ConnectionException) {
                continue;
            }
        }

        if (! isset($dataByName['shares']) || ! isset($dataByName['saved'])) {
            $dataByName = $this->mergeMediaEngagementFieldsFromNode($mediaId, $accessToken, $dataByName);
        }

        if ($dataByName === []) {
            return [];
        }

        return ['data' => array_values($dataByName)];
    }

    /**
     * @param  array<string, array<string, mixed>>  $dataByName
     * @return array<string, array<string, mixed>>
     */
    protected function mergeMediaEngagementFieldsFromNode(
        string $mediaId,
        string $accessToken,
        array $dataByName,
    ): array {
        try {
            $node = $this->request('get', '/'.$mediaId, [
                'fields' => 'shares_count,saved_count',
                'access_token' => $accessToken,
            ]);
        } catch (RequestException|ConnectionException) {
            return $dataByName;
        }

        if (! isset($dataByName['shares']) && isset($node['shares_count'])) {
            $dataByName['shares'] = [
                'name' => 'shares',
                'values' => [['value' => (int) $node['shares_count']]],
            ];
        }

        if (! isset($dataByName['saved']) && isset($node['saved_count'])) {
            $dataByName['saved'] = [
                'name' => 'saved',
                'values' => [['value' => (int) $node['saved_count']]],
            ];
        }

        return $dataByName;
    }

    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws ConnectionException|RequestException
     */
    public function getMediaChildren(string $mediaId, string $accessToken): array
    {
        $response = $this->request('get', '/'.$mediaId.'/children', [
            'fields' => 'id,media_type,media_url,thumbnail_url',
            'access_token' => $accessToken,
        ]);

        return $response['data'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMediaComments(string $mediaId, string $accessToken, int $limit = 100): array
    {
        $fieldVariants = [
            null,
            'id,text,timestamp,like_count,from',
            'id,text,timestamp,like_count,username,from',
        ];

        foreach ($fieldVariants as $fields) {
            $comments = $this->paginateCommentsEdge($mediaId, $accessToken, $limit, $fields, useBearer: true);

            if ($comments !== []) {
                return $this->enrichCommentsWithReplies($comments, $accessToken, $limit);
            }

            $comments = $this->paginateCommentsEdge($mediaId, $accessToken, $limit, $fields, useBearer: false);

            if ($comments !== []) {
                return $this->enrichCommentsWithReplies($comments, $accessToken, $limit);
            }
        }

        $facebookComments = $this->getMediaCommentsViaFacebookGraph($mediaId, $accessToken, $limit);

        if ($facebookComments !== []) {
            return $this->enrichCommentsWithReplies($facebookComments, $accessToken, $limit);
        }

        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMediaCommentsViaFacebookGraph(string $mediaId, string $accessToken, int $limit = 100): array
    {
        try {
            $response = Http::retry(2, 300)
                ->acceptJson()
                ->get(sprintf('https://graph.facebook.com/%s/%s/comments', $this->version(), $mediaId), [
                    'fields' => 'id,text,timestamp,like_count,from,username',
                    'limit' => min(50, $limit),
                    'access_token' => $accessToken,
                ])
                ->throw();

            return $response->json('data') ?? [];
        } catch (RequestException|ConnectionException) {
            return [];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws ConnectionException|RequestException
     */
    public function getMediaCommentsFromMediaNode(string $mediaId, string $accessToken, int $limit = 100): array
    {
        $fieldVariants = [
            'comments.limit('.$limit.'){id,text,timestamp,like_count,from}',
            'comments.limit('.$limit.'){id,text,timestamp,like_count,username,from}',
        ];

        foreach ($fieldVariants as $fields) {
            $response = $this->request('get', '/'.$mediaId, [
                'fields' => $fields,
                'access_token' => $accessToken,
            ]);

            $comments = $response['comments']['data'] ?? [];

            if ($comments !== []) {
                return $this->enrichCommentsWithReplies($comments, $accessToken, $limit);
            }
        }

        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws ConnectionException|RequestException
     */
    public function getCommentReplies(string $commentId, string $accessToken, int $limit = 50): array
    {
        $response = $this->request('get', '/'.$commentId.'/replies', [
            'fields' => 'id,text,timestamp,like_count,username,from',
            'limit' => min(50, $limit),
            'access_token' => $accessToken,
        ]);

        return $response['data'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function paginateCommentsEdge(
        string $mediaId,
        string $accessToken,
        int $limit,
        ?string $fields,
        bool $useBearer,
    ): array {
        $comments = [];
        $after = null;

        while (count($comments) < $limit) {
            $query = [
                'limit' => min(50, $limit - count($comments)),
            ];

            if ($fields !== null) {
                $query['fields'] = $fields;
            }

            if ($after !== null) {
                $query['after'] = $after;
            }

            try {
                $response = $this->requestComments('get', '/'.$mediaId.'/comments', $query, $accessToken, $useBearer);
            } catch (RequestException|ConnectionException) {
                break;
            }

            $page = $response['data'] ?? [];

            if ($page === []) {
                break;
            }

            $comments = array_merge($comments, $page);

            $after = $response['paging']['cursors']['after'] ?? null;

            if (! filled($after)) {
                break;
            }
        }

        return array_slice($comments, 0, $limit);
    }

    /**
     * @param  array<int, array<string, mixed>>  $comments
     * @return array<int, array<string, mixed>>
     */
    protected function enrichCommentsWithReplies(array $comments, string $accessToken, int $limit): array
    {
        $enriched = [];

        foreach (array_slice($comments, 0, $limit) as $comment) {
            if (! isset($comment['replies']) && filled($comment['id'] ?? null)) {
                try {
                    $comment['replies'] = [
                        'data' => $this->getCommentReplies((string) $comment['id'], $accessToken),
                    ];
                } catch (RequestException|ConnectionException) {
                    $comment['replies'] = ['data' => []];
                }
            }

            $enriched[] = $comment;
        }

        return $enriched;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    protected function requestComments(
        string $method,
        string $uri,
        array $query,
        string $accessToken,
        bool $useBearer,
    ): array {
        if ($useBearer) {
            $response = Http::retry(3, 300)
                ->acceptJson()
                ->withToken($accessToken)
                ->{$method}($this->baseUrl().$uri, $query)
                ->throw();

            return $response->json() ?? [];
        }

        $query['access_token'] = $accessToken;

        return $this->request($method, $uri, $query);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    public function subscribeAccountToCommentWebhooks(string $businessAccountId, string $accessToken): void
    {
        Http::retry(2, 300)
            ->acceptJson()
            ->post($this->baseUrl().'/'.$businessAccountId.'/subscribed_apps', [
                'subscribed_fields' => 'comments,story_insights',
                'access_token' => $accessToken,
            ])
            ->throw();
    }

    public function postCommentReply(string $commentId, string $message, string $accessToken): array
    {
        $response = Http::retry(3, 300)
            ->acceptJson()
            ->asForm()
            ->post($this->baseUrl().'/'.$commentId.'/replies', [
                'message' => $message,
                'access_token' => $accessToken,
            ])
            ->throw();

        return $response->json() ?? [];
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

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    private function requestGraph(string $method, string $uri, array $query = []): array
    {
        try {
            return $this->request($method, $uri, $query);
        } catch (RequestException|ConnectionException $instagramException) {
            $response = Http::retry(2, 300)
                ->acceptJson()
                ->{$method}($this->facebookBaseUrl().$uri, $query)
                ->throw();

            return $response->json() ?? [];
        }
    }
}
