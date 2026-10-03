<?php

namespace App\Services\Instagram;

use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

class InstagramSyncService
{
    public function __construct(
        protected InstagramGraphService $graph,
    ) {}

    /**
     * @return array<int, InstagramAccount>
     */
    public function syncAllActive(): array
    {
        return InstagramAccount::query()
            ->where('is_active', true)
            ->get()
            ->map(function (InstagramAccount $account): ?InstagramAccount {
                try {
                    return $this->syncAccount($account);
                } catch (Throwable) {
                    return null;
                }
            })
            ->filter()
            ->all();
    }

    public function syncAccount(InstagramAccount $account, bool $refreshProfile = true): InstagramAccount
    {
        $run = $account->syncRuns()->create([
            'status' => 'running',
            'started_at' => now(),
            'payload' => [
                'refresh_profile' => $refreshProfile,
            ],
        ]);

        try {
            $profile = $refreshProfile
                ? $this->graph->getInstagramAccount($account->business_account_id, $account->access_token)
                : [];

            $accountInsights = [];
            try {
                $accountInsights = $this->graph->getAccountInsights($account->business_account_id, $account->access_token);
            } catch (Throwable) {
                $accountInsights = [];
            }

            $mediaItems = $this->graph->getMedia($account->business_account_id, $account->access_token);

            $profileInsights = $this->normalizeInsights($accountInsights);
            $syncedCount = 0;

            foreach ($mediaItems as $mediaItem) {
                $existingMedia = InstagramMedia::query()
                    ->where('media_id', $mediaItem['id'])
                    ->first();

                $isStory = $this->isStoryMediaItem($mediaItem);
                $mediaInsightsPayload = [];

                try {
                    $mediaInsightsPayload = $isStory
                        ? $this->graph->getStoryInsights($mediaItem['id'], $account->access_token)
                        : $this->graph->getMediaInsights($mediaItem['id'], $account->access_token);
                } catch (Throwable) {
                    $mediaInsightsPayload = [];
                }

                if ($mediaInsightsPayload === [] && $existingMedia !== null) {
                    $mediaInsights = $existingMedia->insights ?? [];
                    $viewCount = (int) $existingMedia->view_count;
                } else {
                    $freshInsights = $isStory
                        ? $this->normalizeStoryInsights($mediaInsightsPayload, $existingMedia?->insights ?? [])
                        : $this->normalizeInsights($mediaInsightsPayload);
                    $mediaInsights = $isStory
                        ? $freshInsights
                        : array_merge($existingMedia?->insights ?? [], $freshInsights);
                    $viewCount = (int) Arr::get($freshInsights, 'views', 0);

                    if ($viewCount === 0) {
                        $viewCount = (int) Arr::get($mediaInsights, 'views', $existingMedia?->view_count ?? 0);
                    }
                }

                if ($mediaInsights === [] && $existingMedia === null) {
                    Log::warning('Instagram media insights empty after sync.', [
                        'media_id' => $mediaItem['id'],
                    ]);
                }

                $children = $isStory ? [] : $this->syncMediaChildren($mediaItem, $account->access_token);
                $comments = $isStory ? [] : $this->syncMediaComments(
                    $mediaItem['id'],
                    $account->access_token,
                    (int) ($mediaItem['comments_count'] ?? 0),
                );

                $reactions = $isStory
                    ? (int) Arr::get($mediaInsights, 'reactions', Arr::get($mediaInsights, 'likes', (int) ($mediaItem['like_count'] ?? 0)))
                    : (int) ($mediaItem['like_count'] ?? 0);

                $replies = $isStory
                    ? (int) Arr::get($mediaInsights, 'replies', (int) ($mediaItem['comments_count'] ?? 0))
                    : (int) ($mediaItem['comments_count'] ?? 0);

                InstagramMedia::query()->updateOrCreate(
                    ['media_id' => $mediaItem['id']],
                    [
                        'instagram_account_id' => $account->id,
                        'caption' => $mediaItem['caption'] ?? null,
                        'permalink' => $mediaItem['permalink'] ?? null,
                        'media_type' => $mediaItem['media_type'] ?? 'UNKNOWN',
                        'media_product_type' => $isStory ? 'STORY' : ($mediaItem['media_product_type'] ?? null),
                        'media_url' => $mediaItem['media_url'] ?? null,
                        'thumbnail_url' => $mediaItem['thumbnail_url'] ?? null,
                        'like_count' => $reactions,
                        'comments_count' => $replies,
                        'view_count' => $viewCount,
                        'timestamp' => isset($mediaItem['timestamp']) ? Carbon::parse($mediaItem['timestamp']) : null,
                        'insights' => $mediaInsights,
                        'raw_data' => $mediaItem,
                        'children' => $children,
                        'comments' => $comments,
                        'synced_at' => now(),
                    ]
                );

                $syncedCount++;
            }

            $storiesSynced = $this->syncStories($account);
            $syncedCount += $storiesSynced;

            if ($storiesSynced === 0) {
                Log::info('Instagram sync: no active stories returned by API (stories expire after ~24h).', [
                    'instagram_account_id' => $account->id,
                    'username' => $account->username,
                ]);
            }

            $account->forceFill([
                'page_id' => $account->page_id,
                'page_name' => $account->page_name,
                'business_account_id' => $profile['id'] ?? $account->business_account_id,
                'username' => $profile['username'] ?? $account->username,
                'name' => $profile['name'] ?? $account->name,
                'biography' => $profile['biography'] ?? $account->biography,
                'website' => $profile['website'] ?? $account->website,
                'profile_picture_url' => $profile['profile_picture_url'] ?? $account->profile_picture_url,
                'followers_count' => (int) ($profile['followers_count'] ?? $account->followers_count),
                'follows_count' => (int) ($profile['follows_count'] ?? $account->follows_count),
                'media_count' => (int) ($profile['media_count'] ?? $account->media_count),
                'latest_account_insights' => $profileInsights,
                'last_synced_at' => now(),
                'last_synced_status' => 'success',
                'last_synced_error' => null,
            ])->save();

            $run->forceFill([
                'status' => 'success',
                'finished_at' => now(),
                'records_synced' => $syncedCount,
                'payload' => [
                    'refresh_profile' => $refreshProfile,
                    'account_insights' => $profileInsights,
                    'media_count' => count($mediaItems),
                    'stories_count' => $storiesSynced,
                ],
            ])->save();
        } catch (Throwable $throwable) {
            $account->forceFill([
                'last_synced_at' => now(),
                'last_synced_status' => 'failed',
                'last_synced_error' => $throwable->getMessage(),
            ])->save();

            $run->forceFill([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => $throwable->getMessage(),
            ])->save();

            throw $throwable;
        }

        return $account->refresh();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function syncCommentsForMedia(InstagramMedia $media): array
    {
        $account = $media->account;

        if ($account === null || ! filled($account->access_token)) {
            throw new \RuntimeException('Compte Instagram ou jeton d’accès manquant.');
        }

        $comments = $this->syncMediaComments(
            $media->media_id,
            $account->access_token,
            (int) $media->comments_count,
        );

        $media->forceFill(['comments' => $comments])->save();

        return $comments;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function normalizeInsights(array $payload): array
    {
        $normalized = collect($payload['data'] ?? [])
            ->mapWithKeys(function (array $item): array {
                $name = (string) ($item['name'] ?? '');

                if ($name === '') {
                    return [];
                }

                $value = $item['values'][0]['value'] ?? null;

                if ($value === null && isset($item['total_value'])) {
                    $value = $item['total_value']['value'] ?? $item['total_value'];
                }

                return [$name => $value];
            })
            ->all();

        return $this->mapStoryReactionMetrics($normalized);
    }

    /**
     * @param  array<string, mixed>  $insights
     * @return array<string, mixed>
     */
    public function normalizeStoryInsights(array $payload, array $existing = []): array
    {
        $normalized = array_merge($existing, $this->normalizeInsights($payload));

        return $this->mapStoryReactionMetrics($normalized);
    }

    /**
     * @param  array<string, mixed>  $insights
     * @return array<string, mixed>
     */
    protected function mapStoryReactionMetrics(array $insights): array
    {
        if (isset($insights['likes']) && ! isset($insights['reactions'])) {
            $insights['reactions'] = (int) $insights['likes'];
        }

        return $insights;
    }

    /**
     * @param  array<string, mixed>  $mediaItem
     */
    protected function isStoryMediaItem(array $mediaItem): bool
    {
        return strtoupper((string) ($mediaItem['media_product_type'] ?? '')) === 'STORY';
    }

    protected function syncStories(InstagramAccount $account): int
    {
        try {
            $stories = $this->graph->getStories($account->business_account_id, $account->access_token);
        } catch (Throwable $throwable) {
            Log::warning('Instagram stories request failed.', [
                'instagram_account_id' => $account->id,
                'message' => $throwable->getMessage(),
            ]);

            return 0;
        }

        $syncedCount = 0;

        foreach ($stories as $storyItem) {
            $mediaId = $storyItem['id'] ?? null;

            if (! filled($mediaId)) {
                continue;
            }

            $existingMedia = InstagramMedia::query()
                ->where('media_id', $mediaId)
                ->first();

            $insightsPayload = [];

            try {
                $insightsPayload = $this->graph->getStoryInsights((string) $mediaId, $account->access_token);
            } catch (Throwable) {
                $insightsPayload = [];
            }

            if ($insightsPayload === [] && $existingMedia !== null) {
                $mediaInsights = $existingMedia->insights ?? [];
                $viewCount = (int) $existingMedia->view_count;
            } else {
                $mediaInsights = $this->normalizeStoryInsights(
                    $insightsPayload,
                    $existingMedia?->insights ?? [],
                );
                $viewCount = (int) Arr::get($mediaInsights, 'views', 0);

                if ($viewCount === 0 && $existingMedia !== null) {
                    $viewCount = (int) $existingMedia->view_count;
                }
            }

            $reactions = (int) Arr::get($mediaInsights, 'reactions', Arr::get($mediaInsights, 'likes', 0));

            InstagramMedia::query()->updateOrCreate(
                ['media_id' => $mediaId],
                [
                    'instagram_account_id' => $account->id,
                    'caption' => $storyItem['caption'] ?? null,
                    'permalink' => $storyItem['permalink'] ?? null,
                    'media_type' => $storyItem['media_type'] ?? 'IMAGE',
                    'media_product_type' => 'STORY',
                    'media_url' => $storyItem['media_url'] ?? null,
                    'thumbnail_url' => $storyItem['thumbnail_url'] ?? null,
                    'like_count' => $reactions,
                    'comments_count' => (int) Arr::get($mediaInsights, 'replies', 0),
                    'view_count' => $viewCount,
                    'timestamp' => isset($storyItem['timestamp']) ? Carbon::parse($storyItem['timestamp']) : null,
                    'insights' => $mediaInsights,
                    'raw_data' => $storyItem,
                    'children' => [],
                    'comments' => [],
                    'synced_at' => now(),
                ]
            );

            $syncedCount++;
        }

        return $syncedCount;
    }

    /**
     * @param  array<string, mixed>  $mediaItem
     * @return array<int, array<string, mixed>>
     */
    protected function syncMediaChildren(array $mediaItem, string $accessToken): array
    {
        if (($mediaItem['media_type'] ?? null) !== 'CAROUSEL_ALBUM') {
            return [];
        }

        try {
            $children = $this->graph->getMediaChildren($mediaItem['id'], $accessToken);
        } catch (Throwable) {
            return [];
        }

        return $this->normalizeMediaChildren($children);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function syncMediaComments(string $mediaId, string $accessToken, int $commentsCount = 0): array
    {
        $rawComments = [];

        try {
            $rawComments = $this->graph->getMediaComments($mediaId, $accessToken);
        } catch (Throwable $throwable) {
            Log::warning('Instagram comments edge request failed.', [
                'media_id' => $mediaId,
                'message' => $throwable->getMessage(),
            ]);
        }

        if ($rawComments === []) {
            try {
                $rawComments = $this->graph->getMediaCommentsFromMediaNode($mediaId, $accessToken);
            } catch (Throwable $throwable) {
                Log::warning('Instagram nested comments request failed.', [
                    'media_id' => $mediaId,
                    'message' => $throwable->getMessage(),
                ]);
            }
        }

        if ($rawComments === []) {
            $rawComments = $this->graph->getMediaCommentsViaFacebookGraph($mediaId, $accessToken);
        }

        $comments = $this->normalizeMediaComments($rawComments);

        if ($commentsCount > 0 && $comments === []) {
            Log::warning('Instagram API returned no comments despite comments_count.', [
                'media_id' => $mediaId,
                'comments_count' => $commentsCount,
                'raw_comments_count' => count($rawComments),
                'raw_comment_keys' => isset($rawComments[0]) && is_array($rawComments[0])
                    ? array_keys($rawComments[0])
                    : [],
            ]);
        }

        return $comments;
    }

    /**
     * @param  array<string, mixed>  $value
     */
    public function upsertWebhookComment(InstagramAccount $account, array $value): void
    {
        $mediaId = (string) (Arr::get($value, 'media.id') ?? Arr::get($value, 'media_id') ?? '');

        if ($mediaId === '') {
            return;
        }

        $media = InstagramMedia::query()
            ->where('instagram_account_id', $account->id)
            ->where('media_id', $mediaId)
            ->first();

        if ($media === null) {
            Log::warning('Instagram webhook comment ignored: media not in database.', [
                'instagram_account_id' => $account->id,
                'media_id' => $mediaId,
                'comment_id' => $value['id'] ?? null,
            ]);

            return;
        }

        $incoming = $this->normalizeMediaComment([
            'id' => $value['id'] ?? null,
            'text' => $value['text'] ?? null,
            'timestamp' => $value['timestamp'] ?? now()->toIso8601String(),
            'like_count' => $value['like_count'] ?? 0,
            'from' => $value['from'] ?? null,
            'username' => Arr::get($value, 'from.username'),
            'replies' => ['data' => []],
        ]);

        if (! filled($incoming['id'])) {
            return;
        }

        $existing = collect($media->comments ?? []);
        $commentId = (string) $incoming['id'];

        $merged = $existing
            ->reject(fn (array $comment): bool => (string) ($comment['id'] ?? '') === $commentId)
            ->push($incoming)
            ->values()
            ->all();

        $media->forceFill(['comments' => $merged])->save();
    }

    /**
     * @param  array<string, mixed>  $value
     */
    public function upsertWebhookStoryInsights(InstagramAccount $account, array $value): void
    {
        $mediaId = (string) (
            Arr::get($value, 'media_id')
            ?? Arr::get($value, 'id')
            ?? Arr::get($value, 'media.id')
            ?? ''
        );

        if ($mediaId === '') {
            return;
        }

        $incomingInsights = [];

        foreach (['views', 'reach', 'replies', 'likes', 'impressions', 'navigation'] as $metric) {
            if (! array_key_exists($metric, $value)) {
                continue;
            }

            if ($metric === 'likes') {
                $incomingInsights['reactions'] = (int) $value[$metric];

                continue;
            }

            $incomingInsights[$metric] = $value[$metric];
        }

        if (isset($value['insights']) && is_array($value['insights'])) {
            $nested = $value['insights'];

            if (isset($nested['data'])) {
                $incomingInsights = array_merge(
                    $incomingInsights,
                    $this->normalizeStoryInsights($nested),
                );
            } else {
                $incomingInsights = array_merge($incomingInsights, $this->mapStoryReactionMetrics($nested));
            }
        }

        $media = InstagramMedia::query()
            ->where('instagram_account_id', $account->id)
            ->where('media_id', $mediaId)
            ->first();

        $mergedInsights = $this->mapStoryReactionMetrics(array_merge(
            $media?->insights ?? [],
            $incomingInsights,
        ));

        $viewCount = (int) Arr::get($mergedInsights, 'views', $media?->view_count ?? 0);
        $reactions = (int) Arr::get($mergedInsights, 'reactions', $media?->like_count ?? 0);
        $replies = (int) Arr::get($mergedInsights, 'replies', $media?->comments_count ?? 0);

        InstagramMedia::query()->updateOrCreate(
            ['media_id' => $mediaId],
            [
                'instagram_account_id' => $account->id,
                'caption' => $media?->caption,
                'permalink' => $media?->permalink ?? Arr::get($value, 'permalink'),
                'media_type' => $media?->media_type ?? Arr::get($value, 'media_type', 'IMAGE'),
                'media_product_type' => 'STORY',
                'media_url' => $media?->media_url ?? Arr::get($value, 'media_url'),
                'thumbnail_url' => $media?->thumbnail_url ?? Arr::get($value, 'thumbnail_url'),
                'like_count' => $reactions,
                'comments_count' => $replies,
                'view_count' => $viewCount,
                'timestamp' => $media?->timestamp
                    ?? (isset($value['timestamp']) ? Carbon::parse($value['timestamp']) : null),
                'insights' => $mergedInsights,
                'raw_data' => array_merge($media?->raw_data ?? [], ['webhook' => $value]),
                'children' => [],
                'comments' => [],
                'synced_at' => now(),
            ]
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $children
     * @return array<int, array<string, mixed>>
     */
    public function normalizeMediaChildren(array $children): array
    {
        return collect($children)
            ->map(fn (array $child): array => [
                'id' => $child['id'] ?? null,
                'media_type' => $child['media_type'] ?? null,
                'media_url' => $child['media_url'] ?? null,
                'thumbnail_url' => $child['thumbnail_url'] ?? null,
            ])
            ->filter(fn (array $child): bool => filled($child['media_url']) || filled($child['thumbnail_url']))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $comments
     * @return array<int, array<string, mixed>>
     */
    public function normalizeMediaComments(array $comments): array
    {
        return collect($comments)
            ->map(fn (array $comment): array => $this->normalizeMediaComment($comment))
            ->filter(fn (array $comment): bool => filled($comment['text']))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $comment
     * @return array<string, mixed>
     */
    public function normalizeMediaComment(array $comment): array
    {
        $repliesRaw = $comment['replies'] ?? [];
        $repliesList = is_array($repliesRaw) && isset($repliesRaw['data'])
            ? $repliesRaw['data']
            : (is_array($repliesRaw) ? $repliesRaw : []);

        return [
            'id' => $comment['id'] ?? null,
            'username' => $comment['username']
                ?? Arr::get($comment, 'from.username')
                ?? (is_array($comment['from'] ?? null) ? ($comment['from']['username'] ?? null) : null),
            'text' => $comment['text'] ?? $comment['message'] ?? null,
            'like_count' => (int) ($comment['like_count'] ?? 0),
            'timestamp' => isset($comment['timestamp'])
                ? Carbon::parse($comment['timestamp'])->toIso8601String()
                : null,
            'replies' => $this->normalizeMediaComments($repliesList),
        ];
    }
}
